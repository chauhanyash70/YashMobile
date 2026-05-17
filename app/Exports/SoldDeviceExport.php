<?php

namespace App\Exports;

use App\Models\InvoiceItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SoldDeviceExport implements FromCollection, WithHeadings, WithMapping
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
        // Get all InvoiceItems of type 'device' within the date range
        return InvoiceItem::where('item_type', 'device')
            ->whereHas('invoice', function ($query) {
                $query->whereBetween('invoice_date', [$this->startDate, $this->endDate]);
            })
            ->with(['invoice.customer', 'device.brand', 'device.model', 'device.purchaseItem.purchase.supplier'])
            ->get();
    }

    public function map($item): array
    {
        $device = $item->device;
        $invoice = $item->invoice;
        $invoiceDate = $invoice->invoice_date;
        $purchaseItem = \App\Models\PurchaseItem::where('imei_id', $item->imei_id)
            ->whereHas('purchase', function ($query) use ($invoiceDate) {
                $query->where('purchase_date', '<=', $invoiceDate);
            })
            ->with('purchase')
            ->get()
            ->sortByDesc(function ($pi) {
                return $pi->purchase->purchase_date;
            })
            ->first();
        if (!$purchaseItem) {
             $purchaseItem = \App\Models\PurchaseItem::where('imei_id', $item->imei_id)->latest()->first();
        }
        $purchase = $purchaseItem->purchase ?? null;
        $supplier = $purchase->supplier ?? null;
        
        $imei = \App\Models\DeviceImei::find($item->imei_id);

        return [
            $invoice->invoice_no ?? '',
            $purchase ? Carbon::parse($purchase->purchase_date)->format('Y-m-d') : '',
            $device->brand->name ?? '',
            $device->model->name ?? '',
            $device->storage ? $device->storage . ($device->ram ? ' (' . $device->ram . ')' : '') : '',
            $device->color ?? '',
            $imei ? $imei->imei : '',
            $purchaseItem ? $purchaseItem->price : 0,
            $purchaseItem ? $purchaseItem->repair_cost : 0,
            $item->price, // Sell Price
            ($item->price - ($purchaseItem ? $purchaseItem->price : 0) - ($purchaseItem ? $purchaseItem->repair_cost : 0)) ?? 0, // Profit
            $invoice->invoice_date, // Sold Date
            $supplier ? $supplier->name . ' (' . $supplier->phone . ')' : '',
            $invoice->customer ? $invoice->customer->name . ' (' . $invoice->customer->phone . ')' : ''
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
            'Color',
            'IMEI',
            'Buy Price',
            'Repair Cost',
            'Sell Price',
            'Profit',
            'Sold Date',
            'Purchase From',
            'Sold To'
        ];
    }
}
