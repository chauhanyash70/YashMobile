<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mobile extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id', 'brand_id', 'model_id',
        'hsn_number',
        'color', 'storage', 'ram', 'battery_health',
        'condition_type', 'status', 'notes'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function model()
    {
        return $this->belongsTo(MobileModel::class, 'model_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function purchaseTransaction()
    {
        return $this->hasOne(Transaction::class)->where('transaction_type', 'buy');
    }

    public function getBuyPriceAttribute()
    {
        return $this->transactions()->where('transaction_type', 'buy')->sum('price');
    }

    public function getSellPriceAttribute()
    {
        return $this->transactions()->where('transaction_type', 'sell')->sum('price');
    }

    public function getRepairCostAttribute()
    {
        return $this->repairs()->sum('repair_cost');
    }

    public function getExpenseAmountAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    public function getProfitAttribute()
    {
        return $this->sell_price - ($this->buy_price + $this->repair_cost + $this->expense_amount);
    }
}
