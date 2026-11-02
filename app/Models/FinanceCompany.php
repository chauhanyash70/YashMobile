<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceCompany extends Model
{
    use HasFactory;

    protected $table = 'finance_companies';

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'status',
        'is_active',
    ];

    /**
     * Scope query to active companies (auto-detects 'status' or 'is_active' column safely).
     */
    public function scopeActive($query)
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('finance_companies', 'status')) {
            return $query->where('status', 'active');
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('finance_companies', 'is_active')) {
            return $query->where(function ($q) {
                $q->where('is_active', 1)
                  ->orWhere('is_active', true)
                  ->orWhere('is_active', 'active');
            });
        }

        return $query;
    }

    /**
     * Helper accessor for boolean check.
     */
    public function getIsActiveAttribute(): bool
    {
        if (isset($this->attributes['status'])) {
            return $this->attributes['status'] === 'active';
        }

        return !empty($this->attributes['is_active']);
    }

    /**
     * Scope query to companies available for the user (global or user-specific).
     */
    public function scopeAvailableForUser($query, $userId = null)
    {
        $userId = $userId ?: (\Illuminate\Support\Facades\Auth::id() ?? null);

        $query = $this->scopeActive($query);

        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'finance_company_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'finance_company_id');
    }
}

