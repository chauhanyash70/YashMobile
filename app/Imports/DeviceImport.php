<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Brand;
use App\Models\MobileModel;
use App\Models\Mobile;
use App\Models\Transaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Accessory;
use App\Models\Repair;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\Traits;

class DeviceImport implements ToCollection, WithHeadingRow
{
    public $deviceCount = 0;
    public $accessoryCount = 0;
    public $skipCount = 0;

    public function collection(Collection $rows)
    {
        // Map headers to consistent keys based on the provided sheet
        // Date | Imei/SN | Model | Brand | Color | RAM | ROM | Condition | Purchase | Expected | Sold | Status | Supplier | Customer
        
        $rows = $rows->flatMap(function ($row) {
            // Check multiple potential IMEI headers
            $imeiString = (string)($row['imeisn'] ?? $row['imei_sn'] ?? $row['imei'] ?? $row['serial_number'] ?? '');
            
            // Expand rows with multiple IMEIs (separated by , \n | ;)
            $imeis = preg_split('/[,\n|;]+/', $imeiString);
            $imeis = array_filter(array_map('trim', $imeis));

            if (count($imeis) <= 1) {
                return [$row];
            }

            return array_map(function ($imei) use ($row) {
                $newRow = $row;
                // Update the imei key to the single imei
                $key = isset($row['imeisn']) ? 'imeisn' : (isset($row['imei_sn']) ? 'imei_sn' : 'imei');
                $newRow[$key] = $imei;
                return $newRow;
            }, $imeis);
        });

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $this->processRow($row);
            }
        });
    }

    private function processRow($row)
    {
        // Extract IMEI/SN
        $imei = trim((string)($row['imeisn'] ?? $row['imei_sn'] ?? $row['imei'] ?? $row['serial_number'] ?? ''));

        if (empty($imei)) {
            // Process as Accessory if brand/model exists but no IMEI
            if (!empty($row['brand']) && !empty($row['model'])) {
                $this->processAccessoryRow($row);
            } else {
                $this->skipCount++;
            }
            return;
        }

        // Basic validation
        if (empty($row['brand']) || empty($row['model'])) {
            $this->skipCount++;
            return;
        }

        $this->deviceCount++;
        
        // Date parsing (moved up for skip check)
        $dateRaw = $row['date'] ?? $row['purchase_date'] ?? null;
        $purchaseDate = $this->parseDate($dateRaw) ?: now();

        // Skip if this specific unit purchase already exists (same IMEI and same Purchase Date)
        // This prevents double-importing the same row while allowing multiple lifecycles (trade-ins/buybacks)
        $exists = Mobile::where('hsn_number', $imei)
            ->whereHas('transactions', function($q) use ($purchaseDate) {
                $q->where('transaction_type', 'buy')
                  ->whereDate('transaction_date', $purchaseDate->format('Y-m-d'));
            })->exists();

        if ($exists) {
            $this->skipCount++;
            return;
        }

        // Brand & Model
        $brand = Brand::firstOrCreate(['name' => trim($row['brand'])]);
        $model = MobileModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => trim($row['model'])
        ]);

        // RAM & Storage (ROM)
        $ram = trim((string)($row['ram'] ?? ''));
        $rom = trim((string)($row['rom'] ?? $row['storage'] ?? ''));
        
        if ($ram && !str_contains(strtolower($ram), 'gb')) $ram .= ' GB';
        if ($rom && !str_contains(strtolower($rom), 'gb')) $rom .= ' GB';

        // Prices
        $buyPrice = floatval($row['purchase'] ?? $row['buy_price'] ?? $row['purchase_price'] ?? 0);
        $sellPrice = floatval($row['sold'] ?? $row['sell_price'] ?? $row['sale_price'] ?? 0);
        $expectedPrice = floatval($row['expected'] ?? 0);

        // Status & Sale Detection
        $soldDateRaw = $row['sold_date'] ?? $row['sell_date'] ?? $row['sale_date'] ?? null;
        $customerRaw = $row['customer'] ?? $row['sold_to'] ?? $row['sell_to'] ?? null;

        $status = 'in_stock';
        // Mark as sold ONLY if actual sale details (date or customer) are provided
        if ($soldDateRaw || $customerRaw) {
            $status = 'sold';
        }

        // Condition Mapping
        $rawCondition = strtolower(trim((string)($row['condition'] ?? 'used')));
        $condition = 'used'; // Default
        if (str_contains($rawCondition, 'new')) {
            $condition = 'new';
        } elseif (str_contains($rawCondition, 'refurbished')) {
            $condition = 'refurbished';
        }
        // "used", "old", "pre-owned" all map to "used"

        // Create Mobile Unit
        $mobile = Mobile::create([
            'brand_id' => $brand->id,
            'model_id' => $model->id,
            'hsn_number' => $imei,
            'storage' => $rom,
            'ram' => $ram,
            'color' => trim((string)($row['color'] ?? '')),
            'condition_type' => $condition,
            'status' => $status,
            'notes' => 'Imported from sheet',
        ]);

        // Repair Cost Handling
        $repairCost = floatval($row['repair'] ?? $row['repair_cost'] ?? $row['fix_cost'] ?? $row['service_cost'] ?? 0);
        if ($repairCost > 0) {
            Repair::create([
                'mobile_id' => $mobile->id,
                'issue' => 'Imported Repair',
                'repair_cost' => $repairCost,
                'repair_status' => 'completed',
                'repair_date' => $purchaseDate->format('Y-m-d'),
                'notes' => 'Imported from spreadsheet',
            ]);
        }

        // Supplier/Customer for Buy Transaction
        $supplierRaw = $row['supplier'] ?? $row['purchase_from'] ?? 'Unknown Supplier';
        $supplierData = $this->parseNamePhone($supplierRaw);
        $supplier = Customer::firstOrCreate(
            ['phone' => $supplierData['phone'] ?? '0000000000'],
            [
                'name' => $supplierData['name'],
                'user_id' => auth()->id()
            ]
        );

        // Create Buy Invoice & Transaction
        $buyInvoice = Invoice::create([
            'customer_id' => $supplier->id,
            'invoice_no' => Traits::getInvoiceNumber(),
            'invoice_date' => $purchaseDate->format('Y-m-d'),
            'invoice_type' => 'buy',
            'subtotal' => $buyPrice,
            'grand_total' => $buyPrice,
            'paid_amount' => $buyPrice,
            'payment_status' => 'paid',
        ]);

        $buyTransaction = Transaction::create([
            'mobile_id' => $mobile->id,
            'customer_id' => $supplier->id,
            'transaction_type' => 'buy',
            'price' => $buyPrice,
            'transaction_date' => $purchaseDate->format('Y-m-d'),
            'invoice_no' => $buyInvoice->invoice_no,
        ]);

        InvoiceItem::create([
            'invoice_id' => $buyInvoice->id,
            'mobile_id' => $mobile->id,
            'transaction_id' => $buyTransaction->id,
            'qty' => 1,
            'price' => $buyPrice,
            'total' => $buyPrice,
        ]);

        // If Sold, Create Sell Invoice & Transaction
        if ($status === 'sold') {
            $customerRaw = $row['customer'] ?? $row['sold_to'] ?? 'Walking Customer';
            $customerData = $this->parseNamePhone($customerRaw);
            $customer = Customer::firstOrCreate(
                ['phone' => $customerData['phone'] ?? '1111111111'],
                [
                    'name' => $customerData['name'],
                    'user_id' => auth()->id()
                ]
            );

            // Sell Date
            $sellDateRaw = $row['sold_date'] ?? $row['sell_date'] ?? null;
            $sellDate = $this->parseDate($sellDateRaw) ?: now();

            $sellInvoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_no' => Traits::getInvoiceNumber(),
                'invoice_date' => $sellDate->format('Y-m-d'),
                'invoice_type' => 'sell',
                'subtotal' => $sellPrice,
                'grand_total' => $sellPrice,
                'paid_amount' => $sellPrice,
                'payment_status' => 'paid',
            ]);

            $sellTransaction = Transaction::create([
                'mobile_id' => $mobile->id,
                'customer_id' => $customer->id,
                'transaction_type' => 'sell',
                'price' => $sellPrice,
                'transaction_date' => $sellDate->format('Y-m-d'),
                'invoice_no' => $sellInvoice->invoice_no,
            ]);

            InvoiceItem::create([
                'invoice_id' => $sellInvoice->id,
                'mobile_id' => $mobile->id,
                'transaction_id' => $sellTransaction->id,
                'qty' => 1,
                'price' => $sellPrice,
                'total' => $sellPrice,
            ]);
        }
    }

    private function processAccessoryRow($row)
    {
        $this->accessoryCount++;
        
        $brand = Brand::firstOrCreate(['name' => trim($row['brand'] ?? 'Unknown')]);
        
        $buyPrice = floatval($row['purchase'] ?? $row['buy_price'] ?? 0);
        $sellPrice = floatval($row['sold'] ?? $row['sell_price'] ?? 0);

        $accessory = Accessory::create([
            'brand_id' => $brand->id,
            'name' => trim($row['model'] ?? 'Unknown Accessory'),
            'color' => trim((string)($row['color'] ?? '')),
            'purchase_price' => $buyPrice,
            'sale_price' => $sellPrice,
            'stock' => 1,
            'purchase_date' => $this->parseDate($row['date'] ?? $row['purchase_date']) ?? now(),
        ]);
    }

    private function parseDate($date)
    {
        if (!$date) return null;

        if (is_numeric($date)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
            } catch (\Exception $e) {
                // Fallback
            }
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseNamePhone($string)
    {
        if (empty($string)) {
            return [
                'name' => 'Unknown',
                'phone' => null,
            ];
        }

        if (preg_match('/^(.*?)\s*\(([\d\s+\-]+)\)\s*$/', $string, $matches)) {
            return [
                'name' => trim($matches[1]),
                'phone' => trim($matches[2]),
            ];
        }

        return [
            'name' => trim($string),
            'phone' => null,
        ];
    }
}
