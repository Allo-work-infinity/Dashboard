<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\UserSubscription;

class AuthApiController extends Controller
{
    /**
     * POST /api/auth/register
     * Create a non-admin user and return an API token.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name'  => ['required','string','max:100'],
            'last_name'   => ['required','string','max:100'],
            'email'       => ['required','email', Rule::unique('users', 'email')],
            'password'    => ['required','string','min:8','confirmed'], // needs password_confirmation
            'phone'       => ['nullable','string','max:20'],
            'city'        => ['nullable','string','max:100'],
            'governorate' => ['nullable','string','max:100'],
        ]);

        // Force non-admin
        $data['is_admin'] = false;

        // If your User model has 'password' => 'hashed' cast, this plain assignment is enough.
        $user = User::create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $this->userPayload($user),
            'token_type' => 'Bearer',
            'access_token' => $token,
        ], 201);
    }

    /**
     * POST /api/auth/login
     * Issue token for non-admin users only.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! \Hash::check($credentials['password'], $user->password)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Block admins from this API
        if ($user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admins are not allowed to use this API.',
            ], 403);
        }

        // Optional: block banned/suspended users
        if (in_array($user->status, ['banned', 'suspended'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not allowed to sign in.',
                'status'  => $user->status,
            ], 403);
        }

        // 🔎 Get the CURRENT (active & not expired) subscription, if any
        $current = UserSubscription::query()
            ->forUser($user->id)
            ->current()
            ->with(['plan:id,name,price,duration_days']) // small plan projection
            ->latest('start_date')
            ->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $this->userPayload($user),

            // NEW:
            'has_active_subscription' => (bool) $current,
            'subscription' => $current ? [
                'id'              => $current->id,
                'status'          => $current->status,         // 'active'
                'is_current'      => $current->is_current,     // accessor
                'remaining_days'  => $current->remaining_days, 
                'payment_status'=> $current->payment_status,// accessor
                'start_date'      => optional($current->start_date)->toIso8601String(),
                'end_date'        => optional($current->end_date)->toIso8601String(),
                'auto_renewal'    => (bool) $current->auto_renewal,
                'amount_paid'     => $current->amount_paid !== null ? (float) $current->amount_paid : null,
                'plan' => $current->relationLoaded('plan') && $current->plan ? [
                    'id'            => $current->plan->id,
                    'name'          => $current->plan->name,
                    'price'         => (float) $current->plan->price,      // decimal cast -> float
                    'duration_days' => (int) $current->plan->duration_days,
                ] : null,
            ] : null,

            'token_type'   => 'Bearer',
            'access_token' => $token,
        ]);
    }


    /**
     * GET /api/auth/me (auth:sanctum)
     */
    


    /**
     * POST /api/auth/logout (auth:sanctum)
     * Revoke only the current access token.
     */
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out.',
        ]);
    }

    /**
     * POST /api/auth/logout-all (auth:sanctum)
     * Revoke all tokens for the user (sign out all devices).
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices.',
        ]);
    }

    /* ------------------ helpers ------------------ */

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
