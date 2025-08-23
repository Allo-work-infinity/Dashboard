<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    /* ---------- Mass-assignable ---------- */
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /* ---------- Casts ---------- */
    protected $casts = [
        'name'        => 'string',
        'slug'        => 'string',
        'description' => 'string',
    ];

    /* ---------- Relationships ---------- */

    /**
     * Many-to-many: Category <-> JobOffer
     * Pivot table: category_job_offer
     */
    public function jobOffers(): BelongsToMany
    {
        return $this->belongsToMany(
            JobOffer::class,
            'category_job_offer', // pivot table
            'category_id',        // this model's FK
            'job_offer_id'        // related model's FK
        )->withTimestamps();
    }
}
