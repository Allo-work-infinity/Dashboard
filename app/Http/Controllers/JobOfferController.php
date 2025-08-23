<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use App\Models\Company;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Category;
use Illuminate\Support\Facades\DB;



class JobOfferController extends Controller
{
    /** List + filters (supports AJAX JSON for your table) */
    public function index(Request $request)
    {
        // If the front-end asks for JSON (AJAX), return a flat array
  

        // First render (Blade): send lists for filters/selects
        return view('job_offers.index', [
            'companies' => Company::orderBy('name')->get(['id','name']),
            'types'     => JobOffer::TYPES,
            'levels'    => JobOffer::LEVELS,
            'statuses'  => JobOffer::STATUSES,
            'plans'     => SubscriptionPlan::orderBy('name')->get(['id','name']), // for filter dropdown, if needed
        ]);
    }
    // Show the form for creating a new job offer.  
     public function data(Request $request)
    {
        // If the front-end asks for JSON (AJAX), return a flat array
       
            $q         = $request->input('q');
            $company   = $request->input('company_id');
            $status    = $request->input('status');
            $type      = $request->input('job_type');
            $level     = $request->input('experience_level');
            $city      = $request->input('city');
            $gov       = $request->input('governorate');
            $remote    = $request->input('remote_allowed');       // 0/1
            $featured  = $request->input('is_featured');          // 0/1
            $minPay    = $request->input('min_salary');
            $maxPay    = $request->input('max_salary');
            $before    = $request->input('deadline_before');
            $after     = $request->input('deadline_after');
            $planId    = $request->input('subscription_plan_id'); // filter by one plan

            $offers = JobOffer::query()
                ->with([
                    'company:id,name',
                    'subscriptionPlans:id,name', // eager-load plans
                ])
                ->when($q, fn($qb) => $qb->search($q))
                ->when($company, fn($qb) => $qb->where('company_id', $company))
                ->when($status, fn($qb) => $qb->where('status', $status))
                ->when($type, fn($qb) => $qb->where('job_type', $type))
                ->when($level, fn($qb) => $qb->where('experience_level', $level))
                ->when($request->filled('remote_allowed'), fn($qb) => $qb->where('remote_allowed', $request->boolean('remote_allowed')))
                ->when($request->filled('is_featured'), fn($qb) => $qb->where('is_featured', $request->boolean('is_featured')))
                ->when($city, fn($qb) => $qb->where('city', 'like', "%{$city}%"))
                ->when($gov, fn($qb)  => $qb->where('governorate', 'like', "%{$gov}%"))
                ->when($minPay, fn($qb) => $qb->where(function ($q) use ($minPay) {
                    $q->whereNull('salary_min')->orWhere('salary_min', '>=', $minPay);
                }))
                ->when($maxPay, fn($qb) => $qb->where(function ($q) use ($maxPay) {
                    $q->whereNull('salary_max')->orWhere('salary_max', '<=', $maxPay);
                }))
                ->when($before, fn($qb) => $qb->whereDate('application_deadline', '<=', Carbon::parse($before)->toDateString()))
                ->when($after, fn($qb)  => $qb->whereDate('application_deadline', '>=', Carbon::parse($after)->toDateString()))
                ->when($planId, fn($qb) => $qb->whereHas('subscriptionPlans', function ($qq) use ($planId) {
                    $qq->where('subscription_plans.id', $planId);
                }))
                ->orderByDesc('created_at')
                ->get();

            $payload = $offers->map(function (JobOffer $o) {
                return [
                    'id'                   => $o->id,
                    'reference'            => $o->reference, // ← NEW surfaced in payload
                    'title'                => $o->title,
                    'company'              => optional($o->company)->name,
                    'job_type'             => $o->job_type,
                    'experience_level'     => $o->experience_level,
                    'city'                 => $o->city,
                    'governorate'          => $o->governorate,
                    'location'             => $o->location,
                    'salary_min'           => $o->salary_min,
                    'salary_max'           => $o->salary_max,
                    'currency'             => $o->currency,
                    'status'               => $o->status, // draft|active|paused|closed
                    'is_featured'          => (bool) $o->is_featured,
                    'application_deadline' => optional($o->application_deadline)->toDateString(),
                    'created_at'           => optional($o->created_at)->toDateString(),
                    // 🔗 many-to-many summary
                    'subscription_plan_ids'   => $o->subscriptionPlans->pluck('id')->all(),
                    'subscription_plan_names' => $o->subscriptionPlans->pluck('name')->all(),
                ];
            });

            return response()->json($payload);

        // First render (Blade): send lists for filters/selects
       
    }
    public function create()
    {
        return view('job_offers.create', [
            'companies'  => Company::orderBy('name')->get(['id','name']),
            'types'      => JobOffer::TYPES,
            'levels'     => JobOffer::LEVELS,
            'statuses'   => JobOffer::STATUSES,
            'plans'      => SubscriptionPlan::active()->orderBy('name')->get(['id','name']),
            'categories' => Category::orderBy('name')->get(['id','name']), // ✅ add categories
        ]);
    }

    

