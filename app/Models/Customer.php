<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, BelongsToUser;

    protected $fillable = ['user_id', 'name', 'phone', 'email', 'city', 'address', 'profile_image', 'documents'];
    
    protected $casts = [
        'documents' => 'array'
    ];

    public static function standardizePhoneNumber($phone)
    {
        if (empty($phone)) {
            return $phone;
        }
        
        // Remove all non-digits except +
        $clean = preg_replace('/[^\d+]/', '', $phone);
        
        // If it starts with +, return it directly to avoid double prefixing
        if (str_starts_with($clean, '+')) {
            return $clean;
        }
        
        // If it starts with 91 and has 12 digits, prepend +
        if (str_starts_with($clean, '91') && strlen($clean) === 12) {
            return '+' . $clean;
        }
        
        // Strip leading 0
        $core = ltrim($clean, '0');
        
        // If the remaining core is exactly 10 digits, prepend +91
        if (strlen($core) === 10) {
            return '+91' . $core;
        }
        
        return $clean;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = self::standardizePhoneNumber($value);
    }

    public static function updateOrCreate(array $attributes, array $values = [])
    {
        if (isset($attributes['phone'])) {
            $attributes['phone'] = self::standardizePhoneNumber($attributes['phone']);
        }
        return static::query()->updateOrCreate($attributes, $values);
    }

    public static function firstOrCreate(array $attributes, array $values = [])
    {
        if (isset($attributes['phone'])) {
            $attributes['phone'] = self::standardizePhoneNumber($attributes['phone']);
        }
        return static::query()->firstOrCreate($attributes, $values);
    }



    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getProfileUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }
}
