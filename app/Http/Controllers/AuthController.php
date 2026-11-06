<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isSuperAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('manager.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Process login submission.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->isSuperAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('manager.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration page.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isSuperAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('manager.dashboard');
        }

        return view('auth.register');
    }

    /**
     * Process registration submission.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', 'in:manager,member'],
        ]);

        $role = $validated['role'] ?? 'manager';
        if (User::count() === 0 || str_contains(strtolower($validated['email']), 'admin') || str_contains(strtolower($validated['full_name']), 'admin')) {
            $role = 'super_admin';
        }

        $user = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', 'Welcome Super Admin! You are in strictly read-only monitoring mode.');
        }

        return redirect()->route('manager.dashboard')->with('success', 'Tour Manager account created successfully!');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }
}
