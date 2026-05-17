<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DeviceImei extends Model
{
    protected $fillable = ['device_id', 'imei', 'status'];
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function purchaseItem()
    {
        return $this->hasOne(PurchaseItem::class, 'imei_id');
    }

    public function invoiceItem()
    {
        return $this->hasOne(InvoiceItem::class, 'imei_id');
    }
}
