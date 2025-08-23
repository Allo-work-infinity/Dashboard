<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'payment_transactions';

    /* ---------- Status enums ---------- */
    public const STATUS_PENDING   = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
    ];

    /* ---------- Payment method enums ---------- */
    public const METHOD_CARD          = 'card';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_D17           = 'd17';

    public const METHODS = [
        self::METHOD_CARD,
        self::METHOD_BANK_TRANSFER,
        self::METHOD_D17,
    ];

    protected $fillable = [
        'user_id',
        'subscription_id',         // FK to user_subscriptions.id
        'konnect_payment_id',
        'konnect_transaction_id',
        'amount',
        'currency',                // default TND
        'payment_method',
        'status',
        'konnect_response',        // JSON
        'failure_reason',
        'processed_at',

        // NEW: manual payment helper fields
        'manual_reference',        // e.g. bank transfer reference, D17 ref
        'proof_path',              // storage path (public disk)
        'proof_original_name',
        'proof_mime',
        'proof_size',
        'proof_uploaded_at',

        // NEW: review metadata
        'reviewed_by',             // users.id
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'amount'           => 'decimal:3',
        'konnect_response' => 'array',
        'processed_at'     => 'datetime',

        // NEW
        'proof_uploaded_at'=> 'datetime',
        'reviewed_at'      => 'datetime',
    ];

    protected $attributes = [
        'currency' => 'TND',
        'status'   => self::STATUS_PENDING,
    ];

    protected $appends = ['is_final', 'is_successful', 'proof_url'];

    /* ---------- Relationships ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Usually references user_subscriptions.id */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    /** Reviewer (admin) */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /* ---------- Scopes ---------- */

    public function scopeForUser($q, int $userId)         { return $q->where('user_id', $userId); }
    public function scopeForSubscription($q, int $sid)    { return $q->where('subscription_id', $sid); }
    public function scopePending($q)                      { return $q->where('status', self::STATUS_PENDING); }
    public function scopeCompleted($q)                    { return $q->where('status', self::STATUS_COMPLETED); }
    public function scopeFailed($q)                       { return $q->where('status', self::STATUS_FAILED); }
    public function scopeCancelled($q)                    { return $q->where('status', self::STATUS_CANCELLED); }
    public function scopeByKonnectId($q, string $pid)     { return $q->where('konnect_payment_id', $pid); }

    /* ---------- Helpers / Computed ---------- */

    public function getIsFinalAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED], true);
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getProofUrlAttribute(): ?string
    {
        if (!$this->proof_path) return null;
        // requires: php artisan storage:link
        return Storage::disk('public')->url($this->proof_path);
    }

    /** Convenience updaters */
    public function markProcessed(?string $gatewayTxnId = null): void
    {
        $this->processed_at = now();
        if ($gatewayTxnId && empty($this->konnect_transaction_id)) {
            $this->konnect_transaction_id = $gatewayTxnId;
        }
        $this->save();
    }

    public function markCompleted(?array $response = null): void
    {
        $this->status = self::STATUS_COMPLETED;
        if ($response !== null) $this->konnect_response = $response;
        $this->markProcessed();
    }

    public function markFailed(string $reason, ?array $response = null): void
    {
        $this->status = self::STATUS_FAILED;
        $this->failure_reason = $reason;
        if ($response !== null) $this->konnect_response = $response;
        $this->markProcessed();
    }

    public function markCancelled(?array $response = null): void
    {
        $this->status = self::STATUS_CANCELLED;
        if ($response !== null) $this->konnect_response = $response;
        $this->markProcessed();
    }

    /**
     * Attach a proof file (image) and fill metadata.
     * Stores on "public" disk under "payment-proofs".
     */
    public function attachProof(UploadedFile $file, string $folder = 'payment-proofs'): void
    {
        $path = $file->store($folder, 'public');
        $this->proof_path          = $path;
        $this->proof_original_name = $file->getClientOriginalName();
        $this->proof_mime          = $file->getClientMimeType();
        $this->proof_size          = $file->getSize();
        $this->proof_uploaded_at   = now();
        $this->save();
    }
}
