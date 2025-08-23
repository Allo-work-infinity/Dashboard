<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentTransaction;


class UserSubscriptionApiController extends Controller
{
    /**
     * GET /api/subscriptions
     */
    public function index(Request $request)
    {
        $auth = $this->authOrAbort();

        $status        = $request->input('status');
        $paymentStatus = $request->input('payment_status');
        $planId        = $request->input('plan_id');
        $current       = $request->boolean('current');
        $from          = $request->input('from');
        $to            = $request->input('to');
        $q             = $request->input('q');

        $subs = UserSubscription::query()
            ->with(['plan:id,name,price,duration_days'])
            ->where('user_id', $auth->id)
            ->when($q, fn($qb) => $qb->whereHas('plan', fn($pq) =>
                $pq->where('name', 'like', "%{$q}%")
            ))
            ->when($status, fn($qb) => $qb->where('status', $status))
            ->when($paymentStatus, fn($qb) => $qb->where('payment_status', $paymentStatus))
            ->when($planId, fn($qb) => $qb->where('plan_id', $planId))
            ->when($current, fn($qb) => $qb->current())
            ->when($from, fn($qb) => $qb->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($to,   fn($qb) => $qb->where('created_at', '<=', Carbon::parse($to)->endOfDay()))
            ->orderByDesc('created_at')
            ->get();

        return response()->json(
            $subs->map(function (UserSubscription $s) use ($auth) {
                return [
                    'id'              => $s->id,
                    'user_id'         => $auth->id,
                    'user_name'       => $auth->name,
                    'user_email'      => $auth->email,
                    'plan_id'         => $s->plan_id,
                    'plan_name'       => optional($s->plan)->name,
                    'plan_price'      => optional($s->plan)?->price,
                    'status'          => $s->status,
                    'payment_status'  => $s->payment_status,
                    'payment_id'      => $s->payment_id,
                    'transaction_id'  => $s->transaction_id,
                    'payment_method'  => $s->payment_method,
                    'amount_paid'     => $s->amount_paid,
                    'start_date'      => optional($s->start_date)?->toIso8601String(),
                    'end_date'        => optional($s->end_date)?->toIso8601String(),
                    'auto_renewal'    => (bool) $s->auto_renewal,
                    'is_current'      => $s->is_current,
                    'remaining_days'  => $s->remaining_days,
                    'created_at'      => optional($s->created_at)?->toIso8601String(),
                    'updated_at'      => optional($s->updated_at)?->toIso8601String(),
                ];
            })
        );
    }

    /**
     * GET /api/subscriptions/{subscription}
     */
    public function show(UserSubscription $user_subscription)
    {
        $auth = $this->authOrAbort();
        $this->authorizeOwner($auth->id, $user_subscription->user_id);

        $user_subscription->load('plan:id,name,price,duration_days');

        return response()->json([
            'id'              => $user_subscription->id,
            'user_id'         => $auth->id,
            'user_name'       => $auth->name,
            'user_email'      => $auth->email,
            'plan_id'         => $user_subscription->plan_id,
            'plan_name'       => optional($user_subscription->plan)->name,
            'status'          => $user_subscription->status,
            'payment_status'  => $user_subscription->payment_status,
            'amount_paid'     => $user_subscription->amount_paid,
            'start_date'      => optional($user_subscription->start_date)?->toIso8601String(),
            'end_date'        => optional($user_subscription->end_date)?->toIso8601String(),
            'auto_renewal'    => (bool) $user_subscription->auto_renewal,
            'is_current'      => $user_subscription->is_current,
            'remaining_days'  => $user_subscription->remaining_days,
        ]);
    }

    /**
     * GET /api/me/subscription/current
     */
    public function current()
    {
        $auth = $this->authOrAbort();

        $sub = UserSubscription::query()
            ->with('plan:id,name,price,duration_days')
            ->where('user_id', $auth->id)
            ->current()
            ->latest('start_date')
            ->first();

        if (!$sub) {
            return response()->json(['message' => 'No current subscription.'], 404);
        }

        return response()->json([
            'id'             => $sub->id,
            'user_id'        => $auth->id,
            'plan_id'        => $sub->plan_id,
            'plan_name'      => optional($sub->plan)->name,
            'status'         => $sub->status,
            'amount_paid'    => $sub->amount_paid,
            'start_date'     => optional($sub->start_date)?->toIso8601String(),
            'end_date'       => optional($sub->end_date)?->toIso8601String(),
            'auto_renewal'   => (bool) $sub->auto_renewal,
            'remaining_days' => $sub->remaining_days,
        ]);
    }

    /**
     * POST /api/subscriptions
     */
    public function store(Request $request)
    {
        try {
            $auth = $this->authOrAbort();

            $data = $request->validate([
                'plan_id'        => ['required','exists:subscription_plans,id'],
                'payment_id'     => ['nullable','string','max:255'],
                'transaction_id' => ['nullable','string','max:255'],
                'payment_method' => ['nullable','string','max:50'],
                'amount_paid'    => ['nullable','numeric','min:0','max:9999999.999'],
                'auto_renewal'   => ['sometimes','boolean'],
            ]);

            $data['user_id']        = $auth->id; // <- from Auth
            $data['status']         = UserSubscription::STATUS_ACTIVE; // default status
            $data['payment_status'] = UserSubscription::PAY_COMPLETED;
            
            // Set start_date to today and calculate end_date based on plan duration
            $data['start_date'] = now();
            $data = $this->applyPlanDerivedFields($data);

            $sub = UserSubscription::create($data);

            return response()->json([
                'message' => 'Subscription created.',
                'id'      => $sub->id,
            ], 201);

        } catch (\Throwable $e) {
            // Log the full exception with stack trace
            \Log::error('Subscription store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'user_id' => optional(auth()->user())->id,
            ]);

            // Return a safe JSON response (don't expose trace to client)
            return response()->json([
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(), // optional: remove in production
            ], 500);
        }
    }

