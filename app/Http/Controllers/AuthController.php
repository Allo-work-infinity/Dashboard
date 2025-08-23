<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /* ---------- Register ---------- */

    public function showRegisterForm()
    {
        return view('auth.register');
    }

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

        // If your User model has 'password' => 'hashed' cast, this plain assignment is enough.
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/')->with('success', 'Welcome!');
    }

    /* ---------- Login / Logout ---------- */

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Signed in.');
        }

        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Signed out.');
    }
}
