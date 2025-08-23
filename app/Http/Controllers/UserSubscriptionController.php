<?php

namespace App\Http\Controllers;

use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserSubscriptionController extends Controller
{
    /** List + filters */
   public function index(Request $request)
    {
        // If the front-end asks for JSON (our AJAX table), return a flat array

        // First render: load the Blade shell; front-end fetches data via AJAX.
        return view('user_subscriptions.index');
    }
    //  
    public function data(Request $request)
    {
        // If the front-end asks for JSON (our AJAX table), return a flat array
       
            $q             = $request->input('q');
            $status        = $request->input('status');
            $paymentStatus = $request->input('payment_status');
            $userId        = $request->input('user_id');
            $planId        = $request->input('plan_id');

            $subs = UserSubscription::query()
                ->with(['user:id,first_name,last_name,email', 'plan:id,name'])
                ->when($q, function ($qb) use ($q) {
                    $qb->whereHas('user', function ($uq) use ($q) {
                            $uq->where('email', 'like', "%{$q}%")
                            ->orWhere('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%");
                        })
                    ->orWhereHas('plan', function ($pq) use ($q) {
                            $pq->where('name', 'like', "%{$q}%");
                        });
                })
                ->when($status,        fn($qb) => $qb->where('status', $status))
                ->when($paymentStatus, fn($qb) => $qb->where('payment_status', $paymentStatus))
                ->when($userId,        fn($qb) => $qb->where('user_id', $userId))
                ->when($planId,        fn($qb) => $qb->where('plan_id', $planId))
                ->orderByDesc('created_at')
                ->get();

            $payload = $subs->map(function (UserSubscription $s) {
                $userName = trim(($s->user->first_name ?? '') . ' ' . ($s->user->last_name ?? ''));
                return [
                    'id'              => $s->id,
                    'user_name'       => $userName !== '' ? $userName : null,
                    'user_email'      => $s->user->email ?? null,
                    'plan_name'       => $s->plan->name ?? null,
                    'status'          => $s->status,
                    'payment_status'  => $s->payment_status,
                    'amount_paid'     => isset($s->amount_paid) ? number_format((float)$s->amount_paid, 3, '.', '') : null,
                    'start_date'      => optional($s->start_date)->toDateString(),
                    'end_date'        => optional($s->end_date)->toDateString(),
                    'auto_renewal'    => (bool) $s->auto_renewal,
                    'created_at'      => optional($s->created_at)->toDateString(),
                ];
            });

            return response()->json($payload);
       
    }
    public function create()
    {
        $users = User::orderBy('first_name')->get(['id','first_name','last_name','email']);
        $plans = SubscriptionPlan::orderBy('name')->get();
        return view('user_subscriptions.create', compact('users','plans'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // Fill defaults from plan (price, end_date)
        $data = $this->applyPlanDerivedFields($data);

        $sub = UserSubscription::create($data);

        return redirect()
            ->route('user-subscriptions.index')
            ->with('success', 'Subscription created successfully.');
    }

    public function show(UserSubscription $user_subscription)
    {
        $user_subscription->load(['user','plan']);
        return view('user_subscriptions.show', ['sub' => $user_subscription]);
    }

    public function edit(UserSubscription $user_subscription)
    {
        $users = User::orderBy('first_name')->get(['id','first_name','last_name','email']);
        $plans = SubscriptionPlan::orderBy('name')->get();
        return view('user_subscriptions.edit', [
            'sub'   => $user_subscription->load(['user','plan']),
            'users' => $users,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request, UserSubscription $user_subscription)
    {
        $data = $this->validateData($request, updating: true);

        $data = $this->applyPlanDerivedFields($data, $user_subscription);

        $user_subscription->update($data);

        return redirect()
            ->route('user-subscriptions.index')
            ->with('success', 'Subscription updated.');
    }

    public function destroy(UserSubscription $user_subscription)
    {
        $user_subscription->delete();

        return redirect()
            ->route('user-subscriptions.index')
            ->with('success', 'Subscription deleted.');
    }

    /* -------------------- helpers -------------------- */

    private function validateData(Request $request, bool $updating = false): array
    {
        // Use model constants if you added them (as shown earlier)
        return $request->validate([
            'user_id'        => ['required','exists:users,id'],
            'plan_id'        => ['required','exists:subscription_plans,id'],

            'status'         => ['required', Rule::in(\App\Models\UserSubscription::STATUSES)],
            'payment_status' => ['required', Rule::in(\App\Models\UserSubscription::PAYMENT_STATUSES)],

            'payment_id'     => ['nullable','string','max:255'],
            'transaction_id' => ['nullable','string','max:255'],
            'payment_method' => ['nullable','string','max:50'],

            'amount_paid'    => ['nullable','numeric','min:0','max:9999999.999'], // nullable -> will fallback to plan price
            'start_date'     => ['nullable','date'],
            'end_date'       => ['nullable','date','after_or_equal:start_date'],
            'auto_renewal'   => ['sometimes','boolean'],
        ], [
            // Custom messages if you want
        ]);
    }

    /**
     * - If amount_paid is empty, use plan price.
     * - If start_date is set and end_date is empty, compute from plan duration_days.
     * - If status is 'active' and start_date missing, set start_date = now() and end_date if plan exists.
     */
    private function applyPlanDerivedFields(array $data, ?UserSubscription $existing = null): array
    {
        $plan = isset($data['plan_id'])
            ? SubscriptionPlan::find($data['plan_id'])
            : ($existing ? $existing->plan : null);

        // amount_paid fallback
        if ((!isset($data['amount_paid']) || $data['amount_paid'] === null || $data['amount_paid'] === '') && $plan) {
            $data['amount_paid'] = $plan->price;
        }

        // normalize boolean
        $data['auto_renewal'] = isset($data['auto_renewal']) ? (bool)$data['auto_renewal'] : ($existing->auto_renewal ?? false);

        // dates
        $status = $data['status'] ?? $existing?->status;

        if (($status === UserSubscription::STATUS_ACTIVE) && empty($data['start_date'])) {
            $data['start_date'] = Carbon::now();
        }

        if (!empty($data['start_date']) && empty($data['end_date']) && $plan) {
            $data['end_date'] = Carbon::parse($data['start_date'])->addDays((int)$plan->duration_days);
        }

        return $data;
    }
}
