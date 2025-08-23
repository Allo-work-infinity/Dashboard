<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class PaymentTransactionApiController extends Controller
{
    /**
     * POST /api/payment-transactions
     * Create & save a payment transaction for the authenticated user.
     */
    public function store(Request $request)
    {
        $this->authorizeAuth();

        $validated = $request->validate([
            'subscription_id'        => ['required', 'integer'],
            'konnect_payment_id'     => ['required', 'string', 'max:255'],
            'konnect_transaction_id' => ['nullable', 'string', 'max:255'],
            'amount'                 => ['nullable', 'numeric'], // decimal(10,3)
            'currency'               => ['nullable', 'string', 'max:10'], // default TND
            'payment_method'         => ['nullable', 'string', 'max:50'],
            'status'                 => ['nullable', Rule::in(PaymentTransaction::STATUSES)],
            'konnect_response'       => ['nullable', 'array'],
            'failure_reason'         => ['nullable', 'string', 'max:255'],
            'processed_at'           => ['nullable', 'date'],
        ]);

        $user = Auth::user();

        // Format amount to 3 decimals if provided (matches cast 'decimal:3')
        $amount = array_key_exists('amount', $validated) && $validated['amount'] !== null
            ? number_format((float)$validated['amount'], 3, '.', '')
            : null;

        $tx = new PaymentTransaction([
            'user_id'               => $user->id,
            'subscription_id'       => $validated['subscription_id'],
            'konnect_payment_id'    => $validated['konnect_payment_id'],
            'konnect_transaction_id'=> $validated['konnect_transaction_id'] ?? null,
            'amount'                => $amount,
            'currency'              => $validated['currency'] ?? 'TND',
            'payment_method'        => $validated['payment_method'] ?? null,
            'status'                => $validated['status'] ?? PaymentTransaction::STATUS_PENDING,
            'konnect_response'      => $validated['konnect_response'] ?? null,
            'failure_reason'        => $validated['failure_reason'] ?? null,
            'processed_at'          => $validated['processed_at'] ?? null,
        ]);

        // If final status but no processed_at, stamp now.
        if (in_array($tx->status, [
                PaymentTransaction::STATUS_COMPLETED,
                PaymentTransaction::STATUS_FAILED,
                PaymentTransaction::STATUS_CANCELLED
            ], true) && empty($tx->processed_at)) {
            $tx->processed_at = now();
        }

        $tx->save();
        $tx->refresh();

        return response()->json(['data' => $tx], 201);
    }

    /**
     * PUT /api/payment-transactions/{id}
     * Update a transaction (status, gateway IDs, response, etc.)
     */
    public function update(Request $request, int $id)
    {
        $this->authorizeAuth();

        $tx = PaymentTransaction::findOrFail($id);
        $this->authorizeOwner($tx);

        $validated = $request->validate([
            'konnect_transaction_id' => ['nullable', 'string', 'max:255'],
            'amount'                 => ['nullable', 'numeric'],
            'currency'               => ['nullable', 'string', 'max:10'],
            'payment_method'         => ['nullable', 'string', 'max:50'],
            'status'                 => ['nullable', Rule::in(PaymentTransaction::STATUSES)],
            'konnect_response'       => ['nullable', 'array'],
            'failure_reason'         => ['nullable', 'string', 'max:255'],
            'processed_at'           => ['nullable', 'date'],
        ]);

        if (array_key_exists('amount', $validated)) {
            $tx->amount = $validated['amount'] === null
                ? null
                : number_format((float)$validated['amount'], 3, '.', '');
        }

        if (array_key_exists('currency', $validated))            $tx->currency = $validated['currency'] ?? $tx->currency;
        if (array_key_exists('payment_method', $validated))      $tx->payment_method = $validated['payment_method'];
        if (array_key_exists('konnect_transaction_id', $validated)) $tx->konnect_transaction_id = $validated['konnect_transaction_id'];
        if (array_key_exists('konnect_response', $validated))    $tx->konnect_response = $validated['konnect_response'];
        if (array_key_exists('failure_reason', $validated))      $tx->failure_reason = $validated['failure_reason'];

        if (array_key_exists('status', $validated) && $validated['status']) {
            $tx->status = $validated['status'];
        }

        // processed_at explicit or inferred if status becomes final
        if (array_key_exists('processed_at', $validated)) {
            $tx->processed_at = $validated['processed_at'];
        } elseif (in_array($tx->status, [
                PaymentTransaction::STATUS_COMPLETED,
                PaymentTransaction::STATUS_FAILED,
                PaymentTransaction::STATUS_CANCELLED
            ], true) && empty($tx->processed_at)) {
            $tx->processed_at = now();
        }

        $tx->save();
        $tx->refresh();

        return response()->json(['data' => $tx]);
    }
    public function apiStoreManual(Request $request)
    {
        $data = $request->validate([
            'amount'           => ['required','numeric','min:0.10'],
            'method'           => ['required', Rule::in([PaymentTransaction::METHOD_BANK_TRANSFER, PaymentTransaction::METHOD_D17])],
            'currency'         => ['nullable','string','size:3'],
            'subscription_id'  => ['nullable','integer','exists:user_subscriptions,id'],
            'manual_reference' => ['nullable','string','max:190'],
            'note'             => ['nullable','string','max:500'],
            'proof'            => ['required','image','max:5120'],
        ]);

        $user = $request->user();

        // Create pending manual transaction (Konnect fields can be nullable)
        $txn = PaymentTransaction::create([
            'user_id'                => $user->id,
            'subscription_id'        => $data['subscription_id'] ?? null,
            'amount'                 => $data['amount'],
            'currency'               => strtoupper($data['currency'] ?? 'TND'),
            'payment_method'         => $data['method'], // 'bank_transfer' | 'd17'
            'status'                 => PaymentTransaction::STATUS_PENDING,
            'konnect_payment_id'     => null,
            'konnect_transaction_id' => null,
            'konnect_response'       => null,
            'failure_reason'         => null,
            'processed_at'           => null,
            'manual_reference'       => $data['manual_reference'] ?? null,
            'review_note'            => $data['note'] ?? null,
        ]);

        // ---- Save proof into storage/app/public/payment-proofs ----
        // php artisan storage:link  (required once)
        $file   = $request->file('proof'); // UploadedFile
        $disk   = 'public';
        $folder = 'payment-proofs';

        $origName = $file->getClientOriginalName();
        $ext      = $file->getClientOriginalExtension() ?: 'bin';
        $nameOnly = pathinfo($origName, PATHINFO_FILENAME);

        $filename = now()->format('YmdHis')
            . '-' . Str::limit(Str::slug($nameOnly), 60, '')
            . '-' . Str::lower(Str::random(6))
            . '.' . $ext;

        // Store and get relative path like 'payment-proofs/20250823-...-abc123.png'
        $relativePath = $file->storeAs($folder, $filename, $disk);

        // Resolve absolute path
        $absolutePath = Storage::disk($disk)->path($relativePath);

        // Read metadata AFTER storing (avoids temp-file issues on Windows)
        $size = null;
        try {
            $size = Storage::disk($disk)->size($relativePath);
        } catch (\Throwable $e) {
            $size = @filesize($absolutePath) ?: null;
        }

        $mime = null;
        try {
            $mime = Storage::disk($disk)->mimeType($relativePath);
        } catch (\Throwable $e) {
            if (function_exists('finfo_open')) {
                $f = finfo_open(FILEINFO_MIME_TYPE);
                $mime = $f ? finfo_file($f, $absolutePath) : null;
                if ($f) finfo_close($f);
            }
            if (!$mime) {
                $mime = $file->getClientMimeType(); // fallback
            }
        }

        // Persist proof fields on the transaction
        $txn->proof_path          = $relativePath;         // relative to 'public' disk
        $txn->proof_original_name = $origName;
        $txn->proof_mime          = $mime;
        $txn->proof_size          = $size;
        $txn->proof_uploaded_at   = now();
        $txn->save();

        return response()->json([
            'status'          => 'ok',
            'transaction_id'  => $txn->id,
            'amount'          => (float) $txn->amount,
            'currency'        => $txn->currency,
            'payment_method'  => $txn->payment_method,
            'status_label'    => $txn->status,
            'proof_url'       => Storage::disk($disk)->url($relativePath), // /storage/payment-proofs/...
            'created_at'      => optional($txn->created_at)->toIso8601String(),
        ], 201);
    }

    /**
     * GET /api/payment-transactions/{id}
     * Fetch one transaction; only owner or admin can see it.
     */
    public function show(int $id)
    {
        $this->authorizeAuth();

        $tx = PaymentTransaction::findOrFail($id);
        $this->authorizeOwner($tx);

        return response()->json(['data' => $tx]);
    }

    /**
     * Optional: GET /api/payment-transactions (list current user's transactions)
     */
    public function index(Request $request)
    {
        $this->authorizeAuth();

        $user = Auth::user();

        $query = PaymentTransaction::query()->where('user_id', $user->id);

        // Optional simple filters
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($subscriptionId = $request->query('subscription_id')) {
            $query->where('subscription_id', $subscriptionId);
        }

        $transactions = $query->latest()->paginate(15);

        return response()->json($transactions);
    }

    // ----------------- Helpers -----------------

    protected function authorizeAuth(): void
    {
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }
    }

    protected function authorizeOwner(PaymentTransaction $tx): void
    {
        $user = Auth::user();
        $isOwner = $user && ($user->id === $tx->user_id);
        $isAdmin = $user && method_exists($user, 'isAdmin') ? $user->isAdmin() : false;

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Forbidden');
        }
    }
}
