@extends('layouts.manager', ['title' => 'Create New Expedition – SafarFlow'])

@section('content')
<div class="max-w-[760px] mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('manager.dashboard') }}" class="text-xs font-semibold text-secondary hover:text-primary flex items-center gap-1 mb-2">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Back to Expeditions</span>
            </a>
            <h1 class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface">
                Create New Expedition
            </h1>
            <p class="text-xs sm:text-sm text-secondary mt-1">
                Configure your tour route, budget, and schedule. You will automatically be added as the primary tour participant.
            </p>
        </div>
    </div>

    <!-- Error Alerts -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm">
            <div class="flex items-center gap-2 font-bold mb-1">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <span>Please fix the following issues:</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-outline-variant/30 p-6 sm:p-8 shadow-sm">
        <form action="{{ route('manager.tours.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Tour Name -->
            <div class="space-y-1.5">
                <label for="tour_name" class="text-xs font-bold uppercase tracking-wider text-on-surface flex items-center justify-between">
                    <span>Tour / Expedition Name *</span>
                    <span class="text-[10px] text-outline font-normal">e.g. Cox's Bazar Beach Trip</span>
                </label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none">
                        flight_takeoff
                    </span>
                    <input type="text" id="tour_name" name="tour_name" value="{{ old('tour_name') }}" required
                        placeholder="e.g. Cox's Bazar"
                        class="w-full h-11 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all">
                </div>
            </div>

            <!-- Route & Destinations -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Start Location -->
                <div class="space-y-1.5">
                    <label for="start_location" class="text-xs font-bold uppercase tracking-wider text-on-surface">
                        Start Location / Origin *
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none">
                            location_on
                        </span>
                        <input type="text" id="start_location" name="start_location" value="{{ old('start_location', 'Dhaka') }}" required
                            placeholder="e.g. Dhaka"
                            class="w-full h-11 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                </div>

                <!-- Budget -->
                <div class="space-y-1.5">
                    <label for="estimated_budget" class="text-xs font-bold uppercase tracking-wider text-on-surface">
                        Estimated Budget (৳ BDT) *
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none">
                            payments
                        </span>
                        <input type="number" step="0.01" min="0" id="estimated_budget" name="estimated_budget" value="{{ old('estimated_budget', '200000.00') }}" required
                            placeholder="e.g. 200000"
                            class="w-full h-11 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                </div>
            </div>

            <!-- Destinations List -->
            <div class="space-y-1.5">
                <label for="destinations" class="text-xs font-bold uppercase tracking-wider text-on-surface flex items-center justify-between">
                    <span>Destinations Along Route *</span>
                    <span class="text-[10px] text-outline font-normal">Comma-separated</span>
                </label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3.5 text-[20px] text-outline pointer-events-none">
                        alt_route
                    </span>
                    <input type="text" id="destinations" name="destinations" value="{{ old('destinations') }}" required
                        placeholder="e.g. Cox's Bazar, Kolatoli Beach, Inani, Marine Drive"
                        class="w-full h-11 pl-11 pr-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all">
                </div>
            </div>

            <!-- Tour Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="start_date" class="text-xs font-bold uppercase tracking-wider text-on-surface">
                        Start Date *
                    </label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" required
                        class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all">
                </div>

                <div class="space-y-1.5">
                    <label for="end_date" class="text-xs font-bold uppercase tracking-wider text-on-surface">
                        End Date *
                    </label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', now()->addDays(4)->format('Y-m-d')) }}" required
                        class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all">
                </div>
            </div>

            <!-- Description / Notes -->
            <div class="space-y-1.5">
                <label for="description" class="text-xs font-bold uppercase tracking-wider text-on-surface">
                    Description &amp; Expedition Notes
                </label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Briefly describe itinerary, hotel bookings, or instructions for the tour members…"
                    class="w-full p-3.5 rounded-xl bg-surface-container-low text-on-surface text-sm outline-none border border-outline-variant/40 focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all">{{ old('description') }}</textarea>
            </div>

            <!-- Manager Participant Notice Pill (Requirement 2) -->
            <div class="p-3.5 rounded-xl bg-surface-container-low border border-primary/20 flex items-start gap-3">
                <span class="material-symbols-outlined text-primary text-[20px] shrink-0 mt-0.5">verified_user</span>
                <div class="text-xs text-secondary leading-relaxed">
                    <span class="font-bold text-on-surface block">Automated Participant Inclusion:</span>
                    As Tour Manager, you (<span class="font-semibold text-primary">{{ Auth::user()->full_name }}</span>) will automatically be enrolled as Member #1 in this expedition. Every expense you add will be divided equally among all members including you.
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2 flex items-center justify-end gap-3">
                <a href="{{ route('manager.dashboard') }}" class="px-5 py-2.5 rounded-xl text-xs font-bold text-secondary hover:bg-surface-container transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                    <span>Create Expedition</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
