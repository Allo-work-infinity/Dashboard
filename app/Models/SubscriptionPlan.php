<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'description',
        'price',          // Decimal(10,3)
        'duration_days',  // Integer
        'features',       // JSON
        'is_active',      // Boolean
    ];

    protected $casts = [
        'price'         => 'decimal:3',
        'duration_days' => 'integer',
        'features'      => 'array',
        'is_active'     => 'boolean',
    ];

    // Default values (also set defaults in the migration)
    protected $attributes = [
        'is_active' => true,
    ];

    /** Quick filter: only active plans */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Many-to-many: SubscriptionPlan <-> JobOffer
     * Pivot table: job_offer_subscription_plan
     */
    public function jobOffers(): BelongsToMany
    {
        return $this->belongsToMany(
            JobOffer::class,
            'job_offer_subscription_plan', // pivot table
            'subscription_plan_id',        // this model's FK
            'job_offer_id'                 // related model's FK
        )->withTimestamps();
    }
}
