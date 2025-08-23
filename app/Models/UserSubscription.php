<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    protected $table = 'user_subscriptions';

    /* -------- Status constants -------- */
    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
    ];

    /* -------- Payment status constants -------- */
    public const PAY_PENDING   = 'pending';
    public const PAY_COMPLETED = 'completed';
    public const PAY_FAILED    = 'failed';
    public const PAY_REFUNDED  = 'refunded';

    public const PAYMENT_STATUSES = [
        self::PAY_PENDING,
        self::PAY_COMPLETED,
        self::PAY_FAILED,
        self::PAY_REFUNDED,
    ];

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'payment_status',
        'payment_id',
        'transaction_id',
        'payment_method',
        'amount_paid',
        'start_date',
        'end_date',
        'auto_renewal',
    ];

    protected $casts = [
        'amount_paid'  => 'decimal:3',
        'start_date'   => 'datetime',
        'end_date'     => 'datetime',
        'auto_renewal' => 'boolean',
    ];

    protected $appends = ['is_current', 'remaining_days'];

    /* -------- Relationships -------- */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /* -------- Scopes -------- */
    /** Active and not past end_date (or no end_date set) */
    public function scopeCurrent($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now());
                     });
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAutoRenewing($query)
    {
        return $query->where('auto_renewal', true);
    }

    /* -------- Helpers / Computed -------- */
    public function getIsCurrentAttribute(): bool
    {
        $active = $this->status === self::STATUS_ACTIVE;
        $notExpired = is_null($this->end_date) || $this->end_date->greaterThanOrEqualTo(now());
        return $active && $notExpired;
    }

    public function getRemainingDaysAttribute(): ?int
    {
        if (! $this->end_date instanceof Carbon) {
            return null;
        }
        return max(0, now()->diffInDays($this->end_date, false));
    }
}
