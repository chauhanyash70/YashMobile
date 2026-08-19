<?php

namespace App\Exports;

use App\Models\Mobile;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class MobileExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Mobile::query()
            ->with([
                'brand',
                'model',
                'purchaseTransaction.customer',
                'transactions' => function ($q) {
                    $q->where('transaction_type', 'sell')->with('customer');
                },
                'invoiceItems' => function ($q) {
                    $q->whereHas('invoice', function ($iq) {
                        $iq->where('invoice_type', 'sell');
                    })->with('invoice.customer');
                },
                'repairs',
                'expenses'
            ])
            ->leftJoin('brands', 'mobiles.brand_id', '=', 'brands.id')
            ->leftJoin('models', 'mobiles.model_id', '=', 'models.id')
            ->whereIn('mobiles.id', function ($q) {
                $q->select(DB::raw('MAX(id)'))
                    ->from('mobiles')
                    ->where('user_id', auth()->id())
                    ->groupBy('hsn_number');
            })
            ->select('mobiles.*');

        if (!empty($this->filters['brand_id'])) {
            $query->where('mobiles.brand_id', $this->filters['brand_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('mobiles.status', $this->filters['status']);
        }

        if (!empty($this->filters['condition'])) {
            $query->where('mobiles.condition_type', $this->filters['condition']);
        }

        if (!empty($this->filters['from_date'])) {
            $query->whereDate('mobiles.created_at', '>=', $this->filters['from_date']);
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate('mobiles.created_at', '<=', $this->filters['to_date']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('brands.name', 'like', "%{$search}%")
                    ->orWhere('models.name', 'like', "%{$search}%")
                    ->orWhere('mobiles.hsn_number', 'like', "%{$search}%")
                    ->orWhere('mobiles.color', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('mobiles.id', 'DESC')->get();
    }

    public function map($mobile): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $buyPrice = $mobile->buy_price;
        $sellPrice = $mobile->sell_price;
        $profit = $mobile->profit;
        $customer = $mobile->purchaseTransaction?->customer;
        $purchaseFrom = $customer ? "{$customer->name} ({$customer->phone})" : 'N/A';

        $soldTo = 'N/A';
        $soldDate = 'N/A';

        // Set Sold To & Sold Date ONLY if the mobile is actually sold
        if ($mobile->status === 'sold') {
            $lastInvoiceItem = $mobile->invoiceItems ? $mobile->invoiceItems->sortByDesc('id')->first() : null;
            $sellTransaction = $mobile->transactions ? $mobile->transactions->where('transaction_type', 'sell')->sortByDesc('id')->first() : null;

            if ($lastInvoiceItem && $lastInvoiceItem->invoice) {
                $soldCustomer = $lastInvoiceItem->invoice->customer;
                $soldTo = $soldCustomer ? "{$soldCustomer->name} ({$soldCustomer->phone})" : 'Cash Customer';
                $soldDate = $lastInvoiceItem->invoice->invoice_date
                    ? Carbon::parse($lastInvoiceItem->invoice->invoice_date)->format('Y-m-d')
                    : ($lastInvoiceItem->created_at ? Carbon::parse($lastInvoiceItem->created_at)->format('Y-m-d') : 'N/A');
            } elseif ($sellTransaction) {
                $soldCustomer = $sellTransaction->customer;
                $soldTo = $soldCustomer ? "{$soldCustomer->name} ({$soldCustomer->phone})" : 'Cash Customer';
                $soldDate = $sellTransaction->transaction_date
                    ? Carbon::parse($sellTransaction->transaction_date)->format('Y-m-d')
                    : ($sellTransaction->created_at ? Carbon::parse($sellTransaction->created_at)->format('Y-m-d') : 'N/A');
            }
        }

        return [
            $rowNumber,
            $mobile->created_at ? Carbon::parse($mobile->created_at)->format('Y-m-d') : 'N/A',
            $mobile->brand->name ?? 'N/A',
            $mobile->model->name ?? 'N/A',
            $mobile->hsn_number ?: 'N/A',
            $mobile->ram ?: 'N/A',
            $mobile->storage ?: 'N/A',
            $mobile->color ?: 'N/A',
            $mobile->battery_health ? $mobile->battery_health . '%' : 'N/A',
            ucfirst($mobile->condition_type ?? 'N/A'),
            ucfirst(str_replace('_', ' ', $mobile->status ?? 'N/A')),
            round((float) $buyPrice, 2),
            $sellPrice > 0 ? round((float) $sellPrice, 2) : 'N/A',
            $sellPrice > 0 ? round((float) $profit, 2) : 'N/A',
            $purchaseFrom,
            $soldTo,
            $soldDate,
            $mobile->notes ?: 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Purchase Date',
            'Brand',
            'Model',
            'HSN / IMEI',
            'RAM',
            'Storage',
            'Color',
            'Battery Health',
            'Condition',
            'Status',
            'Buy Price',
            'Sell Price',
            'Profit',
            'Purchase From',
            'Sold To',
            'Sold Date',
            'Notes',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0']
                ]
            ],
        ];
    }
}
