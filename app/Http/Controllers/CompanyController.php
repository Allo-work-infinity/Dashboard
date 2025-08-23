<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Display a listing of the companies.
     */
    public function index()
    {

        // First render: load the Blade shell; JS will fetch data via AJAX.
        return view('companies.index', [
            'sizes'    => Company::COMPANY_SIZES,
            'statuses' => [Company::STATUS_ACTIVE, Company::STATUS_SUSPENDED],
        ]);// Always the page, never JSON here
    }
    /** List + filters */
    public function data(Request $request)
    {
        // If the front-end asks for JSON (our AJAX table), return a flat array

            $q           = $request->input('q');
            $size        = $request->input('company_size');
            $status      = $request->input('status');
            $verified    = $request->input('is_verified'); // 0/1
            $city        = $request->input('city');
            $governorate = $request->input('governorate');

            $companies = Company::query()
                ->when($q, fn ($qb) => $qb->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('industry', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('governorate', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
                }))
                ->when($size,        fn ($qb) => $qb->where('company_size', $size))
                ->when($status,      fn ($qb) => $qb->where('status', $status))
                ->when($request->filled('is_verified'), fn ($qb) => $qb->where('is_verified', $request->boolean('is_verified')))
                ->when($city,        fn ($qb) => $qb->where('city', 'like', "%{$city}%"))
                ->when($governorate, fn ($qb) => $qb->where('governorate', 'like', "%{$governorate}%"))
                ->orderByDesc('created_at')
                ->get([
                    'id','name','description','industry','company_size','city','governorate',
                    'website','logo_url','contact_email','contact_phone','is_verified','status','created_at'
                ]);

            $payload = $companies->map(function (Company $c) {
                return [
                    'id'            => $c->id,
                    'name'          => $c->name,
                    'description'   => Str::limit((string) $c->description, 120),
                    'industry'      => $c->industry,
                    'company_size'  => $c->company_size,
                    'city'          => $c->city,
                    'governorate'   => $c->governorate,
                    'status'        => $c->status,                  // 'active' | 'suspended'
                    'is_verified'   => (bool) $c->is_verified,      // true/false
                    'created_at'    => optional($c->created_at)->toDateString(),
                ];
            });

            return response()->json($payload);
       
    }
    
    public function create()
    {
        return view('companies.create', [
            'sizes'    => Company::COMPANY_SIZES,
            'statuses' => [Company::STATUS_ACTIVE, Company::STATUS_SUSPENDED],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $company = Company::create($data);

        return redirect()
            ->route('companies.index', $company)
            ->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', [
            'company'  => $company,
            'sizes'    => Company::COMPANY_SIZES,
            'statuses' => [Company::STATUS_ACTIVE, Company::STATUS_SUSPENDED],
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $data = $this->validated($request, updating: true);

        $company->update($data);

        return redirect()
            ->route('companies.edit', $company)
            ->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company deleted.');
    }

    /** Quick toggles from index table/buttons */
    public function toggleStatus(Company $company)
    {
        $company->update([
            'status' => $company->status === Company::STATUS_ACTIVE
                ? Company::STATUS_SUSPENDED
                : Company::STATUS_ACTIVE,
        ]);

        return back()->with('success', 'Company status updated.');
    }

    public function toggleVerified(Company $company)
    {
        $company->update(['is_verified' => ! (bool) $company->is_verified]);

        return back()->with('success', 'Verification updated.');
    }

    /* ---------------- helpers ---------------- */

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'name'          => ['required','string','max:200'],
            'description'   => ['nullable','string'],
            'industry'      => ['nullable','string','max:100'],

            'company_size'  => ['nullable', Rule::in(Company::COMPANY_SIZES)],

            'website'       => ['nullable','url','max:255'],
            'logo_url'      => ['nullable','url','max:500'],
            'address'       => ['nullable','string'],
            'city'          => ['nullable','string','max:100'],
            'governorate'   => ['nullable','string','max:100'],
            'contact_email' => ['nullable','email','max:255'],
            'contact_phone' => ['nullable','string','max:20'],

            'is_verified'   => ['sometimes','boolean'],
            'status'        => ['sometimes', Rule::in([Company::STATUS_ACTIVE, Company::STATUS_SUSPENDED])],
        ]);
    }
}
