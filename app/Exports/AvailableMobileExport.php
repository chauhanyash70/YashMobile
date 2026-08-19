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

class AvailableMobileExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Mobile::query()
            ->with(['brand', 'model', 'purchaseTransaction.customer', 'transactions', 'repairs', 'expenses'])
            ->leftJoin('brands', 'mobiles.brand_id', '=', 'brands.id')
            ->leftJoin('models', 'mobiles.model_id', '=', 'models.id')
            ->where('mobiles.status', 'in_stock')
            ->where('mobiles.user_id', auth()->id())
            ->select('mobiles.*');

        if (!empty($this->filters['brand_id'])) {
            $query->where('mobiles.brand_id', $this->filters['brand_id']);
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
        $customer = $mobile->purchaseTransaction?->customer;
        $customerInfo = $customer ? "{$customer->name} ({$customer->phone})" : 'N/A';

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
            'In Stock',
            round((float) $buyPrice, 2),
            $customerInfo,
            $mobile->notes ?: 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Date Added',
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
            'Purchase From',
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
