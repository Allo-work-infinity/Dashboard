<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    public const COMPANY_SIZES = ['startup', 'small', 'medium', 'large', 'enterprise'];

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'description',
        'industry',
        'company_size',
        'website',
        'logo_url',
        'address',
        'city',
        'governorate',
        'contact_email',
        'contact_phone',
        'is_verified',
        'status',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    protected $attributes = [
        'is_verified' => false,
        'status'      => self::STATUS_ACTIVE,
    ];

    /* Scopes */
    public function scopeActive($q)
    {
        return $q->where('status', self::STATUS_ACTIVE);
    }

    public function scopeVerified($q)
    {
        return $q->where('is_verified', true);
    }

    public function scopeSize($q, string $size)
    {
        return $q->where('company_size', $size);
    }
}
