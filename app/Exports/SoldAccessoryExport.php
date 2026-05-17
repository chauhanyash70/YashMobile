<?php

namespace App\Exports;

use App\Models\InvoiceItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SoldAccessoryExport implements FromCollection, WithHeadings, WithMapping
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
        return InvoiceItem::where('item_type', 'accessory')
            ->whereHas('invoice', function ($query) {
                $query->whereBetween('invoice_date', [$this->startDate, $this->endDate]);
            })
            ->with(['invoice.customer', 'accessory.brand'])
            ->get();
    }

    public function map($item): array
    {
        $accessory = $item->accessory;
        $invoice = $item->invoice;

        return [
            $invoice->invoice_date,
            $invoice->invoice_no,
            $invoice->customer ? $invoice->customer->name : 'Walking Customer',
            $accessory->brand->name ?? '',
            $accessory->name ?? '',
            $accessory->model ?? '',
            $accessory->color ?? '',
            $accessory->sku ?? '',
            $item->quantity,
            $item->price,
            $item->total,
            $item->total - (($accessory->purchase_price ?? 0) * $item->quantity), // Profit
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
            'SKU',
            'Quantity',
            'Unit Price',
            'Total',
            'Profit'
        ];
    }
}
