<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'mobile_id',
        'accessory_id',
        'transaction_id',
        'qty',
        'price',
        'discount',
        'tax_amount',
        'total',
        'is_bought_back'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function mobile()
    {
        return $this->belongsTo(Mobile::class, 'mobile_id');
    }

    public function accessory()
    {
        return $this->belongsTo(Accessory::class, 'accessory_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
