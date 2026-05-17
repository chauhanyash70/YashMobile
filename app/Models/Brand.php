<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'slug', 'type'];
    public function devices() { return $this->hasMany(Device::class); }
}
