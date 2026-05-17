<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'invoice_date',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_method',
        'notes'
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