    public function store(Request $request)
    {
        // Your existing request-wide validation (title, salary, etc.)
        $data = $this->validated($request);

        // ✅ Categories: allow single `category_id` or multiple `category_ids[]`
        $request->validate([
            'category_id'    => ['required_without:category_ids', 'nullable', 'integer', 'exists:categories,id'],
            'category_ids'   => ['required_without:category_id', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],

            // (optional) validate plan IDs if you send them
            'subscription_plan_ids'   => ['sometimes', 'array'],
            'subscription_plan_ids.*' => ['integer', 'exists:subscription_plans,id'],
        ]);

        // Build the final category ID list (de-duplicated)
        $categoryIds = collect((array) $request->input('category_ids', []))
            ->when($request->filled('category_id'), function ($c) use ($request) {
                return $c->push((int) $request->input('category_id'));
            })
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Fallback safety (should be guaranteed by validation above)
        if (empty($categoryIds)) {
            return back()->withErrors(['category_id' => 'Veuillez sélectionner au moins une catégorie.']);
        }

        // Keep legacy column for backward compatibility
        $data['category_id'] = $categoryIds[0];

        // Normalize arrays & flags (your helpers)
        $data['skills_required'] = $this->normalizeArray($request->input('skills_required'));
        $data['benefits']        = $this->normalizeArray($request->input('benefits'));
        $data['currency']        = strtoupper($data['currency'] ?? 'TND');
        $data['remote_allowed']  = $request->boolean('remote_allowed');
        $data['is_featured']     = $request->boolean('is_featured');

        return DB::transaction(function () use ($request, $data, $categoryIds) {
            // === Generate reference like YYYY-0001, YYYY-0002, ... (safe under concurrency) ===
            $currentYear = now()->year;

            $latest = JobOffer::query()
                ->whereYear('created_at', $currentYear)
                ->where('reference', 'like', $currentYear . '-%')
                ->orderByDesc('reference')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($latest && preg_match('/^' . $currentYear . '-(\d+)$/', $latest->reference, $m)) {
                $nextNumber = (int) $m[1] + 1;
            }

            $data['reference'] = sprintf('%d-%04d', $currentYear, $nextNumber);

            // Create offer
            $offer = JobOffer::create($data);

            // 🔗 Attach categories to pivot (category_job_offer) + pivot timestamps
            $offer->categories()->sync($categoryIds);

            // 🔗 Attach subscription plans (if any)
            $planIds = collect((array) $request->input('subscription_plan_ids', []))
                ->map(fn ($v) => (int) $v)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($planIds)) {
                $offer->subscriptionPlans()->sync($planIds);
            }

            return redirect()
                ->route('job-offers.index')
                ->with('success', 'Offre d’emploi créée avec succès (réf. ' . $offer->reference . ').');
        });
    }


    public function show(JobOffer $job_offer)
    {
        // (Optional) increment views count
        $job_offer->increment('views_count');
        $job_offer->load(['company','subscriptionPlans']);

        return view('job_offers.show', ['offer' => $job_offer]);
    }

    public function edit(JobOffer $job_offer)
    {
        $job_offer->load(['subscriptionPlans:id', 'company']); // keep existing + okay to preload other rels

        return view('job_offers.edit', [
            'offer'       => $job_offer,
            'companies'   => Company::orderBy('name')->get(['id','name']),
            'types'       => JobOffer::TYPES,
            'levels'      => JobOffer::LEVELS,
            'statuses'    => JobOffer::STATUSES,
            'plans'       => SubscriptionPlan::orderBy('name')->get(['id','name']),
            'categories'  => Category::orderBy('name')->get(['id','name']), // ✅ pass categories to view
        ]);
    }

    public function update(Request $request, JobOffer $job_offer)
    {
        $data = $this->validated($request, updating: true);

        // ✅ validate & set category
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
        ]);
        $data['category_id'] = (int) $request->input('category_id');

        // Normalize arrays & flags
        $data['skills_required'] = $this->normalizeArray($request->input('skills_required', $job_offer->skills_required));
        $data['benefits']        = $this->normalizeArray($request->input('benefits', $job_offer->benefits));
        $data['currency']        = strtoupper($data['currency'] ?? $job_offer->currency);
        $data['remote_allowed']  = $request->boolean('remote_allowed', $job_offer->remote_allowed);
        $data['is_featured']     = $request->boolean('is_featured', $job_offer->is_featured);

        $job_offer->update($data);

        // 🔗 sync selected subscription plans
        $planIds = $request->input('subscription_plan_ids', []);
        $job_offer->subscriptionPlans()->sync($planIds);

        return redirect()->route('job-offers.index')
            ->with('success', 'Offre d’emploi mise à jour.');
    }


    public function destroy(JobOffer $job_offer)
    {
        $job_offer->delete();

        return redirect()->route('job-offers.index')
            ->with('success', 'Job offer deleted.');
    }

    /** Quick toggles (buttons on index/edit) */
    public function toggleStatus(JobOffer $job_offer)
    {
        $next = match ($job_offer->status) {
            JobOffer::STATUS_ACTIVE => JobOffer::STATUS_PAUSED,
            JobOffer::STATUS_PAUSED => JobOffer::STATUS_ACTIVE,
            JobOffer::STATUS_DRAFT  => JobOffer::STATUS_ACTIVE,
            JobOffer::STATUS_CLOSED => JobOffer::STATUS_ACTIVE,
            default                 => JobOffer::STATUS_ACTIVE,
        };

        $job_offer->update(['status' => $next]);

        return back()->with('success', 'Status updated.');
    }

    public function toggleFeatured(JobOffer $job_offer)
    {
        $job_offer->update(['is_featured' => ! (bool) $job_offer->is_featured]);
        return back()->with('success', 'Featured flag updated.');
    }

    /* ---------------- helpers ---------------- */

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'company_id'           => ['required','exists:companies,id'],
            'title'                => ['required','string','max:255'],
            'description'          => ['required','string'],
            'requirements'         => ['nullable','string'],
            'responsibilities'     => ['nullable','string'],

            'job_type'             => ['required', Rule::in(JobOffer::TYPES)],
            'experience_level'     => ['required', Rule::in(JobOffer::LEVELS)],

            'salary_min'           => ['nullable','numeric','min:0'],
            'salary_max'           => ['nullable','numeric','min:0','gte:salary_min'],
            'currency'             => ['nullable','string','size:3'],

            'location'             => ['required','string','max:200'],
            'city'                 => ['required','string','max:100'],
            'governorate'          => ['required','string','max:100'],

            'remote_allowed'       => ['sometimes','boolean'],
            'skills_required'      => ['nullable'], // array or JSON or comma list
            'benefits'             => ['nullable'], // array or JSON or comma list
            'application_deadline' => ['nullable','date'],

            'is_featured'          => ['sometimes','boolean'],
            'status'               => ['sometimes', Rule::in(JobOffer::STATUSES)],

            // 🔗 subscription plans pivot
            'subscription_plan_ids'   => ['sometimes','array'],
            'subscription_plan_ids.*' => ['integer','exists:subscription_plans,id'],
        ]);
    }

    /** JSON string | array | "a, b, c" | multi-line -> array */
    private function normalizeArray($value): ?array
    {
        if ($value === null || $value === '') return null;
        if (is_array($value)) return array_values(array_filter(array_map('trim', $value), fn($v) => $v !== ''));

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded), fn($v) => $v !== ''));
        }

        $parts = preg_split('/[\r\n,]+/', (string) $value);
        $parts = array_values(array_filter(array_map('trim', $parts), fn($v) => $v !== ''));
        return $parts ?: null;
    }
}
