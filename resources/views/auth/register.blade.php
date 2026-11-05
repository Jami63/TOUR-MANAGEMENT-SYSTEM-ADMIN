@extends('layouts.auth', ['title' => 'Create Account – SafarFly Tour Management'])

@section('content')
<div class="w-full max-w-[480px] p-6 sm:p-8 bg-white rounded-[24px] shadow-xl shadow-[#0B1F3A]/5 border border-outline-variant/30">
    <!-- Header -->
    <header class="mb-6">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center mb-3 text-primary">
            <span class="material-symbols-outlined text-[26px]">person_add</span>
        </div>
        <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-on-surface tracking-tight">Create your account</h2>
        <p class="text-sm text-secondary mt-1">Start organizing group tours and managing split expenses.</p>
    </header>

    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-[#FBE7EA] text-[#93000A] flex items-start gap-3 border border-error/30 text-sm">
            <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">error</span>
            <div>
                <p class="font-bold">Please correct the errors below:</p>
                <ul class="text-xs list-disc list-inside mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf

        <!-- Full Name -->
        <div class="space-y-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="full_name">
                Full Name
            </label>
            <div class="relative flex items-center">
                <span class="absolute left-3.5 material-symbols-outlined text-outline text-[20px] pointer-events-none">
                    person
                </span>
                <input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" required placeholder="e.g. Nusrat Jahan"
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 shadow-sm">
            </div>
        </div>

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="email">
                Email Address
            </label>
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3.5 material-symbols-outlined text-outline text-[20px] pointer-events-none">
                    mail
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="e.g. nusrat@example.com"
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 shadow-sm">
            </div>
        </div>

        <!-- Phone Number -->
        <div class="space-y-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="phone">
                Phone Number (for bKash / Nagad verification)
            </label>
            <div class="relative flex items-center">
                <span class="absolute left-3.5 material-symbols-outlined text-outline text-[20px] pointer-events-none">
                    call
                </span>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="01712-345678"
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 shadow-sm">
            </div>
        </div>

        <!-- Role Selection -->
        <div class="space-y-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface">
                Account Role
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 p-3 rounded-xl border border-outline-variant/50 cursor-pointer hover:bg-surface-container-low transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                    <input type="radio" name="role" value="manager" checked class="text-primary focus:ring-primary">
                    <div>
                        <span class="text-xs font-bold block text-on-surface">Tour Manager</span>
                        <span class="text-[10px] text-secondary">Organizes tours & expenses</span>
                    </div>
                </label>
                <label class="flex items-center gap-2 p-3 rounded-xl border border-outline-variant/50 cursor-pointer hover:bg-surface-container-low transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                    <input type="radio" name="role" value="member" class="text-primary focus:ring-primary">
                    <div>
                        <span class="text-xs font-bold block text-on-surface">Tour Member</span>
                        <span class="text-[10px] text-secondary">Participates & pays shares</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="password">
                Password
            </label>
            <div class="relative flex items-center">
                <span class="absolute left-3.5 material-symbols-outlined text-outline text-[20px] pointer-events-none">
                    lock
                </span>
                <input id="password" name="password" type="password" required placeholder="At least 6 characters"
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 shadow-sm">
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="space-y-1.5">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface" for="password_confirmation">
                Confirm Password
            </label>
            <div class="relative flex items-center">
                <span class="absolute left-3.5 material-symbols-outlined text-outline text-[20px] pointer-events-none">
                    lock_clock
                </span>
                <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Repeat your password"
                    class="w-full h-12 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none transition-all border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 shadow-sm">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="w-full h-[52px] rounded-xl font-bold tracking-wide flex items-center justify-center gap-2 bg-[#008373] text-white shadow-md shadow-primary/20 hover:bg-[#00685b] transition-all cursor-pointer">
                <span>Create SafarFly Account</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>
        </div>
    </form>

    <div class="mt-6 pt-5 border-t border-outline-variant/30 text-center text-xs text-secondary">
        <span>Already have an account?</span>
        <a href="{{ route('login') }}" class="font-bold text-primary hover:underline ml-1">Sign In</a>
    </div>
</div>
@endsection
