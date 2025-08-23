<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentTransactionController extends Controller
{
    /** List + filters (Blade shell) */
    public function index(Request $request)
    {
        return view('payment_transactions.index', [
            'users'     => User::orderBy('first_name')->get(['id','first_name','last_name','email']),
            'subs'      => UserSubscription::orderByDesc('id')->get(['id']),
            'statuses'  => PaymentTransaction::STATUSES,
            // Optional: expose methods if you defined them on the model
            'methods'   => defined(PaymentTransaction::class.'::METHODS')
                ? PaymentTransaction::METHODS
                : ['card','bank_transfer','d17'],
        ]);
    }

    /** AJAX data for table (flat JSON) */
    public function data(Request $request)
    {
        $q      = $request->input('q'); // konnect ids / user email / name / manual ref
        $userId = $request->input('user_id');
        $subId  = $request->input('subscription_id');
        $status = $request->input('status');
        $method = $request->input('payment_method');
        $from   = $request->input('from'); // yyyy-mm-dd
        $to     = $request->input('to');   // yyyy-mm-dd

        $rows = PaymentTransaction::query()
            ->with(['user:id,first_name,last_name,email', 'subscription:id'])
            ->when($q, function ($qb) use ($q) {
                $qb->where('konnect_payment_id', 'like', "%{$q}%")
                   ->orWhere('konnect_transaction_id', 'like', "%{$q}%")
                   ->orWhere('manual_reference', 'like', "%{$q}%")
                   ->orWhereHas('user', fn($uq) =>
                        $uq->where('email', 'like', "%{$q}%")
                           ->orWhere('first_name', 'like', "%{$q}%")
                           ->orWhere('last_name',  'like', "%{$q}%"));
            })
            ->when($userId, fn($qb) => $qb->where('user_id', $userId))
            ->when($subId,  fn($qb) => $qb->where('subscription_id', $subId))
            ->when($status, fn($qb) => $qb->where('status', $status))
            ->when($method, fn($qb) => $qb->where('payment_method', $method))
            ->when($from,   fn($qb) => $qb->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($to,     fn($qb) => $qb->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->orderByDesc('created_at')
            ->get();

        $payload = $rows->map(function (PaymentTransaction $t) {
            $userName  = trim(($t->user->first_name ?? '').' '.($t->user->last_name ?? ''));
            return [
                'id'                     => $t->id,
                'user_name'              => $userName !== '' ? $userName : null,
                'user_email'             => $t->user->email ?? null,
                'subscription_id'        => $t->subscription_id,
                'konnect_payment_id'     => $t->konnect_payment_id,
                'konnect_transaction_id' => $t->konnect_transaction_id,
                'amount'                 => (float) $t->amount,
                'amount_formatted'       => number_format((float) $t->amount, 3, '.', ' '),
                'currency'               => $t->currency,
                'payment_method'         => $t->payment_method,
                'status'                 => $t->status,
                'manual_reference'       => $t->manual_reference,
                'review_note'            => $t->review_note,
                'admin_comment'          => $t->admin_comment,
                'proof_url'              => $t->proof_url, // accessor on model
                'proof_original_name'    => $t->proof_original_name,
                'proof_mime'             => $t->proof_mime,
                'proof_size'             => $t->proof_size,
                'created_at'             => optional($t->created_at)->toDateTimeString(),
                'processed_at'           => optional($t->processed_at)->toDateTimeString(),
                'reviewed_by'            => $t->reviewed_by,
                'reviewed_at'            => optional($t->reviewed_at)->toDateTimeString(),
            ];
        });

        return response()->json($payload);
    }

    public function create()
    {
        return view('payment_transactions.create', [
            'users'     => User::orderBy('first_name')->get(['id','first_name','last_name','email']),
            'subs'      => UserSubscription::orderByDesc('id')->get(['id','user_id']),
            'statuses'  => PaymentTransaction::STATUSES,
            'methods'   => defined(PaymentTransaction::class.'::METHODS')
                ? PaymentTransaction::METHODS
                : ['card','bank_transfer','d17'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['konnect_response'] = $this->normalizeJson($request->input('konnect_response'));
        $data['currency']         = strtoupper($data['currency'] ?? 'TND');

        // Auto set processed_at if status is final
        if ($this->isFinalStatus($data['status'] ?? null)) {
            $data['processed_at'] = $data['processed_at'] ?? now();
            // record reviewer meta for admin actions
            if (empty($data['reviewed_by'])) {
                $data['reviewed_by'] = optional(Auth::user())->id;
            }
            if (empty($data['reviewed_at'])) {
                $data['reviewed_at'] = now();
            }
        }

        $txn = PaymentTransaction::create($data);

        // Optional: admin can upload/replace proof from the UI
        if ($request->hasFile('proof')) {
            $this->saveProofFile($request->file('proof'), $txn);
        }

        return redirect()->route('payment-transactions.index')
            ->with('success', 'Payment transaction created.');
    }

    public function show(PaymentTransaction $payment_transaction)
    {
        $payment_transaction->load(['user','subscription']);
        return view('payment_transactions.show', ['txn' => $payment_transaction]);
    }

    public function edit(PaymentTransaction $payment_transaction)
    {
        return view('payment_transactions.edit', [
            'txn'      => $payment_transaction->load(['user','subscription']),
            'users'    => User::orderBy('first_name')->get(['id','first_name','last_name','email']),
            'subs'     => UserSubscription::orderByDesc('id')->get(['id','user_id']),
            'statuses' => PaymentTransaction::STATUSES,
            'methods'  => defined(PaymentTransaction::class.'::METHODS')
                ? PaymentTransaction::METHODS
                : ['card','bank_transfer','d17'],
        ]);
    }

    public function update(Request $request, PaymentTransaction $payment_transaction)
    {
        $data = $this->validated($request, updating: true);

        $data['konnect_response'] = $this->normalizeJson(
            $request->input('konnect_response', $payment_transaction->konnect_response)
        );
        $data['currency'] = strtoupper($data['currency'] ?? $payment_transaction->currency);

        // If moved to a final status and processed_at missing, set it now
        $newStatus = $data['status'] ?? $payment_transaction->status;
        if ($this->isFinalStatus($newStatus) &&
            empty($data['processed_at']) && empty($payment_transaction->processed_at)) {
            $data['processed_at'] = now();
        }

        // Reviewer meta when status is final
        if ($this->isFinalStatus($newStatus)) {
            $data['reviewed_by'] = $data['reviewed_by'] ?? optional(Auth::user())->id;
            $data['reviewed_at'] = $data['reviewed_at'] ?? now();
        }

        $payment_transaction->update($data);

        // Optional: replace proof
        if ($request->hasFile('proof')) {
            $this->saveProofFile($request->file('proof'), $payment_transaction, replaceExisting: true);
        }

        return redirect()->route('payment-transactions.index')
            ->with('success', 'Payment transaction updated.');
    }

    public function destroy(PaymentTransaction $payment_transaction)
    {
        // delete old proof file if exists
        if ($payment_transaction->proof_path) {
            Storage::disk('public')->delete($payment_transaction->proof_path);
        }
        $payment_transaction->delete();
        return redirect()->route('payment-transactions.index')
            ->with('success', 'Payment transaction deleted.');
    }

    /** Quick status setter (e.g., action button) */
    public function setStatus(Request $request, PaymentTransaction $payment_transaction)
    {
        $validated = $request->validate([
            'status'         => ['required', Rule::in(PaymentTransaction::STATUSES)],
            'failure_reason' => ['nullable','string','max:500'],
            'admin_comment'  => ['nullable','string','max:500'],
        ]);

        // Update the transaction first
        $attrs = [
            'status'        => $validated['status'],
            'admin_comment' => $validated['admin_comment'] ?? $payment_transaction->admin_comment,
        ];

        if (in_array($validated['status'], [
            PaymentTransaction::STATUS_COMPLETED,
            PaymentTransaction::STATUS_FAILED,
            PaymentTransaction::STATUS_CANCELLED,
        ], true)) {
            $attrs['processed_at'] = $payment_transaction->processed_at ?? now();
            $attrs['reviewed_by']  = $payment_transaction->reviewed_by ?? optional(Auth::user())->id;
            $attrs['reviewed_at']  = $payment_transaction->reviewed_at ?? now();
        }

        if (!empty($validated['failure_reason'])) {
            $attrs['failure_reason'] = $validated['failure_reason'];
        }

        $payment_transaction->update($attrs);

        // If the transaction is linked to a subscription, sync its payment_status
        if (!empty($payment_transaction->subscription_id)) {
            $sub = UserSubscription::find($payment_transaction->subscription_id);
            if ($sub) {
                // Map transaction status -> subscription payment_status
                $map = [
                    PaymentTransaction::STATUS_PENDING   => UserSubscription::PAY_PENDING,
                    PaymentTransaction::STATUS_COMPLETED => UserSubscription::PAY_COMPLETED,
                    PaymentTransaction::STATUS_FAILED    => UserSubscription::PAY_FAILED,
                    PaymentTransaction::STATUS_CANCELLED => UserSubscription::PAY_FAILED, // or PAY_REFUNDED if you prefer
                ];

                $newPayStatus = $map[$payment_transaction->status] ?? $sub->payment_status;

                // Only update if changed
                if ($newPayStatus !== $sub->payment_status) {
                    $sub->payment_status = $newPayStatus;
                    $sub->save();
                }
            }
        }

        return back()->with('success', 'Status updated.');
    }

    /* ---------------- helpers ---------------- */

    private function validated(Request $request, bool $updating = false): array
    {
        // make subscription + konnect ids optional (manual payments may not have them)
        $paymentMethodRule = defined(PaymentTransaction::class.'::METHODS')
            ? Rule::in(PaymentTransaction::METHODS)
            : Rule::in(['card','bank_transfer','d17']);

        return $request->validate([
            'user_id'                => ['required','exists:users,id'],
            'subscription_id'        => ['nullable','exists:user_subscriptions,id'],

            'konnect_payment_id'     => [
                'nullable','string','max:255',
                Rule::unique('payment_transactions','konnect_payment_id')->ignore($request->route('payment_transaction')),
            ],
            'konnect_transaction_id' => ['nullable','string','max:255'],

            'amount'                 => ['required','numeric','min:0'],
            'currency'               => ['nullable','string','size:3'],
            'payment_method'         => ['required','string','max:50', $paymentMethodRule],

            'status'                 => ['sometimes', Rule::in(PaymentTransaction::STATUSES)],
            'konnect_response'       => ['nullable'], // JSON string or array
            'failure_reason'         => ['nullable','string','max:500'],
            'processed_at'           => ['nullable','date'],

            // NEW manual / review fields
            'manual_reference'       => ['nullable','string','max:190'],
            'review_note'            => ['nullable','string','max:500'],
            'admin_comment'          => ['nullable','string','max:500'],
            'reviewed_by'            => ['nullable','exists:users,id'],
            'reviewed_at'            => ['nullable','date'],

            // Optional proof upload via admin web
            'proof'                  => ['nullable','image','max:5120'], // <= 5MB
        ]);
    }

    private function normalizeJson($value): ?array
    {
        if ($value === null || $value === '') return null;
        if (is_array($value)) return $value;

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    private function isFinalStatus(?string $status): bool
    {
        return in_array($status, [
            PaymentTransaction::STATUS_COMPLETED,
            PaymentTransaction::STATUS_FAILED,
            PaymentTransaction::STATUS_CANCELLED,
        ], true);
    }

    /**
     * Save or replace a proof image using move() under storage/app/public/payment-proofs.
     * Requires: php artisan storage:link
     */
    private function saveProofFile($uploadedFile, PaymentTransaction $txn, bool $replaceExisting = false): void
    {
        $disk   = 'public';
        $folder = 'payment-proofs';

        $dir = Storage::disk($disk)->path($folder);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($replaceExisting && $txn->proof_path) {
            Storage::disk($disk)->delete($txn->proof_path);
        }

        $origName   = $uploadedFile->getClientOriginalName();
        $ext        = $uploadedFile->getClientOriginalExtension() ?: 'bin';
        $nameOnly   = pathinfo($origName, PATHINFO_FILENAME);

        $filename = now()->format('YmdHis')
            . '-' . Str::limit(Str::slug($nameOnly), 60, '')
            . '-' . Str::lower(Str::random(6))
            . '.' . $ext;

        // Move to storage/app/public/payment-proofs/<filename>
        $uploadedFile->move($dir, $filename);

        $relativePath = trim($folder . '/' . $filename, '/');
        $absolutePath = $dir . DIRECTORY_SEPARATOR . $filename;

        $mime = $uploadedFile->getClientMimeType() ?: (@mime_content_type($absolutePath) ?: null);
        $size = $uploadedFile->getSize() ?: (@filesize($absolutePath) ?: null);

        $txn->proof_path          = $relativePath;
        $txn->proof_original_name = $origName;
        $txn->proof_mime          = $mime;
        $txn->proof_size          = $size;
        $txn->proof_uploaded_at   = now();
        $txn->save();
    }
}
