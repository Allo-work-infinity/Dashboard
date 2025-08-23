<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Optional: keep allowed status values centralized
    public const STATUSES = ['active', 'suspended', 'banned'];

    protected $fillable = [
        'email',
        'password',                 // stored hashed via cast below
        'first_name',
        'last_name',
        'phone',
        'date_of_birth',
        'address',
        'city',
        'governorate',
        'profile_picture_url',
        'cv_file_url',
        'is_email_verified',        // boolean column (default false)
        'email_verification_token',
        'password_reset_token',
        'password_reset_expires',
        'status',                   // enum: active|suspended|banned
        'last_access_time',
        'is_admin',                 // <<< your admin flag
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
        'password_reset_token',
    ];

    protected $casts = [
        'password'               => 'hashed',
        'email_verified_at'      => 'datetime', // keep if you also use Laravel’s native verification
        'is_email_verified'      => 'boolean',
        'is_admin'               => 'boolean',
        'date_of_birth'          => 'date',
        'password_reset_expires' => 'datetime',
        'last_access_time'       => 'datetime',
    ];

    // Always expose a read-only "name" derived from first/last (keeps old code working)
    protected $appends = ['name'];

    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')));
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
