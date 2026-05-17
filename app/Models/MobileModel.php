<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileModel extends Model
{
    use HasFactory, BelongsToUser;

    protected $table = 'models';

    protected $fillable = ['user_id', 'brand_id', 'name', 'model_number'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function mobiles()
    {
        return $this->hasMany(Mobile::class, 'model_id');
    }
}
