@extends('layouts.auth', ['title' => 'Verify Email – SafarFly Tour Management'])

@section('content')
<div class="w-full max-w-[480px] p-6 sm:p-8 bg-white rounded-[24px] shadow-xl shadow-[#0B1F3A]/5 border border-outline-variant/30">
    <!-- Brand Icon & Header -->
    <div class="mb-6 text-center sm:text-left">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center mb-3 text-primary mx-auto sm:mx-0">
            <span class="material-symbols-outlined text-[26px]">mark_email_read</span>
        </div>
        <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-surface-container text-on-surface-variant text-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
            <span class="font-medium">Two-Step Email Authentication</span>
        </div>
        <h1 class="font-display text-2xl sm:text-3xl text-on-surface font-extrabold tracking-tight">Enter Verification Code</h1>
        <p class="text-sm text-secondary mt-1">
            We sent a 6-digit OTP code to <strong class="text-on-surface">{{ $email }}</strong>. Please enter the code to verify your email and activate your account.
        </p>
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="mb-5 p-3.5 rounded-xl bg-teal-50 text-teal-900 flex items-start gap-2.5 text-xs sm:text-sm border border-teal-200">
            <span class="material-symbols-outlined text-teal-700 text-[20px] shrink-0 mt-0.5">check_circle</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <!-- Error Alert Banner -->
    @if ($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-[#FBE7EA] text-[#93000A] flex items-start gap-3 border border-error/30 text-sm">
            <span class="material-symbols-outlined text-error text-[20px] shrink-0 mt-0.5">error</span>
            <div>
                <p class="font-bold">Verification failed</p>
                <p class="text-xs mt-0.5">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <!-- OTP Verification Form -->
    <form action="{{ route('register.otp.verify') }}" method="POST" class="space-y-5">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="space-y-2">
            <label class="text-xs font-semibold uppercase tracking-wider text-on-surface block text-center sm:text-left" for="otp">
                6-Digit Security Code
            </label>
            <div class="relative flex items-center justify-center">
                <input id="otp" name="otp" type="text" maxlength="6" pattern="[0-9]{6}" required autofocus
                    placeholder="• • • • • •"
                    class="w-full h-14 text-center tracking-[10px] sm:tracking-[14px] font-mono font-bold text-2xl rounded-xl bg-surface-container-low text-on-surface outline-none transition-all border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 shadow-sm">
            </div>
            <p class="text-[11px] text-secondary text-center">Enter the numbers received in your Gmail inbox or spam folder.</p>
        </div>

        <button type="submit"
            class="w-full h-12 bg-primary hover:bg-primary-hover active:bg-primary text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-primary/25 flex items-center justify-center gap-2">
            <span>Verify & Activate Account</span>
            <span class="material-symbols-outlined text-[18px]">verified_user</span>
        </button>
    </form>

    <!-- Resend & Change Email Actions -->
    <div class="mt-6 pt-5 border-t border-outline-variant/30 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <form action="{{ route('register.otp.resend') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button type="submit" class="text-primary hover:text-primary-hover font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">refresh</span>
                <span>Resend Code</span>
            </button>
        </form>

        <a href="{{ route('register') }}" class="text-secondary hover:text-on-surface font-medium flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Change Email Address</span>
        </a>
    </div>
</div>
@endsection
