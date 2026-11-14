<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
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
     * Process registration submission - Step 1: Validate & send OTP to Gmail.
     */
    public function register(Request $request, OtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', 'in:manager,member'],
        ]);

        // Web registration defaults to manager; bootstrap super_admin only if no super_admin exists yet
        $role = User::where('role', 'super_admin')->count() === 0 ? 'super_admin' : 'manager';

        $res = $otpService->generateAndSendOtp($validated['email'], [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        if (! $res['success']) {
            return back()->withErrors(['email' => $res['message']])->withInput();
        }

        session([
            'pending_otp_email' => $validated['email'],
            'pending_otp_name' => $validated['full_name'],
        ]);

        return redirect()->route('register.otp')->with('success', 'A 6-digit verification code has been sent to ' . $validated['email'] . '. Please enter it below to activate your account.');
    }

    /**
     * Show OTP Verification screen.
     */
    public function showVerifyOtp(Request $request): View|RedirectResponse
    {
        $email = session('pending_otp_email');
        if (! $email) {
            return redirect()->route('register')->with('warning', 'Please submit your registration details first.');
        }

        return view('auth.verify_otp', [
            'email' => $email,
            'fullName' => session('pending_otp_name', 'User'),
        ]);
    }

    /**
     * Process OTP verification - Step 2: Validate code & activate account.
     */
    public function verifyOtp(Request $request, OtpService $otpService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $payload = $otpService->verifyOtp($validated['email'], $validated['otp']);

        if (! $payload) {
            return back()->withErrors(['otp' => 'Invalid or expired 6-digit verification code. Please check your Gmail or request a new code.'])->withInput();
        }

        $existing = User::where('email', $payload['email'])->first();
        if ($existing) {
            Auth::login($existing);
            session()->forget(['pending_otp_email', 'pending_otp_name']);
            return redirect()->route($existing->isSuperAdmin() ? 'admin.dashboard' : 'manager.dashboard')->with('success', 'Account verified! Welcome back.');
        }

        $user = User::create([
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => $payload['password_hash'],
            'role' => $payload['role'] ?? 'manager',
            'email_verified_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        session()->forget(['pending_otp_email', 'pending_otp_name']);

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', 'Super Admin account verified & created successfully!');
        }

        return redirect()->route('manager.dashboard')->with('success', 'Tour Manager account verified & created successfully!');
    }

    /**
     * Resend fresh OTP.
     */
    public function resendOtp(Request $request, OtpService $otpService): RedirectResponse
    {
        $email = $request->input('email') ?? session('pending_otp_email');
        if (! $email) {
            return redirect()->route('register')->with('error', 'Session expired. Please register again.');
        }

        $res = $otpService->resendOtp($email);

        if (! $res['success']) {
            return back()->with('error', $res['message']);
        }

        return back()->with('success', 'A new 6-digit verification code was sent to ' . $email . '. Please check your inbox.');
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