    //


   public function apiCreateSubscriptionFromManual(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'plan_id'      => ['required','exists:subscription_plans,id'],
            'auto_renewal' => ['sometimes','boolean'],
        ]);

        // Get plan to derive price & duration
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // Dates derived like your `store()` method (start = now, end = start + duration_days)
        $start = now();
        $end   = $plan->duration_days
            ? $start->copy()->addDays((int) $plan->duration_days)
            : null;

        // Create a PENDING subscription awaiting manual verification
        $sub = UserSubscription::create([
            'user_id'        => $user->id,
            'plan_id'        => $plan->id,
            'status'         => UserSubscription::STATUS_PENDING,   // not active yet
            'payment_status' => UserSubscription::PAY_PENDING,      // waiting for verification
            'auto_renewal'   => (bool)($validated['auto_renewal'] ?? false),

            // Derived fields
            'start_date'     => $start,
            'end_date'       => $end,
            'amount_paid'    => $plan->price,                       // decimal(10,3)

            // No payment details here for manual flow
            'payment_method' => null,
            'payment_id'     => null,
            'transaction_id' => null,
        ]);

        return response()->json([
            'status'              => 'ok',
            'subscription_id'     => $sub->id,
            'subscription_status' => $sub->status,          // pending
            'payment_status'      => $sub->payment_status,  // pending
            'start_date'          => optional($sub->start_date)->toIso8601String(),
            'end_date'            => optional($sub->end_date)->toIso8601String(),
            'amount_paid'         => (float) $sub->amount_paid,
            'plan'                => [
                'id'            => $plan->id,
                'name'          => $plan->name,
                'price'         => (float) $plan->price,
                'duration_days' => (int) $plan->duration_days,
            ],
        ], 201);
    }
    /**
     * PATCH /api/subscriptions/{subscription}
     */
    public function update(Request $request, UserSubscription $user_subscription)
    {
        $auth = $this->authOrAbort();
        $this->authorizeOwner($auth->id, $user_subscription->user_id);

        $data = $request->validate([
            'auto_renewal' => ['sometimes','boolean'],
            'status'       => ['sometimes', Rule::in([UserSubscription::STATUS_CANCELLED])],
        ]);

        $attrs = [];
        if ($request->has('auto_renewal')) {
            $attrs['auto_renewal'] = (bool) $request->boolean('auto_renewal');
        }
        if (($data['status'] ?? null) === UserSubscription::STATUS_CANCELLED) {
            $attrs['status'] = UserSubscription::STATUS_CANCELLED;
        }

        if ($attrs) {
            $user_subscription->update($attrs);
        }

        return response()->json(['message' => 'Subscription updated.']);
    }

    /* ================= helpers ================= */

    private function applyPlanDerivedFields(array $data, ?UserSubscription $existing = null): array
    {
        $plan = isset($data['plan_id'])
            ? SubscriptionPlan::find($data['plan_id'])
            : ($existing ? $existing->plan : null);

        if ((!isset($data['amount_paid']) || $data['amount_paid'] === null || $data['amount_paid'] === '') && $plan) {
            $data['amount_paid'] = $plan->price;
        }

        $data['auto_renewal'] = array_key_exists('auto_renewal', $data)
            ? (bool) $data['auto_renewal']
            : ($existing->auto_renewal ?? false);

        return $data; // dates/statuses are set by payment flow later
    }

    private function authorizeOwner(int $authId, int $ownerId): void
    {
        abort_unless($authId === $ownerId, 403, 'Forbidden');
    }

    private function authOrAbort()
    {
        $user = Auth::user();
        abort_unless($user, 401, 'Unauthenticated.');
        return $user;
    }
}
