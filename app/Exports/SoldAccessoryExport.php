<?php

namespace App\Exports;

use App\Models\InvoiceItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SoldAccessoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        return InvoiceItem::whereNotNull('accessory_id')
            ->whereHas('invoice', function ($query) {
                $query->where('invoice_type', 'sell')
                      ->whereBetween('invoice_date', [$this->startDate, $this->endDate]);
            })
            ->with(['invoice.customer', 'accessory.brand'])
            ->get();
    }

    public function map($item): array
    {
        $accessory = $item->accessory;
        $invoice = $item->invoice;
        
        $brandName = $accessory->brand->name ?? '';
        $accessoryName = $accessory->name ?? '';
        $model = $accessory->model ?? '';
        $color = $accessory->color ?? '';
        $hsn = $accessory->hsn ?? '';
        
        $purchasePrice = $accessory->purchase_price ?? 0;
        $salePrice = $accessory->sale_price ?? 0;
        $qty = $item->qty ?? 1;
        $discount = $item->discount ?? 0;
        $price = $item->price ?? 0;
        $total = $item->total ?? 0;
        
        $profit = $total - ($purchasePrice * $qty);

        return [
            $invoice->invoice_date ?? 'N/A',
            $invoice->invoice_no ?? 'N/A',
            $invoice->customer ? $invoice->customer->name : 'Walking Customer',
            $brandName,
            $accessoryName,
            $model,
            $color,
            $hsn,
            $qty,
            round($purchasePrice, 2),
            round($salePrice, 2),
            round($price, 2),
            round($discount, 2),
            round($total, 2),
            round($profit, 2),
        ];
    }

    public function headings(): array
    {
        return [
            'Invoice Date',
            'Invoice No',
            'Customer',
            'Brand',
            'Name',
            'Model',
            'Color',
            'HSN',
            'Quantity',
            'Purchase Price',
            'Sale Price',
            'Sold Price',
            'Discount',
            'Total',
            'Profit'
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
