<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class JobApplicationController extends Controller
{
    /** List + filters */
    public function index(Request $request)
    {
       
        // First render: load the Blade shell; JS will fetch data via AJAX.
        return view('job_applications.index');
    }
    /** Show form to create a new job application */
     public function data(Request $request)
    {
        // If the front-end asks for JSON (our AJAX table), return a flat array

            $q        = $request->input('q');            // search term
            $status   = $request->input('status');
            $userId   = $request->input('user_id');
            $offerId  = $request->input('job_offer_id');
            $reviewer = $request->input('reviewed_by');
            $from     = $request->input('from');         // yyyy-mm-dd
            $to       = $request->input('to');           // yyyy-mm-dd

            $apps = \App\Models\JobApplication::query()
                ->with(['user:id,first_name,last_name,email', 'jobOffer:id,title,company_id', 'jobOffer.company:id,name', 'reviewer:id,first_name,last_name,email'])
                ->when($q, function ($qb) use ($q) {
                    $qb->whereHas('user', fn($uq) =>
                            $uq->where('email', 'like', "%{$q}%")
                            ->orWhere('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name',  'like', "%{$q}%"))
                    ->orWhereHas('jobOffer', fn($jq) =>
                            $jq->where('title', 'like', "%{$q}%"));
                })
                ->when($status,  fn($qb) => $qb->where('status', $status))
                ->when($userId,  fn($qb) => $qb->where('user_id', $userId))
                ->when($offerId, fn($qb) => $qb->where('job_offer_id', $offerId))
                ->when($reviewer,fn($qb) => $qb->where('reviewed_by', $reviewer))
                ->when($from,    fn($qb) => $qb->where('applied_at', '>=', \Carbon\Carbon::parse($from)->startOfDay()))
                ->when($to,      fn($qb) => $qb->where('applied_at', '<=', \Carbon\Carbon::parse($to)->endOfDay()))
                ->orderByDesc('applied_at')
                ->get();

            $payload = $apps->map(function (\App\Models\JobApplication $a) {
                $user = $a->user;
                $offer = $a->jobOffer;
                $company = optional($offer)->company;

                return [
                    'id'               => $a->id,
                    'applicant_name'   => trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
                    'applicant_email'  => $user->email ?? '',
                    'offer_title'      => $offer->title ?? '',
                    'company'          => optional($company)->name,
                    'status'           => $a->status,
                    'applied_at'       => optional($a->applied_at)->toDateString(),
                    'reviewed'         => (bool) ($a->reviewed_by || $a->reviewed_at),
                    'cv_file_url'      => $a->cv_file_url,
                ];
            });

            return response()->json($payload);
        
    }
    public function create()
    {
        return view('job_applications.create', [
            'users'    => User::orderBy('first_name')->get(['id','first_name','last_name','email']),
            'offers'   => JobOffer::orderBy('title')->get(['id','title']),
            'reviewers'=> User::orderBy('first_name')->get(['id','first_name','last_name','email']),
            'statuses' => JobApplication::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        // Handle CV upload (optional <input name="cv">)
        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('cv', 'public');
            $data['cv_file_url'] = Storage::disk('public')->url($path);
        }

        $data['additional_documents'] = $this->normalizeArray($request->input('additional_documents'));

        // If reviewed_by provided without reviewed_at, set now()
        if (!empty($data['reviewed_by']) && empty($data['reviewed_at'])) {
            $data['reviewed_at'] = now();
        }

        $app = JobApplication::create($data);

        return redirect()->route('job-applications.index')
            ->with('success', 'Application created successfully.');
    }

    public function show(JobApplication $job_application)
    {
        $job_application->load(['user','jobOffer.company','reviewer']);
        return view('job_applications.show', ['app' => $job_application]);
    }

    public function edit(JobApplication $job_application)
    {
        return view('job_applications.edit', [
            'app'      => $job_application->load(['user','jobOffer','reviewer']),
            'users'    => User::orderBy('first_name')->get(['id','first_name','last_name','email']),
            'offers'   => JobOffer::orderBy('title')->get(['id','title']),
            'reviewers'=> User::orderBy('first_name')->get(['id','first_name','last_name','email']),
            'statuses' => JobApplication::STATUSES,
        ]);
    }

    public function update(Request $request, JobApplication $job_application)
    {
        $data = $this->validated($request, updating: true);

        // CV upload replace
        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('cv', 'public');
            $data['cv_file_url'] = Storage::disk('public')->url($path);
        }

        $data['additional_documents'] = $this->normalizeArray(
            $request->input('additional_documents', $job_application->additional_documents)
        );

        if (!empty($data['reviewed_by']) && empty($data['reviewed_at'])) {
            $data['reviewed_at'] = now();
        }

        $job_application->update($data);

        return redirect()->route('job-applications.edit', $job_application)
            ->with('success', 'Application updated.');
    }

    public function destroy(JobApplication $job_application)
    {
        $job_application->delete();
        return redirect()->route('job-applications.index')
            ->with('success', 'Application deleted.');
    }

    /** Quick status change button */
    public function setStatus(Request $request, JobApplication $job_application)
    {
        $request->validate([
            'status' => ['required', Rule::in(JobApplication::STATUSES)],
        ]);

        $job_application->update(['status' => $request->input('status')]);

        return back()->with('success', 'Status updated.');
    }

    /** Mark reviewed by current user (optional note/response) */
    public function markReviewed(Request $request, JobApplication $job_application)
    {
        $request->validate([
            'response_message' => ['nullable','string'],
            'admin_notes'      => ['nullable','string'],
        ]);

        $job_application->update([
            'reviewed_by'     => auth()->id(),
            'reviewed_at'     => now(),
            'response_message'=> $request->input('response_message', $job_application->response_message),
            'admin_notes'     => $request->input('admin_notes', $job_application->admin_notes),
            'status'          => $job_application->status === JobApplication::STATUS_SUBMITTED
                                   ? JobApplication::STATUS_UNDER_REVIEW
                                   : $job_application->status,
        ]);

        return back()->with('success', 'Application marked as reviewed.');
    }

    /* ---------------- helpers ---------------- */

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'user_id'        => ['required','exists:users,id'],
            'job_offer_id'   => ['required','exists:job_offers,id'],

            'status'         => ['sometimes', Rule::in(JobApplication::STATUSES)],

            'cv'             => [$updating ? 'nullable' : 'sometimes','file','mimes:pdf,doc,docx','max:4096'], // optional upload
            'cv_file_url'    => ['nullable','url','max:500'],

            'additional_documents' => ['nullable'], // JSON/array/string

            'admin_notes'    => ['nullable','string'],
            'reviewed_by'    => ['nullable','exists:users,id'],
            'reviewed_at'    => ['nullable','date'],
            'response_message'=> ['nullable','string'],
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
