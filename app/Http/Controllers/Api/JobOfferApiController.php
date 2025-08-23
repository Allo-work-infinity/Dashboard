<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use App\Models\UserSubscription;                 // ✅ add this
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;      
// at the top of your controller
use App\Models\JobApplication;
       // ✅ and this

class JobOfferApiController extends Controller
{
    /**
     * GET /api/job-offers
     */
    public function index(Request $request)
    {
        // 🔐 must be authenticated
        $auth = Auth::user();
        abort_unless($auth, 401, 'Unauthenticated.');

        // ========== Plan gate (optional) ==========
        // pass ?bypass_plan_gate=1 to see all offers regardless of the user's plans
        $applyPlanGate = !$request->boolean('bypass_plan_gate', false);

        $userPlanIds = [];
        if ($applyPlanGate) {
            $userPlanIds = UserSubscription::query()
                ->forUser($auth->id)
                ->current()
                ->pluck('plan_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($userPlanIds)) {
                // keep JSON shape if caller asked for pagination
                if ($request->has('per_page') || $request->has('page')) {
                    return response()->json([
                        'data' => [],
                        'meta' => [
                            'current_page' => (int) $request->query('page', 1),
                            'last_page'    => 1,
                            'per_page'     => (int) $request->query('per_page', 15),
                            'total'        => 0,
                        ],
                    ]);
                }
                return response()->json([]);
            }
        }

        // ---------- Category-name special handling (all/any) ----------
        $rawCategoryName = trim((string) $request->query('category_name', ''));
        $lcName = $rawCategoryName !== '' ? mb_strtolower($rawCategoryName, 'UTF-8') : '';
        $isAllCategoryName = $rawCategoryName !== '' && in_array($lcName, [
            'all', 'toute', 'toutes', 'tous', 'all categories', 'all-category', '*',
        ], true);

        // ---------- Base query (plan-gated) ----------
        // IMPORTANT: qualify columns inside with() to avoid "Column 'id' is ambiguous"
        $qb = JobOffer::query()
            ->select('job_offers.*')
            ->with([
                'company' => function ($q) {
                    $q->select('companies.id', 'companies.name', 'companies.logo_url');
                },
                'categories' => function ($q) {
                    $q->select('categories.id', 'categories.name', 'categories.slug');
                },
            ]);

        if ($applyPlanGate) {
            $qb->whereHas('subscriptionPlans', function ($q) use ($userPlanIds) {
                $q->whereIn('subscription_plans.id', $userPlanIds);
            });
        }

        // ---------- Main filter switch ----------
        // If category_name=all* and no explicit filter, default to 'all'
        $defaultFilter = $isAllCategoryName && !$request->has('filter') ? 'all' : 'open';
        $filter = strtolower((string) $request->query('filter', $defaultFilter));

        switch ($filter) {
            case 'all':
                // keep plan gate if enabled, no status restriction
                break;

            case 'my-offer':
            case 'my':
            case 'mine':
                if (!empty($auth->company_id)) {
                    $qb->where('company_id', $auth->company_id);
                    if (!$request->boolean('include_closed', false)) {
                        $qb->open();
                    }
                } else {
                    // keep JSON shape if paginated
                    if ($request->has('per_page') || $request->has('page')) {
                        return response()->json([
                            'data' => [],
                            'meta' => [
                                'current_page' => (int) $request->query('page', 1),
                                'last_page'    => 1,
                                'per_page'     => (int) $request->query('per_page', 15),
                                'total'        => 0,
                            ],
                        ]);
                    }
                    return response()->json([]);
                }
                break;

            case 'featured':
                $qb->open()->where('is_featured', true);
                break;

            case 'remote':
                $qb->open()->where('remote_allowed', true);
                break;

            case 'closed':
                $qb->where('status', JobOffer::STATUS_CLOSED);
                break;

            case 'populer':   // UI spelling
            case 'popular':   // alias
                $qb->open()
                ->addSelect([
                    'applications_total' => JobApplication::selectRaw('COUNT(*)')
                        ->whereColumn('job_applications.job_offer_id', 'job_offers.id'),
                ])
                ->orderByDesc('applications_total');
                break;

            default:
                // default: open only
                $qb->open();
                break;
        }

        // ---------- Extra filters ----------
        if ($request->filled('q'))               $qb->search($request->query('q'));
        if ($request->filled('company_id'))      $qb->where('company_id', $request->query('company_id'));
        if ($request->filled('job_type'))        $qb->where('job_type', $request->query('job_type'));
        if ($request->filled('experience_level'))$qb->where('experience_level', $request->query('experience_level'));
        if ($request->filled('city'))            $qb->where('city', 'like', '%' . $request->query('city') . '%');
        if ($request->filled('governorate'))     $qb->where('governorate', 'like', '%' . $request->query('governorate') . '%');
        if ($request->filled('remote_allowed'))  $qb->where('remote_allowed', $request->boolean('remote_allowed'));
        if ($request->filled('is_featured'))     $qb->where('is_featured', $request->boolean('is_featured'));

        if ($request->filled('min_salary')) {
            $min = $request->query('min_salary');
            $qb->where(function ($q) use ($min) {
                $q->whereNull('salary_min')->orWhere('salary_min', '>=', $min);
            });
        }
        if ($request->filled('max_salary')) {
            $max = $request->query('max_salary');
            $qb->where(function ($q) use ($max) {
                $q->whereNull('salary_max')->orWhere('salary_max', '<=', $max);
            });
        }
        if ($request->filled('deadline_before')) {
            $qb->whereDate('application_deadline', '<=', Carbon::parse($request->query('deadline_before'))->toDateString());
        }
        if ($request->filled('deadline_after')) {
            $qb->whereDate('application_deadline', '>=', Carbon::parse($request->query('deadline_after'))->toDateString());
        }

        // ---- Category filters ----
        $modeAll = $request->query('category_mode') === 'all';

        // Plain category NAME (skip sentinel "all")
        if ($request->filled('category_name') && !$isAllCategoryName) {
            $name = $rawCategoryName;
            $slug = Str::slug($name);
            $lc   = mb_strtolower($name, 'UTF-8');

            $qb->whereHas('categories', function ($q) use ($slug, $lc) {
                $q->where('categories.slug', $slug)
                ->orWhereRaw('LOWER(categories.name) = ?', [$lc]);
            });
        }

        // Single ID
        if ($request->filled('category_id')) {
            $id = (int) $request->query('category_id');
            $qb->whereHas('categories', fn ($q) => $q->where('categories.id', $id));
        }

        // Multiple IDs (CSV or array)
        if ($request->filled('category_ids')) {
            $ids = $request->input('category_ids');
            if (is_string($ids)) {
                $ids = array_filter(array_map('intval', explode(',', $ids)));
            } elseif (is_array($ids)) {
                $ids = array_filter(array_map('intval', $ids));
            } else {
                $ids = [];
            }

            if (!empty($ids)) {
                if ($modeAll) {
                    foreach ($ids as $id) {
                        $qb->whereHas('categories', fn ($q) => $q->where('categories.id', $id));
                    }
                } else {
                    $qb->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
                }
            }
        }

        // Single slug (ignore "all")
        if ($request->filled('category_slug') && mb_strtolower($request->query('category_slug'), 'UTF-8') !== 'all') {
            $slug = (string) $request->query('category_slug');
            $qb->whereHas('categories', fn ($q) => $q->where('categories.slug', $slug));
        }

        // Multiple slugs (CSV or array)
        if ($request->filled('category_slugs')) {
            $slugs = $request->input('category_slugs');
            if (is_string($slugs)) {
                $slugs = array_filter(array_map('trim', explode(',', $slugs)));
            } elseif (!is_array($slugs)) {
                $slugs = [];
            }

            if (!empty($slugs)) {
                if ($modeAll) {
                    foreach ($slugs as $slug) {
                        if (mb_strtolower($slug, 'UTF-8') === 'all') continue;
                        $qb->whereHas('categories', fn ($q) => $q->where('categories.slug', $slug));
                    }
                } else {
                    $slugs = array_values(array_filter($slugs, fn ($s) => mb_strtolower($s, 'UTF-8') !== 'all'));
                    if (!empty($slugs)) {
                        $qb->whereHas('categories', fn ($q) => $q->whereIn('categories.slug', $slugs));
                    }
                }
            }
        }

        // ---------- Sort (skip if popular already set order) ----------
        if (!in_array($filter, ['populer', 'popular'], true)) {
            $sort  = $request->query('sort', 'created_at');
            $order = strtolower($request->query('order', 'desc')) === 'asc' ? 'asc' : 'desc';
            if (in_array($sort, ['created_at', 'application_deadline', 'salary_min', 'salary_max', 'views_count'], true)) {
                $qb->orderBy($sort, $order);
            } else {
                $qb->orderByDesc('created_at');
            }
        }

        // ---------- Pagination (optional) ----------
        $shouldPaginate = $request->has('per_page') || $request->has('page');
        if ($shouldPaginate) {
            $perPage   = (int) $request->query('per_page', 15);
            $pageNum   = (int) $request->query('page', 1);
            $paginator = $qb->paginate($perPage, ['*'], 'page', $pageNum)->appends($request->query());

            $data = collect($paginator->items())->map(function (JobOffer $o) {
                $applicationsCount = isset($o->applications_total)
                    ? (int) $o->applications_total
                    : (int) $o->applications_count;

                return [
                    'id'                 => $o->id,
                    'title'              => $o->title,
                    'reference'          => $o->reference,
                    'description'        => $o->description,
                    'company_id'         => $o->company_id,
                    'company'            => optional($o->company)->name,
                    'logo_url'           => optional($o->company)->logo_url,
                    'job_type'           => $o->job_type,
                    'experience_level'   => $o->experience_level,
                    'city'               => $o->city,
                    'governorate'        => $o->governorate,
                    'salary_min'         => $o->salary_min,
                    'salary_max'         => $o->salary_max,
                    'currency'           => $o->currency,
                    'is_featured'        => (bool) $o->is_featured,
                    'status'             => $o->status,
                    'is_open'            => $o->is_open,
                    'applications_count' => $applicationsCount,
                    'categories'         => $o->categories->map(fn ($c) => [
                        'id'   => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ])->all(),
                ];
            })->values();

            return response()->json([
                'data' => $data,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                ],
            ]);
        }

