<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use App\Models\Device;
use App\Models\Accessory;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'item_type',
        'item_id',
        'quantity',
        'price',
        'total',
        'imei_id',
        'repair_cost',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function deviceImei()
    {
        return $this->belongsTo(DeviceImei::class, 'imei_id');
    }

    public function item(): MorphTo
    {
        return $this->morphTo()->morphWith([
            Device::class => ['brand', 'model', 'imei'],
            Accessory::class => [],
        ]);
    }
}
