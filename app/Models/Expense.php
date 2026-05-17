<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id', 'mobile_id', 'title', 'amount', 'expense_date', 'notes'
    ];

    public function mobile()
    {
        return $this->belongsTo(Mobile::class);
    }
}
