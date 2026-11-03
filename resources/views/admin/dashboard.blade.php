@extends('layouts.admin', ['title' => 'Super Admin Dashboard – SafarFlow'])

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
                User Registrations &amp; Accounts
            </h1>
            <p class="text-xs sm:text-sm text-secondary mt-1">
                Live monitoring of everyone who creates an account on SafarFlow as a Tour Manager or Tour Member.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 rounded-xl bg-surface-container text-xs font-bold text-secondary flex items-center gap-2 border border-outline-variant/30 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Platform Live</span>
            </span>
        </div>
    </div>

    <!-- 3 Summary Cards: Total Accounts, Tour Managers, Tour Members -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Users Registered -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Total Accounts</span>
                <span class="font-display text-3xl font-extrabold text-on-surface block mt-1">{{ $totalUsers }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">All registered people</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">groups</span>
            </div>
        </div>

        <!-- Card 2: Tour Managers -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Tour Managers</span>
                <span class="font-display text-3xl font-extrabold text-primary block mt-1">{{ $totalManagers }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Creating &amp; running tours</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">supervisor_account</span>
            </div>
        </div>

        <!-- Card 3: Tour Members -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Tour Members</span>
                <span class="font-display text-3xl font-extrabold text-[#4d5f7d] block mt-1">{{ $totalMembers }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Joined as participants</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-secondary-container/40 flex items-center justify-center text-secondary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">person</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-xl border border-outline-variant/30 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, or phone…"
                    class="h-9 pl-9 pr-3.5 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 focus:outline-none focus:ring-1 focus:ring-primary w-64">
            </div>

            <select name="role" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 text-secondary focus:outline-none">
                <option value="">All Roles</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Tour Managers</option>
                <option value="member" {{ request('role') === 'member' ? 'selected' : '' }}>Tour Members</option>
            </select>

            <button type="submit" class="h-9 px-3.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.dashboard') }}" class="text-xs text-secondary hover:underline">Clear Filter</a>
            @endif
        </form>
    </div>

    <!-- Who is Creating Account (Table) -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
            <h2 class="font-display text-base font-bold text-on-surface">Who Is Creating Accounts</h2>
            <span class="text-xs text-secondary">{{ $users->total() }} accounts registered</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">User Name</th>
                        <th class="py-3.5 px-4 font-bold">Email</th>
                        <th class="py-3.5 px-4 font-bold">Phone</th>
                        <th class="py-3.5 px-4 font-bold">Account Role</th>
                        <th class="py-3.5 px-4 font-bold">Created At (Date &amp; Time)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20">
                    @forelse($users as $u)
                        <tr class="hover:bg-surface-container-low/40 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($u->full_name, 0, 2)) }}
                                    </div>
                                    <span class="font-bold text-on-surface">{{ $u->full_name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-on-surface font-medium">
                                {{ $u->email }}
                            </td>
                            <td class="py-3.5 px-4 text-secondary">
                                {{ $u->phone ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->role === 'manager')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-primary/15 text-primary border border-primary/30 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Tour Manager
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-secondary-container text-on-secondary-fixed border border-secondary/20 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                        Tour Member
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-secondary">
                                <span class="font-medium text-on-surface">{{ $u->created_at->format('d M, Y') }}</span>
                                <span class="text-[10px] text-outline ml-1">{{ $u->created_at->format('h:i A') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-secondary">
                                <span class="material-symbols-outlined text-4xl text-outline-variant mb-2">person_off</span>
                                <p class="text-sm font-medium">No accounts registered yet.</p>
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
