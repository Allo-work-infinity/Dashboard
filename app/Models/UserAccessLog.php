<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserAccessLog extends Model
{
    use HasFactory;

    protected $table = 'user_access_logs';

    /** Restriction window in hours */
    public const RESTRICT_HOURS = 6;

    protected $fillable = [
        'user_id',
        'access_time',
        'ip_address',
        'user_agent',
        'session_duration',   // in minutes
        'pages_visited',      // JSON
        'actions_performed',  // JSON
    ];

    protected $casts = [
        'access_time'       => 'datetime',
        'session_duration'  => 'integer',
        'pages_visited'     => 'array',
        'actions_performed' => 'array',
    ];

    protected $attributes = [
        'session_duration' => 0,
    ];

    protected $appends = ['next_allowed_at', 'is_within_restriction'];

    /* ---------- Relationships ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ---------- Scopes ---------- */

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSince($query, Carbon|string $from)
    {
        return $query->where('access_time', '>=', Carbon::parse($from));
    }

    /** Logs within the last RESTRICT_HOURS for the given user */
    public function scopeWithinRestrictionWindow($query, int $userId)
    {
        return $query->forUser($userId)->since(now()->subHours(self::RESTRICT_HOURS));
    }

    /* ---------- Computed ---------- */

    public function getNextAllowedAtAttribute(): ?Carbon
    {
        return $this->access_time ? $this->access_time->copy()->addHours(self::RESTRICT_HOURS) : null;
    }

    public function getIsWithinRestrictionAttribute(): bool
    {
        return $this->access_time
            ? $this->access_time->greaterThan(now()->subHours(self::RESTRICT_HOURS))
            : false;
    }

    /* ---------- Helpers ---------- */

    /** Check if a user is currently restricted (has an access within last RESTRICT_HOURS) */
    public static function userIsRestricted(int $userId): bool
    {
        return static::withinRestrictionWindow($userId)->exists();
    }

    /** When can the user access again? (null if not restricted or no logs) */
    public static function userNextAllowedAt(int $userId): ?Carbon
    {
        $last = static::forUser($userId)->latest('access_time')->first();
        return $last ? $last->next_allowed_at : null;
    }
}