        // ---------- Non-paginated ----------
        $offers = $qb->get();

        $list = $offers->map(function (JobOffer $o) {
            $applicationsCount = isset($o->applications_total)
                ? (int) $o->applications_total
                : (int) $o->applications_count;

            return [
                'id'                 => $o->id,
                'title'              => $o->title,
                'reference'          => $o->reference,
                'description'        => $o->description,
                'company_id'         => $o->company_id,
                'company'            => optional($o->company)->name,
                'logo_url'           => optional($o->company)->logo_url,
                'job_type'           => $o->job_type,
                'experience_level'   => $o->experience_level,
                'city'               => $o->city,
                'governorate'        => $o->governorate,
                'salary_min'         => $o->salary_min,
                'salary_max'         => $o->salary_max,
                'currency'           => $o->currency,
                'is_featured'        => (bool) $o->is_featured,
                'status'             => $o->status,
                'is_open'            => $o->is_open,
                'applications_count' => $applicationsCount,
                'categories'         => $o->categories->map(fn ($c) => [
                    'id'   => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ])->all(),
            ];
        })->values();

        return response()->json($list);
    }







    /**
     * GET /api/job-offers/{job_offer}
     */
    public function show(JobOffer $job_offer)
    {
        // must be authenticated
        $auth = Auth::user();
        abort_unless($auth, 401, 'Unauthenticated.');

        if ($job_offer->status === JobOffer::STATUS_DRAFT) {
            abort(404);
        }

        // ensure the offer is allowed by user's current plans
        $userPlanIds = UserSubscription::query()
            ->forUser($auth->id)
            ->current()
            ->pluck('plan_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $allowed = $job_offer->subscriptionPlans()
            ->whereIn('subscription_plans.id', $userPlanIds)
            ->exists();

        abort_unless($allowed, 403, 'Forbidden.');

        $job_offer->load(['company:id,name', 'subscriptionPlans:id,name']);
        $job_offer->increment('views_count');

        return response()->json([
            'id'                   => $job_offer->id,
            'title'                => $job_offer->title,
            'company'              => optional($job_offer->company)->name,
            'company_id'           => $job_offer->company_id,
            'description'          => $job_offer->description,
            'requirements'         => $job_offer->requirements,
            'responsibilities'     => $job_offer->responsibilities,
            'job_type'             => $job_offer->job_type,
            'experience_level'     => $job_offer->experience_level,
            'city'                 => $job_offer->city,
            'governorate'          => $job_offer->governorate,
            'location'             => $job_offer->location,
            'salary_min'           => $job_offer->salary_min,
            'salary_max'           => $job_offer->salary_max,
            'currency'             => $job_offer->currency,
            'remote_allowed'       => (bool) $job_offer->remote_allowed,
            'skills_required'      => $job_offer->skills_required,
            'benefits'             => $job_offer->benefits,
            'status'               => $job_offer->status,
            'is_featured'          => (bool) $job_offer->is_featured,
            'application_deadline' => optional($job_offer->application_deadline)?->toDateString(),
            'is_open'              => $job_offer->is_open,
            'views_count'          => (int) $job_offer->views_count,
            'applications_count'   => (int) $job_offer->applications_count,
            'subscription_plans'   => $job_offer->subscriptionPlans->map(fn($p) => [
                'id' => $p->id, 'name' => $p->name
            ])->values(),
            'created_at'           => optional($job_offer->created_at)?->toIso8601String(),
            'updated_at'           => optional($job_offer->updated_at)?->toIso8601String(),
        ]);
    }

    /**
     * GET /api/job-offers/meta
     */
    public function meta()
    {
        return response()->json([
            'types'    => JobOffer::TYPES,
            'levels'   => JobOffer::LEVELS,
            'statuses' => JobOffer::STATUSES,
        ]);
    }

    /* ================= helpers ================= */

    
}
