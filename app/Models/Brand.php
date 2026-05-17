<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = ['user_id', 'name', 'slug', 'type'];

    public function models()
    {
        return $this->hasMany(MobileModel::class, 'brand_id');
    }

    public function phoneModels()
    {
        return $this->hasMany(MobileModel::class, 'brand_id');
    }

    public function mobiles()
    {
        return $this->hasMany(Mobile::class);
    }

    public function accessories()
    {
        return $this->hasMany(Accessory::class);
    }

}
