<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Accessory extends Model
{
    use BelongsToUser;

    protected $fillable = ['user_id', 'brand_id', 'name', 'model', 'color', 'hsn', 'description', 'purchase_price', 'sale_price', 'stock', 'purchase_date'];

    public function brand() 
    {
        return $this->belongsTo(Brand::class);
    }


}
