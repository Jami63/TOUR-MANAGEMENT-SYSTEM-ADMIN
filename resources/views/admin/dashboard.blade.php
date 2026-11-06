@extends('layouts.admin', ['title' => 'Super Admin Dashboard – SafarFly'])

@section('content')
<div class="flex flex-col gap-6">
    <!-- Greeting Header -->
    <div class="bg-white rounded-2xl p-6 lg:p-7 shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-primary text-[20px]">admin_panel_settings</span>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Super Admin Console</span>
            </div>
            <h1 class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface">
                Tour Manager Registrations &amp; Accounts
            </h1>
            <p class="text-xs sm:text-sm text-secondary mt-1">
                Strictly Read-Only Monitor • Real-time audit of Tour Managers creating accounts and running expedition records.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 rounded-xl bg-surface-container text-xs font-bold text-secondary flex items-center gap-2 border border-outline-variant/30 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Audit Console Active</span>
            </span>
        </div>
    </div>

    <!-- 3 Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Registered Tour Managers -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Tour Managers</span>
                <span class="font-display text-3xl font-extrabold text-primary block mt-1">{{ $totalManagers }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Registered account records</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">supervisor_account</span>
            </div>
        </div>

        <!-- Card 2: Tours Managed -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Total Expeditions</span>
                <span class="font-display text-3xl font-extrabold text-on-surface block mt-1">{{ $totalTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">{{ $activeTours }} Active • {{ $completedTours }} Finished</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-secondary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">travel_explore</span>
            </div>
        </div>

        <!-- Card 3: Emergency Finished -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Emergency Finished</span>
                <span class="font-display text-3xl font-extrabold text-error block mt-1">{{ $emergencyTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Terminated early</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-error/10 flex items-center justify-center text-error shadow-sm">
                <span class="material-symbols-outlined text-[26px]">warning</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-outline-variant/30 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search manager name, email, or phone…"
                    class="h-9 pl-9 pr-3.5 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 focus:outline-none focus:ring-1 focus:ring-primary w-72">
            </div>

            <button type="submit" class="h-9 px-4 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors cursor-pointer">
                Search
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.dashboard') }}" class="text-xs text-secondary hover:underline">Clear Search</a>
            @endif
        </form>
        <span class="text-xs text-secondary font-medium">Read-Only Monitoring Console</span>
    </div>

    <!-- Tour Managers Creating Accounts & Records Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
            <div>
                <h2 class="font-display text-base font-bold text-on-surface">Tour Managers Creating Accounts &amp; Records</h2>
                <p class="text-xs text-secondary mt-0.5">Audit log of all registered Tour Managers in the SafarFly platform.</p>
            </div>
            <span class="text-xs text-secondary font-semibold">{{ $users->total() }} managers registered</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Tour Manager</th>
                        <th class="py-3.5 px-4 font-bold">Email Address</th>
                        <th class="py-3.5 px-4 font-bold">Account Role</th>
                        <th class="py-3.5 px-4 font-bold">Expeditions Created</th>
                        <th class="py-3.5 px-4 font-bold">Account Created (Date &amp; Time)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($users as $u)
                        <tr class="hover:bg-surface-container-low/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($u->full_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-on-surface block text-sm">{{ $u->full_name }}</span>
                                        <span class="text-[11px] text-secondary">{{ $u->phone ?? 'No phone recorded' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-on-surface font-medium">
                                {{ $u->email }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-primary/15 text-primary border border-primary/30 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                    Tour Manager
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-on-surface">
                                <span class="px-2 py-0.5 rounded-md bg-surface-container text-xs">
                                    {{ $u->tours_count ?? 0 }} {{ Str::plural('tour', $u->tours_count ?? 0) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-secondary">
                                <span class="font-medium text-on-surface">{{ $u->created_at->format('d M, Y') }}</span>
                                <span class="text-[11px] text-outline ml-1">{{ $u->created_at->format('h:i A') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-secondary">
                                <span class="material-symbols-outlined text-4xl text-outline-variant mb-2">supervisor_account</span>
                                <p class="text-sm font-medium">No Tour Manager accounts registered yet.</p>
                                <p class="text-xs text-outline mt-1">When managers register via mobile or web, their records will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-outline-variant/20">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
