<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Device;
use App\Models\Accessory;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_id',
        'quantity',
        'price',
        'total',
        'imei_id',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deviceImei()
    {
        return $this->belongsTo(DeviceImei::class, 'imei_id');
    }

    /**
     * Get the parent item model (device or accessory).
     */
    public function item()
    {
        return $this->morphTo(__FUNCTION__, 'item_type', 'item_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'item_id');
    }

    public function accessory()
    {
        return $this->belongsTo(Accessory::class, 'item_id');
    }
}
