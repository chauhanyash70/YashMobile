<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'brand_id',
        'model_id',
        'storage',
        'ram',
        'color',
        'condition',
        'status',
        'buy_price',
        'stock',
        'purchase_date'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function model()
    {
        return $this->belongsTo(PhoneModel::class, 'model_id');
    }

    public function imei()
    {
        return $this->hasOne(DeviceImei::class);
    }
    public function imeis()
    {
        return $this->hasMany(DeviceImei::class);
    }
    public function transactions()
    {
        return $this->hasMany(DeviceTransaction::class);
    }

    public function purchaseItems()
    {
        return $this->morphMany(PurchaseItem::class, 'item');
    }
    public function purchaseItem()
    {
        return $this->morphOne(PurchaseItem::class, 'item');
    }
    public function invoiceItems()
    {
        return $this->morphMany(InvoiceItem::class, 'item');
    }
}
