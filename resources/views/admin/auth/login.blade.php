@extends('layouts.auth', ['title' => 'Super Admin Terminal – SafarFly'])

@section('content')
<div class="w-full max-w-[480px] p-6 sm:p-8 bg-white rounded-[24px] shadow-2xl shadow-[#071c36]/10 border border-[#071c36]/15 relative overflow-hidden">
    <!-- Top Security Stripe -->
    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#071c36] via-[#00685b] to-[#ba1a1a]"></div>

    <!-- Administrative Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 rounded-2xl bg-[#071c36] flex items-center justify-center text-white shadow-md shadow-[#071c36]/20">
                <span class="material-symbols-outlined text-[26px]">shield_person</span>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 text-xs font-semibold tracking-wide">
                <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                <span>Restricted Portal</span>
            </div>
        </div>

        <h1 class="font-display text-2xl sm:text-3xl text-on-surface font-extrabold tracking-tight">
            Admin Terminal
        </h1>
        <p class="text-xs text-secondary mt-1">
            Super Administrator authentication & centralized audit console.
        </p>
    </div>

    <!-- Security Warning Disclaimer -->
    <div class="mb-5 p-3.5 rounded-xl bg-[#071c36]/5 border border-[#071c36]/10 text-[#071c36] flex items-start gap-2.5 text-xs">
        <span class="material-symbols-outlined text-[#071c36] text-[18px] shrink-0 mt-0.5">verified_user</span>
        <div>
            <span class="font-bold">Authorized Personnel Only.</span>
            <span>All login attempts are recorded with timestamp and IP address. Tour managers must access the public management portal.</span>
        </div>
    </div>

    <!-- Error Alert Banner -->
    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-[#FBE7EA] text-[#93000A] flex items-start gap-3 border border-error/30 text-sm">
            <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">lock</span>
            <div>
                <p class="font-bold">Access Denied</p>
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

    <!-- Admin Login Form -->
    <form action="{{ route('admin.login') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Email Field -->
        <div class="space-y-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="admin-email">
                Admin Email Address
            </label>
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none">
                    admin_panel_settings
                </span>
                <input id="admin-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="admin@safarfly.com"
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-[#071c36] focus:bg-white focus:ring-2 focus:ring-[#071c36]/20 shadow-sm">
            </div>
        </div>

        <!-- Password Field -->
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="admin-password">
                    Master Password
                </label>
            </div>
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none">
                    key
                </span>
                <input id="admin-password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••"
                    class="w-full h-12 pl-11 pr-12 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-[#071c36] focus:bg-white focus:ring-2 focus:ring-[#071c36]/20 shadow-sm">
                <button type="button" onclick="toggleAdminPassword()" class="absolute right-3 text-outline hover:text-on-surface p-1">
                    <span class="material-symbols-outlined text-[20px]" id="admin-eye-icon">visibility</span>
                </button>
            </div>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center gap-2 cursor-pointer text-secondary">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#071c36] focus:ring-[#071c36]/20 border-outline-variant">
                <span>Maintain secure administrative session</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full h-[52px] rounded-xl font-bold tracking-wide flex items-center justify-center gap-2 bg-[#071c36] text-white shadow-md shadow-[#071c36]/20 hover:bg-[#0b284c] transition-all cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">verified</span>
                <span>Authenticate & Access Console</span>
            </button>
        </div>
    </form>

    <!-- Public Portal Switcher -->
    <div class="mt-6 pt-5 border-t border-outline-variant/30 text-center text-xs text-secondary flex items-center justify-center gap-1.5">
        <span>Not an administrator?</span>
        <a href="{{ route('login') }}" class="font-bold text-primary hover:underline inline-flex items-center gap-1">
            <span>Go to Tour Manager Portal</span>
            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
        </a>
    </div>
</div>

<script>
    function toggleAdminPassword() {
        const input = document.getElementById('admin-password');
        const icon = document.getElementById('admin-eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerText = 'visibility_off';
        } else {
            input.type = 'password';
            icon.innerText = 'visibility';
        }
    }
</script>
@endsection
