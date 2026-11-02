<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = [
        'user_id', 'invoice_no', 'customer_id', 'invoice_type',
        'subtotal', 'discount', 'tax_amount', 'grand_total',
        'paid_amount', 'due_amount', 'payment_status',
        'payment_method', 'finance_company_id', 'approval_number', 'bajaj_approval_number', 'invoice_date', 'notes'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function financeCompany()
    {
        return $this->belongsTo(FinanceCompany::class, 'finance_company_id');
    }

    public function getDisplayPaymentMethodAttribute()
    {
        if ($this->financeCompany) {
            return $this->financeCompany->name;
        }
        if ($this->payment_method === 'bajaj_finance') {
            return 'Bajaj Finance';
        }
        return ucfirst(str_replace('_', ' ', $this->payment_method ?? 'cash'));
    }

    public function getDisplayApprovalNumberAttribute()
    {
        return $this->approval_number ?: $this->bajaj_approval_number;
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function recalculateTotals()
    {
        $this->subtotal = $this->items()->sum(\DB::raw('price * qty'));
        $this->grand_total = $this->subtotal - $this->discount + $this->tax_amount;
        $this->due_amount = $this->grand_total - $this->paid_amount;
        
        if ($this->due_amount <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }

        $this->save();
    }
}
