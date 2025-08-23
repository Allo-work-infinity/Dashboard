<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory;

    protected $table = 'job_applications';

    /** Use applied_at / updated_at instead of created_at / updated_at */
    public const CREATED_AT = 'applied_at';
    public const UPDATED_AT = 'updated_at';

    /* -------- Status enums -------- */
    public const STATUS_SUBMITTED    = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_SHORTLISTED  = 'shortlisted';
    public const STATUS_REJECTED     = 'rejected';
    public const STATUS_ACCEPTED     = 'accepted';

    public const STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_SHORTLISTED,
        self::STATUS_REJECTED,
        self::STATUS_ACCEPTED,
    ];

    protected $fillable = [
        'user_id',
        'job_offer_id',
        'status',
        'cv_file_url',
        'additional_documents', // JSON
        'admin_notes',
        'reviewed_by',          // FK to users.id (admin/staff)
        'reviewed_at',
        'response_message',
        // applied_at & updated_at are handled by timestamps
    ];

    protected $casts = [
        'additional_documents' => 'array',
        'reviewed_at'          => 'datetime',
        'applied_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_SUBMITTED,
    ];

    protected $appends = ['is_final'];

    /* -------- Relationships -------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /* -------- Scopes -------- */

    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeForOffer($q, int $offerId)
    {
        return $q->where('job_offer_id', $offerId);
    }

    public function scopeStatus($q, string $status)
    {
        return $q->where('status', $status);
    }

    /* -------- Computed helpers -------- */

    public function getIsFinalAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_REJECTED], true);
    }
}
