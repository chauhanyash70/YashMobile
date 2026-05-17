<?php

namespace App\Exports;

use App\Models\InvoiceItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoldDeviceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Get all InvoiceItems that belong to a "sell" invoice within the date range
        return InvoiceItem::whereNotNull('mobile_id')
            ->whereHas('invoice', function ($query) {
                $query->where('invoice_type', 'sell')
                      ->whereBetween('invoice_date', [$this->startDate, $this->endDate]);
            })
            ->with([
                'invoice.customer', 
                'mobile.brand', 
                'mobile.model', 
                'mobile.purchaseTransaction',
                'mobile.repairs',
                'mobile.expenses'
            ])
            ->get();
    }

    public function map($item): array
    {
        $mobile = $item->mobile;
        $invoice = $item->invoice;
        
        // Access eager loaded purchase transaction
        $buyTransaction = $mobile ? $mobile->purchaseTransaction : null;

        $buyPrice = $buyTransaction ? $buyTransaction->price : 0;
        $repairCost = $mobile ? $mobile->repair_cost : 0;
        $expenseAmount = $mobile ? $mobile->expense_amount : 0;
        $sellPrice = $item->price;
        $discount = $invoice->discount;
        $profit = $sellPrice - $buyPrice - $repairCost - $expenseAmount - $discount;

        return [
            $invoice->invoice_no ?? 'N/A',
            $buyTransaction ? Carbon::parse($buyTransaction->transaction_date)->format('Y-m-d') : 'N/A',
            $mobile?->brand?->name ?? 'N/A',
            $mobile?->model?->name ?? 'N/A',
            $mobile ? ($mobile->storage . ($mobile->ram ? " / {$mobile->ram}" : "")) : 'N/A',
            $mobile->hsn_number ?? 'N/A',
            $mobile->color ?? 'N/A',
            round((float) $buyPrice, 2),
            round((float) $repairCost, 2),
            round((float) $sellPrice, 2),
            round((float) $profit, 2),
            $invoice->invoice_date ? Carbon::parse($invoice->invoice_date)->format('Y-m-d') : 'N/A',
            $buyTransaction && $buyTransaction->customer ? $buyTransaction->customer->name.'('.$buyTransaction->customer->phone.')' : 'N/A',
            $invoice->customer ? $invoice->customer->name.'('.$invoice->customer->phone.')' : 'Cash Customer',
            Str::title(Str::replace('_', ' ', $invoice->payment_method)) ?? 'N/A'
        ];
    }

    public function headings(): array
    {
        return [
            'Invoice No',
            'Purchase Date',
            'Brand',
            'Model',
            'Storage',
            'IMEI',
            'Color',
            'Buy Price',
            'Repair Cost',
            'Sell Price',
            'Profit',
            'Sold Date',
            'Purchase From',
            'Sold To', 
            'Payment Mode'
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


