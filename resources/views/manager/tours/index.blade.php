@extends('layouts.manager', ['title' => 'My Expeditions – SafarFly Tour Manager'])

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-6 lg:p-7 shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-primary text-[20px]">explore</span>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Tour Manager Workspace</span>
            </div>
            <h1 class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface">
                My Expeditions
            </h1>
            <p class="text-xs sm:text-sm text-secondary mt-1">
                Manage your tours, track equal expense shares, record member payments, and process final settlements.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.tours.create') }}" class="px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-all flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Create New Tour</span>
            </a>
        </div>
    </div>

    <!-- 3 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-4">
        <!-- Total Tours -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Total Tours</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface block mt-1">{{ $totalTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Expeditions created</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">luggage</span>
            </div>
        </div>

        <!-- Active Tours -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Active Tours</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-primary block mt-1">{{ $activeTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Upcoming &amp; Live</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">timer</span>
            </div>
        </div>

        <!-- Completed Tours -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Finished Tours</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-emerald-700 block mt-1">{{ $completedTours }}</span>
                <span class="text-[11px] text-outline mt-0.5 block">Completed &amp; settled</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                <span class="material-symbols-outlined text-[26px]">task_alt</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl border border-outline-variant/30 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <form method="GET" action="{{ route('manager.dashboard') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tour name or location…"
                    class="h-9 pl-9 pr-3.5 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 focus:outline-none focus:ring-1 focus:ring-primary w-64">
            </div>

            <select name="status" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 text-secondary focus:outline-none">
                <option value="">All Statuses</option>
                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Settled)</option>
            </select>

            <button type="submit" class="h-9 px-3.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('manager.dashboard') }}" class="text-xs text-secondary hover:underline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Tours List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($tours as $tour)
            <div class="bg-white rounded-2xl border border-outline-variant/30 p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                <div>
                    <!-- Top Status Row -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        @if ($tour->status === 'completed')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1 border border-emerald-200">
                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                Finished
                            </span>
                        @elseif ($tour->status === 'ongoing')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 flex items-center gap-1 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                Live Now
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                Upcoming
                            </span>
                        @endif

                        <span class="text-[11px] font-semibold text-secondary">
                            ৳{{ number_format($tour->estimated_budget, 2) }}
                        </span>
                    </div>

                    <!-- Tour Name & Route -->
                    <h3 class="font-display text-lg font-bold text-on-surface hover:text-primary transition-colors">
                        {{ $tour->tour_name }}
                    </h3>

                    <!-- Destination Route -->
                    <div class="mt-2 text-xs text-secondary flex items-center gap-1.5 flex-wrap">
                        <span class="material-symbols-outlined text-[16px] text-primary">route</span>
                        <span class="font-medium text-on-surface">{{ $tour->start_location }}</span>
                        @foreach ($tour->destinations as $dest)
                            <span class="text-outline">→</span>
                            <span class="font-medium text-on-surface">{{ $dest->destination_name }}</span>
                        @endforeach
                    </div>

                    <!-- Dates & Members Count -->
                    <div class="mt-4 pt-3 border-t border-outline-variant/20 flex items-center justify-between text-xs text-secondary">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-outline">calendar_month</span>
                            <span>{{ $tour->start_date->format('M d') }} - {{ $tour->end_date->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-outline">group</span>
                            <span>{{ $tour->members->count() }} Members</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="mt-5 pt-3 border-t border-outline-variant/20 flex items-center justify-between">
                    <span class="text-[11px] text-outline">
                        Expenses: ৳{{ number_format($tour->totalActualExpenses(), 2) }}
                    </span>
                    <a href="{{ route('manager.tours.show', $tour->id) }}" class="px-3.5 py-1.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors flex items-center gap-1.5 shadow-sm">
                        <span>Open Workspace</span>
                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-outline-variant/30">
                <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center text-primary mx-auto mb-3">
                    <span class="material-symbols-outlined text-[32px]">luggage</span>
                </div>
                <h3 class="font-display text-lg font-bold text-on-surface">No Tours Found</h3>
                <p class="text-xs text-secondary mt-1 max-w-md mx-auto">
                    You haven't created any tours matching your criteria yet. Start by creating a new expedition!
                </p>
                <div class="mt-5">
                    <a href="{{ route('manager.tours.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        <span>Create Tour</span>
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($tours->hasPages())
        <div class="bg-white p-4 rounded-xl border border-outline-variant/30 flex items-center justify-center shadow-sm">
            {{ $tours->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
