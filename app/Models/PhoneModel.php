<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PhoneModel extends Model
{
    protected $fillable = ['brand_id','name','sku'];
    public function brand() { return $this->belongsTo(Brand::class); }
    public function devices() { return $this->hasMany(Device::class, 'model_id'); }
}
