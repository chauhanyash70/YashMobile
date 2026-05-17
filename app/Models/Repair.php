<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id', 'mobile_id', 'issue', 'repair_cost', 'technician_name',
        'repair_status', 'repair_date', 'notes'
    ];

    public function mobile()
    {
        return $this->belongsTo(Mobile::class);
    }
}
