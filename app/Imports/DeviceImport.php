<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Brand;
use App\Models\PhoneModel;
use App\Models\Device;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\DeviceImei;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeviceImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            // Group by Purchase
            $purchases = $rows->groupBy(function ($item) {
                return $item['purchase_date'] . '_' . $item['purchase_from'];
            });

            foreach ($purchases as $key => $purchaseRows) {
                $firstRow = $purchaseRows->first();
                $purchaseDate = $this->parseDate($firstRow['purchase_date']);

                // Create or find Supplier
                $supplierData = $this->parseNamePhone($firstRow['purchase_from']);
                $supplier = Supplier::firstOrCreate(
                    ['name' => $supplierData['name']],
                    ['phone' => $supplierData['phone']]
                );

                $purchase = Purchase::create([
                    'supplier_id' => $supplier->id,
                    'purchase_date' => $purchaseDate,
                    'total_amount' => $purchaseRows->sum('buy_price'),
                    'paid_amount' => $purchaseRows->sum('buy_price'), // Assuming fully paid
                    'due_amount' => 0,
                    'status' => 'completed',
                ]);
                foreach ($purchaseRows as $row) {
                    $this->processRow($row, $purchase);
                }
            }
        });
    }

    private function processRow($row, $purchase)
    {
        // Skip if Brand or Model is missing
        if (empty($row['brand']) || empty($row['model']) || empty($row['imei'])) {
            return;
        }

        // Brand & Model
        $brand = Brand::firstOrCreate(['name' => trim($row['brand'])]);
        $model = PhoneModel::firstOrCreate([
            'brand_id' => $brand->id,
            'name' => trim($row['model'])
        ]);

        // Parse Storage & RAM
        $rawStorage = trim($row['storage']);
        $clean = preg_replace('/[^0-9+]/', '', $rawStorage);
        $storageData = explode('+', $clean);
        $ram = '';
        $storage = '';

        if (count($storageData) === 2) {
            $ram = $storageData[0];
            $storage = $storageData[1];
        } elseif (count($storageData) === 1) {
            $storage = $storageData[0];
        }

        $ramString = $ram ? $ram . ' GB' : '';
        $storageString = $storage ? $storage . ' GB' : '';

        // Device Model Profile
        $device = Device::firstOrCreate(
            [
                'brand_id' => $brand->id,
                'model_id' => $model->id,
                'ram' => $ramString,
                'storage' => $storageString,
                'color' => trim($row['color'] ?? ''),
            ],
            [
                'buy_price' => $row['buy_price'] ?? 0,
                'sell_price' => $row['sell_price'] ?? 0,
                'stock' => 0, // Will be incremented below
                'condition' => 'old',
            ]
        );

        // Create individual IMEI
        $status = $row['sold_date'] ? 'sold' : 'available';
        $imeiRecord = DeviceImei::firstOrCreate(
            ['imei' => trim($row['imei'])],
            [
                'device_id' => $device->id,
                'status' => $status
            ]
        );

        // Update Device Stock if available
        if ($status === 'available') {
            $device->increment('stock');
        }

        // Purchase Item
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'item_type' => 'device',
            'item_id' => $device->id,
            'imei_id' => $imeiRecord->id,
            'quantity' => 1,
            'price' => $row['buy_price'] ?? 0,
            'repair_cost' => $row['repair_cost'] ?? 0,
            'total' => $row['buy_price'] ?? 0,
        ]);

        // Invoice (if sold)
        if ($row['sold_date']) {
            $soldDate = $this->parseDate($row['sold_date']);
            $toCustomerData = $this->parseNamePhone($row['sold_to'] ?? 'Walking Customer');

            $customer = Customer::firstOrCreate(
                ['phone' => $toCustomerData['phone']],
                ['name' => $toCustomerData['name']]
            );

            $invoice = Invoice::firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'invoice_date' => $soldDate->format('Y-m-d'),
                ],
                [
                    'invoice_no' => \App\Http\Traits\Traits::getInvoiceNumber(),
                    'total_amount' => 0,
                    'paid_amount' => 0,
                    'due_amount' => 0,
                    'payment_method' => 'Cash',
                ]
            );

            // Update Invoice Totals
            $sellPrice = $row['sell_price'] ?? 0;
            $invoice->increment('total_amount', $sellPrice);
            $invoice->increment('paid_amount', $sellPrice);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'device',
                'item_id' => $device->id,
                'imei_id' => $imeiRecord->id,
                'quantity' => 1,
                'price' => $sellPrice,
                'total' => $sellPrice,
            ]);
        }
    }

    private function parseDate($date)
    {
        if (is_numeric($date)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
            } catch (\Exception $e) {
                // Fallback if numeric but fails conversion
            }
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return now();
        }
    }

    private function parseNamePhone($string)
    {
        preg_match('/^(.*?)\s*\((\d+)\)$/', $string, $matches);

        $name = trim($matches[1]);
        $phone = $matches[2];

        return [
            'name' => trim($name ?? $string),
            'phone' => $phone ?? null,
        ];
    }

}
