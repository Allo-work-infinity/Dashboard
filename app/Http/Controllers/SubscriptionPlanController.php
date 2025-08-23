<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class SubscriptionPlanController extends Controller
{
    /** List with simple filters */
    public function index(Request $request)
    {
        // Return JSON for AJAX table
   
        // First render loads the shell; JS will fetch data via AJAX
        return view('subscription_plans.index');
    }
    // Show the form for creating a new subscription plan.
    public function data(Request $request)
    {

            $q = $request->input('q');

            $plans = SubscriptionPlan::query()
                ->when($q, fn($qb) => $qb->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
                }))
                ->when($request->filled('is_active'), fn($qb) => $qb->where('is_active', $request->boolean('is_active')))
                ->orderByDesc('created_at')
                ->get(['id','name','description','price','duration_days','is_active','created_at']);

            $payload = $plans->map(function ($p) {
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'description'   => Str::limit((string)$p->description, 120),
                    'price'         => (float) $p->price,
                    'duration_days' => (int) $p->duration_days,
                    'status'        => $p->is_active ? 'active' : 'inactive',
                    'created_at'    => optional($p->created_at)->toDateString(),
                ];
            });

            return response()->json($payload);
       
    }
    public function create()
    {
        return view('subscription_plans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['features'] = $this->normalizeFeatures($request->input('features'));

        $plan = SubscriptionPlan::create($data);

        return redirect()
            ->route('subscription-plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    public function show(SubscriptionPlan $subscription_plan)
    {
        return view('subscription_plans.show', ['plan' => $subscription_plan]);
    }

    public function edit(SubscriptionPlan $subscription_plan)
    {
        return view('subscription_plans.edit', ['plan' => $subscription_plan]);
    }

    public function update(Request $request, SubscriptionPlan $subscription_plan)
    {
        $data = $this->validateData($request, updating: true);
        $data['features'] = $this->normalizeFeatures($request->input('features', $subscription_plan->features));

        $subscription_plan->update($data);

        return redirect()
            ->route('subscription-plans.index')
            ->with('success', 'Subscription plan updated.');
    }

    public function destroy(SubscriptionPlan $subscription_plan)
    {
        $subscription_plan->delete();

        return redirect()
            ->route('subscription-plans.index')
            ->with('success', 'Subscription plan deleted.');
    }

    /** Toggle active from list */
    public function toggleActive(SubscriptionPlan $subscription_plan)
    {
        $subscription_plan->update(['is_active' => ! (bool) $subscription_plan->is_active]);

        return back()->with('success', 'Plan status updated.');
    }

    /* ---------------- helpers ---------------- */

    private function validateData(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'name'          => ['required','string','max:100'],
            'description'   => ['nullable','string'],
            'price'         => ['required','numeric','min:0','max:9999999.999'],
            'duration_days' => ['required','integer','min:1','max:100000'],
            'features'      => ['nullable'],          // JSON string or comma/newline list
            'is_active'     => ['sometimes','boolean'],
        ]);
    }

    private function normalizeFeatures($features)
    {
        if (is_null($features) || $features === '') {
            return null;
        }
        if (is_array($features)) {
            return $features;
        }
        if (is_string($features)) {
            // Try JSON first
            $decoded = json_decode($features, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            // Fallback: split by commas/newlines into array
            $items = preg_split('/[\r\n,]+/', $features);
            $items = array_values(array_filter(array_map('trim', $items), fn($v) => $v !== ''));
            return $items ?: null;
        }
        return null;
    }
}
