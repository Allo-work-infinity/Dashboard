<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionPlanApiController extends Controller
{
    /**
     * GET /api/plans
     */
    public function index(Request $request)
    {
        $q           = $request->query('q');
        $onlyActive  = $request->has('active') ? $request->boolean('active') : true;
        $include     = collect(explode(',', (string) $request->query('include')))->filter()->values();
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
            ->when($onlyActive, fn ($qb) => $qb->where('is_active', true))
            ->when($include->contains('job_offers'), fn ($qb) => $qb->with(['jobOffers:id,title']))
            ->when($withCounts, fn ($qb) => $qb->withCount('jobOffers'))
            ->orderBy('name');

        if ($paginate) {
            $page = $plans->paginate($perPage)->appends($request->query());
            $page->getCollection()->transform(fn ($p) => $this->planPayload($p, $include, $withCounts));
            return response()->json($page);
        }

        $data = $plans->get()->map(fn ($p) => $this->planPayload($p, $include, $withCounts));
        return response()->json($data);
    }

    /**
     * GET /api/plans/{subscription_plan}
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

    /**
     * GET /api/subscription/current-plan
     *
     * Returns:
     * - current { plan, subscription }
     * - plans   [ { plan, latest_subscription, is_current } ]
     */
    public function myCurrentPlan(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $include    = collect(explode(',', (string) $request->query('include')))->filter()->values();
        $withCounts = $request->boolean('with_counts', true);

        // 1) Current subscription
        $current = UserSubscription::query()
            ->forUser($user->id)
            ->current()
            ->with('plan')
            ->latest('start_date')
            ->first();

        if ($current && $current->plan) {
            if ($include->contains('job_offers')) {
                $current->plan->load(['jobOffers:id,title']);
            }
            if ($withCounts) {
                $current->plan->loadCount('jobOffers');
            }
        }

        $currentPlanPayload = $current && $current->plan
            ? $this->planPayload($current->plan, $include, $withCounts)
            : null;

        $currentSubPayload = $current ? $this->subscriptionPayload($current) : null;

        // 2) Latest subscription per plan (history)
        $latestIds = UserSubscription::query()
            ->selectRaw('MAX(id) as id')
            ->where('user_id', $user->id)
            ->groupBy('plan_id')
            ->pluck('id');

        $latestSubsPerPlan = UserSubscription::query()
            ->whereIn('id', $latestIds)
            ->with('plan')
            ->get()
            ->filter(fn ($sub) => $sub->plan);

        // Optional eager loads for all plans
        if ($latestSubsPerPlan->isNotEmpty()) {
            $planIds = $latestSubsPerPlan->pluck('plan.id')->unique()->values();

            if ($include->contains('job_offers')) {
                SubscriptionPlan::whereIn('id', $planIds)->with(['jobOffers:id,title'])->get();
            }
            if ($withCounts) {
                SubscriptionPlan::whereIn('id', $planIds)->withCount('jobOffers')->get();
            }
        }

        $plansList = $latestSubsPerPlan->map(function (UserSubscription $sub) use ($include, $withCounts, $current) {
            return [
                'plan'                => $this->planPayload($sub->plan, $include, $withCounts),
                'latest_subscription' => $this->subscriptionPayload($sub),
                'is_current'          => $current ? ($current->id === $sub->id) : false,
            ];
        })->values();

        return response()->json([
            'current' => [
                'plan'         => $currentPlanPayload,
                'subscription' => $currentSubPayload,
            ],
            'plans' => $plansList,
        ]);
    }

    /* ------------ helpers ------------ */

    private function planPayload(SubscriptionPlan $p, $include, bool $withCounts): array
    {
        return [
            'id'              => $p->id,
            'name'            => $p->name,
            'description'     => (string) $p->description,
            'price'           => (float) $p->price,
            'duration_days'   => (int) $p->duration_days,
            'features'        => $p->features ?? [],
            'is_active'       => (bool) $p->is_active,
            'status'          => $p->is_active ? 'active' : 'inactive',
            'created_at'      => optional($p->created_at)->toIso8601String(),
            'updated_at'      => optional($p->updated_at)->toIso8601String(),
            'job_offers_count'=> $withCounts ? ($p->job_offers_count ?? 0) : null,
            'job_offers'      => $include->contains('job_offers')
                                  ? ($p->relationLoaded('jobOffers')
                                        ? $p->jobOffers->map(fn ($o) => ['id' => $o->id, 'title' => $o->title])->values()
                                        : [])
                                  : null,
        ];
    }

    private function subscriptionPayload(UserSubscription $s): array
    {
        return [
            'id'             => $s->id,
            'status'         => $s->status,
            'payment_status' => $s->payment_status,
            'payment_method' => $s->payment_method,
            'amount_paid'    => $s->amount_paid !== null ? (float) $s->amount_paid : null,
            'start_date'     => optional($s->start_date)->toIso8601String(),
            'end_date'       => optional($s->end_date)->toIso8601String(),
            'auto_renewal'   => (bool) $s->auto_renewal,
            'is_current'     => (bool) $s->is_current,
            'remaining_days' => $s->remaining_days,
            'plan_id'        => $s->plan_id,
        ];
    }
}
