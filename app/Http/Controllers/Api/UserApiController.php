<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserApiController extends Controller
{
    public function __construct()
    {
        // Protect these routes with Sanctum (or 'auth' if you're on web guard)
        $this->middleware('auth:sanctum');
    }

    /**
     * GET /users
     * Admin: list users (search + paginate)
     * Non-admin: returns their own profile (no listing)
     */
    public function index(Request $request)
    {
        $auth = Auth::user();

        if ($auth->is_admin) {
            $perPage = (int) $request->input('per_page', 15);
            $q       = $request->input('q');

            $users = User::query()
                ->when($q, function ($query) use ($q) {
                    $query->where('email', 'like', "%{$q}%")
                          ->orWhere('first_name', 'like', "%{$q}%")
                          ->orWhere('last_name', 'like', "%{$q}%");
                })
                ->orderByDesc('created_at')
                ->paginate($perPage);

            return response()->json($users);
        }

        // Non-admins only get themselves
        return response()->json($auth);
    }

    /**
     * GET /users/{user}
     * Admin: can view anyone
     * Non-admin: can only view self
     */
    public function show(User $user)
    {
        $auth = Auth::user();

        if (! $auth->is_admin && $auth->id !== $user->id) {
            abort(404); // hide existence
        }

        return response()->json($user);
    }
    public function me()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Block admins on this API (same rule as login)
        if ($user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admins are not allowed to use this API.',
            ], 403);
        }

        // Optional: keep the same policy as login for banned/suspended users
        if (in_array($user->status, ['banned', 'suspended'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not allowed to use this API.',
                'status'  => $user->status,
            ], 403);
        }

        // 🔎 Get the CURRENT (active & not expired) subscription, if any
        $current = \App\Models\UserSubscription::query()
            ->forUser($user->id)            // scope: filter by user_id
            ->current()                     // scope: active + not expired
            ->with(['plan:id,name,price,duration_days'])
            ->latest('start_date')
            ->first();

        return response()->json([
            'success' => true,
            'user'    => $this->userPayload($user),

            // ✅ Same additional fields as login response
            'has_active_subscription' => (bool) $current,
            'subscription' => $current ? [
                'id'             => $current->id,
                'status'         => $current->status,          // e.g. 'active'
                'is_current'     => $current->is_current,      // accessor
                'remaining_days' => $current->remaining_days,
                'payment_status'=> $current->payment_status,  // accessor
                'start_date'     => optional($current->start_date)->toIso8601String(),
                'end_date'       => optional($current->end_date)->toIso8601String(),
                'auto_renewal'   => (bool) $current->auto_renewal,
                'amount_paid'    => $current->amount_paid !== null ? (float) $current->amount_paid : null,
                'plan' => ($current->relationLoaded('plan') && $current->plan) ? [
                    'id'            => $current->plan->id,
                    'name'          => $current->plan->name,
                    'price'         => (float) $current->plan->price,
                    'duration_days' => (int) $current->plan->duration_days,
                ] : null,
            ] : null,

            // ⬇️ If you want to keep the *exact* keys as login, you can echo the current token:
            // 'token_type'   => 'Bearer',
            // 'access_token' => $request->bearerToken(), // optional (client already has it)
        ]);
    }
    /**
     * POST /users
     * Admin only: create users
     */
    public function store(Request $request)
    {
        $auth = Auth::user();
        if (! $auth->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $this->validateData($request, true);

        // Safety: never allow creating an admin via this endpoint unless admin explicitly sets it
        $data['is_admin'] = (bool)($data['is_admin'] ?? false);

        $user = User::create($data);

        return response()->json($user, 201);
    }

    /**
     * PUT/PATCH /users/{user}
     * Admin: can update anyone
     * Non-admin: can only update self; cannot change status or is_admin
     */
    public function update(Request $request)
    {
        $auth = Auth::user(); // ← on met à jour CE user

        // ---- Normalisation facultative ----
        // name -> first_name / last_name si non fournis
        if ($request->filled('name') && !$request->filled('first_name') && !$request->filled('last_name')) {
            $name  = trim($request->input('name'));
            $parts = preg_split('/\s+/', $name);
            $last  = array_pop($parts) ?? '';
            $first = trim(implode(' ', $parts));
            $request->merge([
                'first_name' => $request->input('first_name', $first),
                'last_name'  => $request->input('last_name',  $last),
            ]);
        }

        // location -> address, city, governorate si non fournis (format "adresse, ville, gouvernorat")
        if ($request->filled('location') &&
            !$request->filled('address') && !$request->filled('city') && !$request->filled('governorate')) {
            $chunks  = array_map('trim', explode(',', $request->input('location')));
            $address = $chunks[0] ?? null;
            $city    = $chunks[1] ?? null;
            $gov     = $chunks[2] ?? null;
            $request->merge([
                'address'     => $request->input('address', $address),
                'city'        => $request->input('city', $city),
                'governorate' => $request->input('governorate', $gov),
            ]);
        }

        // ---- Validation ----
        $data = $request->validate([
            'first_name'  => ['sometimes','nullable','string','max:100'],
            'last_name'   => ['sometimes','nullable','string','max:100'],
            'email'       => [
                'sometimes','email:rfc',
                Rule::unique('users','email')->ignore($auth->id), // ← ignore l'ID courant
            ],
            'phone'       => ['sometimes','nullable','string','max:30'],
            'address'     => ['sometimes','nullable','string','max:255'],
            'city'        => ['sometimes','nullable','string','max:120'],
            'governorate' => ['sometimes','nullable','string','max:120'],
            'password'    => ['sometimes','nullable','string','min:8'],
            // champs admin éventuellement envoyés par erreur depuis le mobile
            'is_admin'    => ['sometimes','boolean'],
            'status'      => ['sometimes','in:active,inactive,banned'],
        ]);

        // Mot de passe: ne pas modifier si vide, sinon hash
        if (!array_key_exists('password', $data) || empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Sécurité: si non-admin, on retire les champs réservés
        if (!$auth->is_admin) {
            unset($data['is_admin'], $data['status']);
        } else {
            // Admin: si is_admin non fourni, garder la valeur actuelle
            if (!array_key_exists('is_admin', $data)) {
                $data['is_admin'] = $auth->is_admin;
            }
        }

        // Mise à jour
        $auth->fill($data)->save();

        // Réponse simple et propre
        return response()->json([
            'user' => $auth->fresh(), // renvoie l'utilisateur à jour
        ], 200);
    }

    /**
     * DELETE /users/{user}
     * Admin: can delete anyone
     * Non-admin: can only delete self
     */
    public function destroy(User $user)
    {
        $auth = Auth::user();

        if (! $auth->is_admin && $auth->id !== $user->id) {
            abort(404);
        }

        $user->delete();

        return response()->noContent();
    }

    /**
     * Shared validation rules
     */
    private function validateData(Request $request, bool $isCreate, ?int $ignoreId = null): array
    {
        return $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($ignoreId),
            ],

            // required on create, optional on update
            'password' => $isCreate ? ['required','string','min:8'] : ['nullable','string','min:8'],

            'first_name'    => ['required','string','max:100'],
            'last_name'     => ['required','string','max:100'],
            'phone'         => ['nullable','string','max:20'],
            'date_of_birth' => ['nullable','date'],
            'address'       => ['nullable','string'],
            'city'          => ['nullable','string','max:100'],
            'governorate'   => ['nullable','string','max:100'],

            'profile_picture_url'   => ['nullable','url','max:500'],
            'cv_file_url'           => ['nullable','url','max:500'],

            'is_email_verified'       => ['sometimes','boolean'],
            'email_verification_token'=> ['nullable','string','max:255'],
            'password_reset_token'    => ['nullable','string','max:255'],
            'password_reset_expires'  => ['nullable','date'],

            'status'   => ['sometimes', Rule::in(User::STATUSES)], // admin-only effectively (stripped for non-admin)
            'last_access_time' => ['nullable','date'],

            'is_admin' => ['sometimes','boolean'], // admin-only effectively (stripped for non-admin)
        ]);
    }
    private function userPayload(User $u): array
    {
        return [
            'id'                 => $u->id,
            'first_name'         => $u->first_name,
            'last_name'          => $u->last_name,
            'name'               => $u->name, // accessor
            'email'              => $u->email,
            'phone'              => $u->phone,
            'city'               => $u->city,
            'governorate'        => $u->governorate,
            'status'             => $u->status,
            'profile_picture_url'=> $u->profile_picture_url,
            'cv_file_url'        => $u->cv_file_url,
            'is_admin'           => (bool) $u->is_admin, // will be false here
        ];
    }
}
