@extends('layouts.auth', ['title' => 'Login – SafarFlow Tour Management'])

@section('content')
<div class="w-full max-w-[460px] p-6 sm:p-8 bg-white rounded-[24px] shadow-xl shadow-[#0B1F3A]/5 border border-outline-variant/30">
    <!-- Brand Icon & Header -->
    <div class="mb-6">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center mb-3 text-primary">
            <span class="material-symbols-outlined text-[26px]">near_me</span>
        </div>
        <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-surface-container text-on-surface-variant text-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
            <span class="font-medium">Expedition Ledger Access</span>
        </div>
        <h1 class="font-display text-2xl sm:text-3xl text-on-surface font-extrabold tracking-tight">Welcome back</h1>
        <p class="text-sm text-secondary mt-1">Sign in to your tour management console.</p>
    </div>

    <!-- Error Alert Banner -->
    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-[#FBE7EA] text-[#93000A] flex items-start gap-3 border border-error/30 text-sm">
            <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">error</span>
            <div>
                <p class="font-bold">Authentication failed</p>
                <p class="text-xs mt-0.5">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="mb-5 p-3.5 rounded-xl bg-blue-50 text-blue-800 flex items-center gap-2 text-sm border border-blue-200">
            <span class="material-symbols-outlined text-[20px]">info</span>
            <span>{{ session('info') }}</span>
        </div>
    @endif

    <!-- Super Admin Quick-Fill Pill -->
    <div class="mb-5 p-3 rounded-xl bg-surface-container-low border border-primary/20 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-primary block">Super Admin Login:</span>
            <span class="text-[11px] text-secondary">admin@safarflow.test / password</span>
        </div>
        <button type="button" onclick="fillDemo('admin@safarflow.test', 'password')" class="px-2.5 py-1 text-xs font-semibold bg-primary text-white rounded-lg hover:bg-primary-container transition-colors shadow-sm">
            Auto-fill
        </button>
    </div>

    <!-- Login Form -->
    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Email Field -->
        <div class="space-y-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="email">
                Email Address
            </label>
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none">
                    mail
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com"
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 shadow-sm">
            </div>
        </div>

        <!-- Password Field -->
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="password">
                    Password
                </label>
            </div>
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none">
                    lock
                </span>
                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••"
                    class="w-full h-12 pl-11 pr-12 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 shadow-sm">
                <button type="button" onclick="togglePassword()" class="absolute right-3 text-outline hover:text-on-surface p-1">
                    <span class="material-symbols-outlined text-[20px]" id="eye-icon">visibility</span>
                </button>
            </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center gap-2 cursor-pointer text-secondary">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded text-primary focus:ring-primary/20 border-outline-variant">
                <span>Remember me for 30 days</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full h-[52px] rounded-xl font-bold tracking-wide flex items-center justify-center gap-2 bg-[#008373] text-white shadow-md shadow-primary/20 hover:bg-[#00685b] transition-all cursor-pointer">
                <span>Login to Dashboard</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
        </div>
    </form>

    <!-- Sign Up Redirect -->
    <div class="mt-6 pt-5 border-t border-outline-variant/30 text-center text-xs text-secondary">
        <span>Don't have an account yet?</span>
        <a href="{{ route('register') }}" class="font-bold text-primary hover:underline ml-1">Create an account</a>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }

    function fillDemo(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
@endsection
