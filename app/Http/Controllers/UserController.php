<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // protect all routes
    }

    /** GET /users */
   public function index(Request $request)
    {
        // AJAX/JSON: return non-admin users as a flat array
        

        // First render loads the Blade shell; JS fetches data via AJAX
        return view('users.index');
    }
    //
     public function data(Request $request)
    {
        // AJAX/JSON: return non-admin users as a flat array
       
            $users = User::query()
                ->where('is_admin', false)
                ->orderByDesc('created_at')
                ->get(['id','first_name','last_name','email','status','is_admin']);

            // Shape the payload for your front-end (name, email, role, status)
            $payload = $users->map(function (User $u) {
                return [
                    'id'     => $u->id,
                    'name'   => $u->name,                 // accessor = "first last"
                    'email'  => $u->email,
                    'role'   => $u->is_admin ? 'admin' : 'user',
                    'status' => $u->status ?? 'active',
                ];
            });

            return response()->json($payload);
     
    }

    /** GET /users/create */
    public function create()
    {
        return view('users.create');
    }

    /** POST /users */
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'        => ['required','string','max:100'],
            'last_name'         => ['required','string','max:100'],
            'email'             => ['required','email','max:255','unique:users,email'],
            'password'          => ['required','string','min:8','confirmed'], // needs password_confirmation
            'phone'             => ['nullable','string','max:20'],
            'date_of_birth'     => ['nullable','date'],
            'address'           => ['nullable','string'],
            'city'              => ['nullable','string','max:100'],
            'governorate'       => ['nullable','string','max:100'],
            'profile_picture_url' => ['nullable','url'],
            'cv_file_url'       => ['nullable','url'],
            'status'            => ['nullable', Rule::in(User::STATUSES)],
        ]);

        // Enforce non-admin
        $data['is_admin'] = false;

        $user = User::create($data); // password is auto-hashed via cast

        return redirect()->route('users.index')
            ->with('success', 'User created.');
    }

    /** GET /users/{user} */
    public function show(User $user)
    {
        if ($user->is_admin) abort(404);
        return view('users.show', compact('user'));
    }

    /** GET /users/{user}/edit */
    public function edit(User $user)
    {
        if ($user->is_admin) abort(404);
        return view('users.edit', compact('user'));
    }

    /** PUT/PATCH /users/{user} */
    public function update(Request $request, User $user)
    {
        if ($user->is_admin) abort(404);

        $data = $request->validate([
            'first_name'        => ['required','string','max:100'],
            'last_name'         => ['required','string','max:100'],
            'email'             => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password'          => ['nullable','string','min:8','confirmed'],
            'phone'             => ['nullable','string','max:20'],
            'date_of_birth'     => ['nullable','date'],
            'address'           => ['nullable','string'],
            'city'              => ['nullable','string','max:100'],
            'governorate'       => ['nullable','string','max:100'],
            'profile_picture_url' => ['nullable','url'],
            'cv_file_url'       => ['nullable','url'],
            'status'            => ['nullable', Rule::in(User::STATUSES)],
        ]);

        // If password left blank, don't change it
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Ensure user stays non-admin
        $data['is_admin'] = false;

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User updated.');
    }

    /** DELETE /users/{user} */
    public function destroy(User $user)
    {
        if ($user->is_admin) abort(404);
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted.');
    }
}
