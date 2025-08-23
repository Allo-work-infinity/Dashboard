<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobOffer extends Model
{
    use HasFactory;

    protected $table = 'job_offers';

    /* ---------- Enums ---------- */
    public const TYPE_FULL_TIME  = 'full_time';
    public const TYPE_PART_TIME  = 'part_time';
    public const TYPE_CONTRACT   = 'contract';
    public const TYPE_INTERNSHIP = 'internship';
    public const TYPE_REMOTE     = 'remote';
    public const TYPES = [
        self::TYPE_FULL_TIME, self::TYPE_PART_TIME,
        self::TYPE_CONTRACT,  self::TYPE_INTERNSHIP, self::TYPE_REMOTE,
    ];

    public const LEVEL_ENTRY  = 'entry';
    public const LEVEL_JUNIOR = 'junior';
    public const LEVEL_MID    = 'mid';
    public const LEVEL_SENIOR = 'senior';
    public const LEVEL_LEAD   = 'lead';
    public const LEVELS = [
        self::LEVEL_ENTRY, self::LEVEL_JUNIOR, self::LEVEL_MID, self::LEVEL_SENIOR, self::LEVEL_LEAD,
    ];

    public const STATUS_DRAFT   = 'draft';
    public const STATUS_ACTIVE  = 'active';
    public const STATUS_PAUSED  = 'paused';
    public const STATUS_CLOSED  = 'closed';
    public const STATUSES = [
        self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_PAUSED, self::STATUS_CLOSED,
    ];

    /* ---------- Mass-assignable ---------- */
    protected $fillable = [
        'company_id',
        'category_id', 
        'reference',
        'title',
        'description',
        'requirements',
        'responsibilities',
        'job_type',
        'experience_level',
        'salary_min',
        'salary_max',
        'currency',
        'location',
        'city',
        'governorate',
        'remote_allowed',
        'skills_required',
        'benefits',
        'application_deadline',
        'is_featured',
        'status',
        'views_count',
        'applications_count',
    ];

    /* ---------- Casts ---------- */
    protected $casts = [
        'salary_min'           => 'decimal:3',
        'salary_max'           => 'decimal:3',
        'remote_allowed'       => 'boolean',
        'is_featured'          => 'boolean',
        'skills_required'      => 'array',
        'benefits'             => 'array',
        'application_deadline' => 'datetime',
        'views_count'          => 'integer',
        'applications_count'   => 'integer',
    ];

    /* ---------- Defaults ---------- */
    protected $attributes = [
        'currency'           => 'TND',
        'remote_allowed'     => false,
        'is_featured'        => false,
        'status'             => self::STATUS_DRAFT,
        'views_count'        => 0,
        'applications_count' => 0,
    ];

    protected $appends = ['is_open'];

    /* ---------- Relationships ---------- */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Many-to-many: JobOffer <-> SubscriptionPlan
     * Pivot table: job_offer_subscription_plan
     * (add .withPivot(...) later if you store extra fields)
     */
    public function subscriptionPlans(): BelongsToMany
    {
        return $this->belongsToMany(
            SubscriptionPlan::class,
            'job_offer_subscription_plan', // pivot table
            'job_offer_id',                // this model's FK
            'subscription_plan_id'         // related model's FK
        )->withTimestamps();
    }

    /* ---------- Scopes ---------- */
    public function scopeActive($q)   { return $q->where('status', self::STATUS_ACTIVE); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }
    public function scopeRemote($q)   { return $q->where('remote_allowed', true); }

    public function scopeType($q, string $type)
    {
        return $q->where('job_type', $type);
    }

    public function scopeExperience($q, string $level)
    {
        return $q->where('experience_level', $level);
    }

    public function scopeInGovernorate($q, string $gov)
    {
        return $q->where('governorate', $gov);
    }

    /** Open = active AND (no deadline OR deadline in the future) */
    public function scopeOpen($q)
    {
        return $q->where('status', self::STATUS_ACTIVE)
                 ->where(function ($qq) {
                     $qq->whereNull('application_deadline')
                        ->orWhere('application_deadline', '>=', now());
                 });
    }

    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        return $q->where(function ($qq) use ($term) {
            $qq->where('title', 'like', "%{$term}%")
               ->orWhere('city', 'like', "%{$term}%")
               ->orWhere('governorate', 'like', "%{$term}%")
               ->orWhere('location', 'like', "%{$term}%")
               ->orWhereHas('company', fn($cq) => $cq->where('name', 'like', "%{$term}%"));
        });
    }

    /* ---------- Computed ---------- */
    public function getIsOpenAttribute(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) return false;
        return is_null($this->application_deadline) || $this->application_deadline->greaterThanOrEqualTo(now());
    }

    // public function categories(): BelongsToMany
    // {
    //     return $this->belongsToMany(
    //         Category::class,
    //         'category_job_offer', // pivot table
    //         'job_offer_id',       // this model's FK
    //         'category_id'         // related model's FK
    //     )->withTimestamps();
    // }
    // // app/Models/JobOffer.php
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_job_offer', 'job_offer_id', 'category_id')
                    ->withTimestamps();
    }

}
