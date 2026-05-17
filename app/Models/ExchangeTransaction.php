<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ExchangeTransaction extends Model
{
    protected $fillable = ['customer_id','old_device_id','new_device_id','old_device_value','new_device_price','difference_amount','notes'];
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function oldDevice(){ return $this->belongsTo(Device::class,'old_device_id'); }
    public function newDevice(){ return $this->belongsTo(Device::class,'new_device_id'); }
}
