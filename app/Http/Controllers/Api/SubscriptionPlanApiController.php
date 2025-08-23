<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlanApiController extends Controller
{
    /**
     * GET /api/plans
     *
     * Query params:
     * - q=...                 (search name/description)
     * - active=1|0            (default: 1 -> only active)
     * - include=job_offers    (include related job offers: id, title)
     * - with_counts=1|0       (default: 1 -> include job_offers_count)
     * - paginate=1|0          (default: 0 -> return ALL)
     * - per_page=15           (only used if paginate=1)
     */
    public function index(Request $request)
    {
        $q           = $request->query('q');
        $onlyActive  = $request->has('active')
                        ? $request->boolean('active')
                        : true; // default: only active
        $include     = collect(explode(',', (string) $request->query('include')))
                        ->filter()->values();
        $withCounts  = $request->boolean('with_counts', true);
        $paginate    = $request->boolean('paginate', false);
        $perPage     = max(1, (int) $request->query('per_page', 15));

        $plans = SubscriptionPlan::query()
            ->when($q, function ($qb) use ($q) {
                $qb->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($onlyActive, fn($qb) => $qb->where('is_active', true))
            ->when($include->contains('job_offers'), fn($qb) => $qb->with(['jobOffers:id,title']))
            ->when($withCounts, fn($qb) => $qb->withCount('jobOffers'))
            ->orderBy('name');

        if ($paginate) {
            $page = $plans->paginate($perPage)->appends($request->query());
            $page->getCollection()->transform(fn($p) => $this->planPayload($p, $include, $withCounts));
            return response()->json($page);
        }

        $data = $plans->get()->map(fn($p) => $this->planPayload($p, $include, $withCounts));
        return response()->json($data);
    }

    /**
     * GET /api/plans/{subscription_plan}
     * Optional: ?include=job_offers&with_counts=1
     */
    public function show(SubscriptionPlan $subscription_plan, Request $request)
    {
        $include    = collect(explode(',', (string) $request->query('include')))->filter()->values();
        $withCounts = $request->boolean('with_counts', true);

        if ($include->contains('job_offers')) {
            $subscription_plan->load(['jobOffers:id,title']);
        }
        if ($withCounts) {
            $subscription_plan->loadCount('jobOffers');
        }

        return response()->json($this->planPayload($subscription_plan, $include, $withCounts));
    }

       public function myCurrentPlan(Request $request)
    {
        $user = Auth::user(); // or $request->user()
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $include    = collect(explode(',', (string) $request->query('include')))->filter()->values();
        $withCounts = $request->boolean('with_counts', true);

        $subscription = UserSubscription::query()
            ->forUser($user->id)
            ->current()
            ->with('plan')
            ->latest('start_date')
            ->first();

        // If no current subscription: return nulls (200 so UI can handle gracefully)
        if (! $subscription || ! $subscription->plan) {
            return response()->json([
                'plan'         => null,
                'subscription' => null,
            ]);
        }

        // Optionally load plan extras (job_offers, counts)
        if ($include->contains('job_offers')) {
            $subscription->plan->load(['jobOffers:id,title']);
        }
        if ($withCounts) {
            $subscription->plan->loadCount('jobOffers');
        }

        $planPayload = $this->planPayload($subscription->plan, $include, $withCounts);

        $subscriptionPayload = [
            'id'              => $subscription->id,
            'status'          => $subscription->status,
            'payment_status'  => $subscription->payment_status,
            'payment_method'  => $subscription->payment_method,
            'amount_paid'     => $subscription->amount_paid !== null ? (float) $subscription->amount_paid : null,
            'start_date'      => optional($subscription->start_date)->toIso8601String(),
            'end_date'        => optional($subscription->end_date)->toIso8601String(),
            'auto_renewal'    => (bool) $subscription->auto_renewal,
            'is_current'      => (bool) $subscription->is_current,
            'remaining_days'  => $subscription->remaining_days,
        ];

        return response()->json([
            'plan'         => $planPayload,
            'subscription' => $subscriptionPayload,
        ]);
    }
    /* ------------ helpers ------------ */

    private function planPayload(SubscriptionPlan $p, $include, bool $withCounts): array
    {
        return [
            'id'            => $p->id,
            'name'          => $p->name,
            'description'   => (string) $p->description,
            'price'         => (float) $p->price,          // decimal cast -> string; expose as float
            'duration_days' => (int) $p->duration_days,
            'features'      => $p->features ?? [],         // array (cast)
            'is_active'     => (bool) $p->is_active,
            'status'        => $p->is_active ? 'active' : 'inactive',
            'created_at'    => optional($p->created_at)->toIso8601String(),
            'updated_at'    => optional($p->updated_at)->toIso8601String(),

            // conditionals
            'job_offers_count' => $withCounts ? ($p->job_offers_count ?? 0) : null,
            'job_offers'       => $include->contains('job_offers')
                                  ? ($p->relationLoaded('jobOffers')
                                        ? $p->jobOffers->map(fn($o) => [
                                            'id'    => $o->id,
                                            'title' => $o->title,
                                          ])->values()
                                        : [])
                                  : null,
        ];
    }
}
