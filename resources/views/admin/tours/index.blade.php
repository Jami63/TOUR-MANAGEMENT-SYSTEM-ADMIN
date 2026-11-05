@extends('layouts.admin', ['title' => 'All Tours Monitoring – SafarFlow Super Admin'])

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-6 lg:p-7 shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-primary text-[20px]">travel_explore</span>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Audit &amp; Oversight</span>
            </div>
            <h1 class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface">
                All Tours Monitoring
            </h1>
            <p class="text-xs sm:text-sm text-secondary mt-1">
                Comprehensive read-only monitoring of all tours, member splits, manual payments, and financial settlements across all Tour Managers.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 rounded-xl bg-amber-50 text-xs font-bold text-amber-800 flex items-center gap-2 border border-amber-200 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-amber-600">visibility</span>
                <span>Strictly Read-Only Monitor</span>
            </span>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Tours -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Total Tours</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface block mt-1">{{ $totalTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">All platform tours</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">travel_explore</span>
            </div>
        </div>

        <!-- Active Tours -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Active Tours</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-primary block mt-1">{{ $activeTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Upcoming &amp; Ongoing</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">pending_actions</span>
            </div>
        </div>

        <!-- Completed Tours -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Settled Tours</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-emerald-700 block mt-1">{{ $completedTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Normal finish &amp; settled</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                <span class="material-symbols-outlined text-[26px]">task_alt</span>
            </div>
        </div>

        <!-- Emergency Finished -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Emergency Finished</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-red-600 block mt-1">{{ $emergencyTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Terminated early</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 shadow-sm">
                <span class="material-symbols-outlined text-[26px]">emergency</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl border border-outline-variant/30 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <form method="GET" action="{{ route('admin.tours.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tour name, location, or manager…"
                    class="h-9 pl-9 pr-3.5 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 focus:outline-none focus:ring-1 focus:ring-primary w-64">
            </div>

            <select name="status" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 text-secondary focus:outline-none">
                <option value="">All Statuses</option>
                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Normal)</option>
                <option value="emergency_finished" {{ request('status') === 'emergency_finished' ? 'selected' : '' }}>Emergency Finished</option>
            </select>

            <select name="manager_id" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 text-secondary focus:outline-none">
                <option value="">All Tour Managers</option>
                @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" {{ (string)request('manager_id') === (string)$manager->id ? 'selected' : '' }}>
                        {{ $manager->full_name }} ({{ $manager->email }})
                    </option>
                @endforeach
            </select>

            <button type="submit" class="h-9 px-3.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'manager_id']))
                <a href="{{ route('admin.tours.index') }}" class="text-xs text-secondary hover:underline">Clear Filter</a>
            @endif
        </form>
    </div>

    <!-- Tours List -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
            <h2 class="font-display text-base font-bold text-on-surface">Platform Tours Overview</h2>
            <span class="text-xs text-secondary">{{ $tours->total() }} total tours found</span>
        </div>

        @if($tours->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="py-3 px-4">Tour Name &amp; Route</th>
                            <th class="py-3 px-4">Tour Manager</th>
                            <th class="py-3 px-4">Dates</th>
                            <th class="py-3 px-4">Members</th>
                            <th class="py-3 px-4 text-right">Budget</th>
                            <th class="py-3 px-4 text-right">Actual Expense</th>
                            <th class="py-3 px-4 text-right">Collected</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach($tours as $tour)
                            @php
                                $destinationNames = $tour->destinations->pluck('destination_name')->filter()->implode(', ');
                                $routeText = $tour->start_location . ($destinationNames ? ' → ' . $destinationNames : '');
                            @endphp
                            <tr class="hover:bg-surface-container-low/40 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-on-surface text-sm">{{ $tour->tour_name }}</div>
                                    <div class="text-[11px] text-secondary flex items-center gap-1 mt-0.5">
                                        <span class="material-symbols-outlined text-[14px] text-primary">near_me</span>
                                        <span>{{ $routeText ?: 'No route specified' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-on-surface">{{ $tour->creator?->full_name ?? 'N/A' }}</div>
                                    <div class="text-[11px] text-secondary">{{ $tour->creator?->email ?? '' }}</div>
                                </td>
                                <td class="py-3 px-4 text-secondary whitespace-nowrap">
                                    <div>{{ $tour->start_date ? \Carbon\Carbon::parse($tour->start_date)->format('d M Y') : 'TBD' }}</div>
                                    <div class="text-[10px] text-outline">to {{ $tour->end_date ? \Carbon\Carbon::parse($tour->end_date)->format('d M Y') : 'TBD' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-surface-container font-semibold text-secondary text-[11px]">
                                        <span class="material-symbols-outlined text-[13px]">groups</span>
                                        {{ $tour->members->count() }} members
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-on-surface">
                                    ৳{{ number_format($tour->total_budget, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-primary">
                                    ৳{{ number_format($tour->total_actual_expense ?? $tour->totalActualExpenses(), 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700">
                                    ৳{{ number_format($tour->total_collected ?? $tour->totalCollectedMoney(), 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($tour->isEmergencyFinished())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-100 text-red-800 font-bold text-[10px] uppercase tracking-wide border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                            Emergency Finished
                                        </span>
                                    @elseif($tour->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 font-bold text-[10px] uppercase tracking-wide border border-purple-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                            Finished (Settled)
                                        </span>
                                    @elseif($tour->status === 'ongoing')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase tracking-wide border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Ongoing
                                        </span>
                                    @elseif($tour->status === 'upcoming')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-bold text-[10px] uppercase tracking-wide border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                            Upcoming
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 font-bold text-[10px] uppercase tracking-wide border border-gray-200">
                                            {{ ucfirst($tour->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('admin.tours.show', $tour->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-surface-container text-primary font-bold text-xs hover:bg-primary hover:text-white transition-all shadow-sm border border-outline-variant/30">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        <span>Inspect</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($tours->hasPages())
                <div class="p-4 border-t border-outline-variant/20">
                    {{ $tours->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <span class="material-symbols-outlined text-outline text-[48px] block mb-2">travel_explore</span>
                <p class="text-sm font-bold text-on-surface">No tours match your filter</p>
                <p class="text-xs text-secondary mt-1">Try clearing your search terms or filters.</p>
            </div>
        @endif
    </div>
</div>
@endsection
