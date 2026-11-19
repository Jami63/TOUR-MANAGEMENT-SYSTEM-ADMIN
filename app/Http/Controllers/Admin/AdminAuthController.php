<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    /**
     * Show the dedicated Super Admin login page.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->isSuperAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('manager.dashboard')->with('error', 'Tour Managers do not have administrative terminal access.');
        }

        return view('admin.auth.login');
    }

    /**
     * Process Super Admin login submission with strict role enforcement.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Strict Role Gate: Only Super Administrators are permitted through this terminal
            if (! $user->isSuperAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Log::warning('Unauthorized Super Admin portal login attempt intercepted.', [
                    'email' => $credentials['email'],
                    'role' => $user->role,
                    'ip' => $request->ip(),
                ]);

                return redirect()->route('admin.login')->withErrors([
                    'email' => 'Access Denied: This administrative terminal is strictly restricted to SafarFly Super Administrators. Tour Managers must authenticate via the public portal.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            Log::info('Super Admin authenticated successfully via Admin Terminal.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome to the SafarFly Super Admin Security Terminal.');
        }

        return back()->withErrors([
            'email' => 'Invalid administrative credentials provided.',
        ])->onlyInput('email');
    }

    /**
     * Log the Super Admin out and return to the dedicated admin login portal.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('info', 'You have been securely logged out of the Super Admin terminal.');
    }
}
