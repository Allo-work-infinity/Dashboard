<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class JobApplicationApiController extends Controller
{
    public function __construct()
    {
        // All endpoints require a token (Sanctum)
        $this->middleware('auth:sanctum');

        // Optional: block admins from using this user-facing API
        $this->middleware(function ($request, $next) {
            if (Auth::user()?->is_admin) {
                return response()->json(['message' => 'Admins cannot use this endpoint.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * GET /api/job-applications
     * List the authenticated user's applications.
     * Query (optional): q, status, job_offer_id, from, to, per_page, page
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $q       = $request->query('q');
        $status  = $request->query('status');
        $offerId = $request->query('job_offer_id');
        $from    = $request->query('from');
        $to      = $request->query('to');

        $with = ['jobOffer.company:id,name']; // context for list
        $apps = JobApplication::query()
            ->with($with)
            ->forUser($user->id)
            ->when($q, function ($qb) use ($q) {
                $qb->whereHas('jobOffer', fn($jq) =>
                    $jq->where('title', 'like', "%{$q}%")
                       ->orWhereHas('company', fn($cq) => $cq->where('name', 'like', "%{$q}%"))
                )->orWhere('response_message', 'like', "%{$q}%");
            })
            ->when($status, fn($qb) => $qb->where('status', $status))
            ->when($offerId, fn($qb) => $qb->where('job_offer_id', $offerId))
            ->when($from, fn($qb) => $qb->where('applied_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($to,   fn($qb) => $qb->where('applied_at', '<=', Carbon::parse($to)->endOfDay()))
            ->orderByDesc('applied_at');

        if ($request->filled('per_page')) {
            $p = $apps->paginate(
                max(1, (int)$request->integer('per_page', 15)),
                ['*'],
                'page',
                max(1, (int)$request->integer('page', 1))
            )->withQueryString();

            return response()->json([
                'data' => $p->getCollection()->map(fn($a) => $this->toListItem($a)),
                'meta' => [
                    'current_page' => $p->currentPage(),
                    'per_page'     => $p->perPage(),
                    'total'        => $p->total(),
                    'last_page'    => $p->lastPage(),
                ],
            ]);
        }

        return response()->json(
            $apps->get()->map(fn($a) => $this->toListItem($a))
        );
    }

    /**
     * GET /api/job-applications/{id}
     * Show one application (must belong to the auth user).
     */
    public function show(int $id)
    {
        $app = JobApplication::with(['jobOffer.company', 'reviewer:id,first_name,last_name,email'])
            ->findOrFail($id);

        $this->ownerOr404($app->user_id);

        return response()->json($this->toDetails($app));
    }

    /**
     * POST /api/job-applications
     * Apply to a job (user_id comes from Auth, NOT from request).
     * Accepts optional multipart file "cv" (pdf/doc/docx).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'job_offer_id'         => ['required', 'integer', 'exists:job_offers,id'],
            'cv'                   => ['sometimes','file','mimes:pdf,doc,docx','max:4096'],
            'cv_file_url'          => ['nullable','url','max:500'],
            'additional_documents' => ['nullable'], // JSON/array/string
        ]);

        // Ensure offer is open (active and not past deadline)
        $offer = JobOffer::open()->find($data['job_offer_id']);
        if (!$offer) {
            return response()->json(['message' => 'This offer is closed or not available.'], 422);
        }

        // Prevent duplicate application by the same user to the same offer
        $exists = JobApplication::query()
            ->forUser($user->id)
            ->forOffer($data['job_offer_id'])
            ->exists();
        if ($exists) {
            return response()->json(['message' => 'You already applied to this offer.'], 409);
        }

        // Handle file upload (optional)
        $cvUrl = $data['cv_file_url'] ?? null;
        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('cv', 'public');
            $cvUrl = Storage::disk('public')->url($path);
        }

        $app = JobApplication::create([
            'user_id'              => $user->id,
            'job_offer_id'         => $data['job_offer_id'],
            'status'               => JobApplication::STATUS_SUBMITTED,
            'cv_file_url'          => $cvUrl,
            'additional_documents' => $this->normalizeArray($request->input('additional_documents')),
            // applied_at auto-filled via model timestamps (CREATED_AT = applied_at)
        ]);

        $app->load(['jobOffer.company']);

        return response()->json($this->toDetails($app), 201);
    }

    /**
     * PUT/PATCH /api/job-applications/{id}
     * The owner can update non-admin fields while the application is not final (accepted/rejected).
     */
    public function update(Request $request, int $id)
    {
        $app = JobApplication::findOrFail($id);
        $this->ownerOr404($app->user_id);

        if ($app->is_final) {
            return response()->json(['message' => 'Finalized applications cannot be updated.'], 422);
        }

        $data = $request->validate([
            'cv'                   => ['sometimes','file','mimes:pdf,doc,docx','max:4096'],
            'cv_file_url'          => ['nullable','url','max:500'],
            'additional_documents' => ['nullable'], // JSON/array/string
        ]);

        // File or URL update
        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('cv', 'public');
            $app->cv_file_url = Storage::disk('public')->url($path);
        } elseif (array_key_exists('cv_file_url', $data)) {
            $app->cv_file_url = $data['cv_file_url'];
        }

        if ($request->has('additional_documents')) {
            $app->additional_documents = $this->normalizeArray($request->input('additional_documents'));
        }

        $app->save();

        $app->load(['jobOffer.company']);
        return response()->json($this->toDetails($app));
    }

    /**
     * DELETE /api/job-applications/{id}
     * Owner can delete (withdraw) if not final.
     */
    public function destroy(int $id)
    {
        $app = JobApplication::findOrFail($id);
        $this->ownerOr404($app->user_id);

        if ($app->is_final) {
            return response()->json(['message' => 'Finalized applications cannot be deleted.'], 422);
        }

        $app->delete();
        return response()->json(['message' => 'Application deleted.']);
    }

    /* ================= helpers ================= */

    private function ownerOr404(int $ownerId): void
    {
        if (Auth::id() !== $ownerId) {
            abort(404); // hide existence from other users
        }
    }

    /** JSON string | array | "a, b, c" | multi-line -> array */
    private function normalizeArray($value): ?array
    {
        if ($value === null || $value === '') return null;
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), fn($v) => $v !== ''));
        }
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded), fn($v) => $v !== ''));
        }
        $parts = preg_split('/[\r\n,]+/', (string) $value);
        $parts = array_values(array_filter(array_map('trim', $parts), fn($v) => $v !== ''));
        return $parts ?: null;
    }

    private function toListItem(JobApplication $a): array
    {
        return [
            'id'              => $a->id,
            'status'          => $a->status,
            'offer_id'        => $a->job_offer_id,
            'offer_title'     => optional($a->jobOffer)->title,
            'company'         => optional(optional($a->jobOffer)->company)->name,
            'cv_file_url'     => $a->cv_file_url,
            'reviewed'        => !empty($a->reviewed_at),
            'applied_at'      => optional($a->applied_at)?->toIso8601String(),
            'updated_at'      => optional($a->updated_at)?->toIso8601String(),
            'is_final'        => (bool)$a->is_final,
        ];
    }

    private function toDetails(JobApplication $a): array
    {
        return [
            'id'                   => $a->id,
            'user_id'              => $a->user_id,     // owner (you)
            'job_offer_id'         => $a->job_offer_id,
            'offer_title'          => optional($a->jobOffer)->title,
            'company'              => optional(optional($a->jobOffer)->company)->name,
            'status'               => $a->status,
            'cv_file_url'          => $a->cv_file_url,
            'additional_documents' => $a->additional_documents,
            'reviewed_by'          => $a->reviewed_by,
            'reviewed_at'          => optional($a->reviewed_at)?->toIso8601String(),
            'response_message'     => $a->response_message,
            'applied_at'           => optional($a->applied_at)?->toIso8601String(),
            'updated_at'           => optional($a->updated_at)?->toIso8601String(),
            'is_final'             => (bool)$a->is_final,
        ];
    }
}
