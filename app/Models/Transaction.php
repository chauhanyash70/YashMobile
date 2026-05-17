<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id',
        'mobile_id',
        'accessory_id',
        'customer_id',
        'transaction_type',
        'price',
        'payment_method',
        'bajaj_approval_number',
        'transaction_date',
        'invoice_no',
        'notes'
    ];

    public function mobile()
    {
        return $this->belongsTo(Mobile::class, 'mobile_id');
    }

    public function accessory()
    {
        return $this->belongsTo(Accessory::class, 'accessory_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_no', 'invoice_no');
    }

    public function invoiceItem()
    {
        return $this->hasOne(InvoiceItem::class, 'transaction_id');
    }
}
