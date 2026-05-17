<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Accessory extends Model
{
    protected $fillable = ['brand_id', 'name', 'model', 'color', 'sku', 'description', 'purchase_price', 'sale_price', 'stock', 'purchase_date'];

    public function brand() 
    {
        return $this->belongsTo(Brand::class);
    }
}
