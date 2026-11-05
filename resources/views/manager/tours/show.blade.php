@extends('layouts.manager', ['title' => $tour->tour_name . ' – Expedition Workspace'])

@section('content')
<div class="flex flex-col gap-6" x-data="{ activeTab: 'overview' }">
    <!-- Top Back Link & Status Row -->
    <div class="flex items-center justify-between">
        <a href="{{ route('manager.dashboard') }}" class="text-xs font-semibold text-secondary hover:text-primary flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Back to All Expeditions</span>
        </a>
        <div class="flex items-center gap-2">
            @if ($tour->isEmergencyFinished())
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 flex items-center gap-1.5 border border-red-200">
                    <span class="material-symbols-outlined text-[16px]">warning</span>
                    <span>Emergency Finished</span>
                </span>
            @elseif ($tour->status === 'completed')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1.5 border border-emerald-200">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    <span>Tour Completed &amp; Settled</span>
                </span>
            @elseif ($tour->status === 'ongoing')
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 flex items-center gap-1.5 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-600 animate-pulse"></span>
                    <span>Live Expedition</span>
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                    Upcoming
                </span>
            @endif
        </div>
    </div>

    <!-- Tour Hero Banner -->
    <div class="bg-gradient-to-br from-[#0B1F3A] via-[#0E2748] to-[#14355E] text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-primary-fixed text-xs mb-3 border border-white/15">
                    <span class="material-symbols-outlined text-[14px]">shield_person</span>
                    <span>Tour Manager: {{ $tour->creator->full_name }}</span>
                </div>
                <h1 class="font-display text-2xl sm:text-4xl font-extrabold tracking-tight">
                    {{ $tour->tour_name }}
                </h1>
                <!-- Route Waypoints -->
                <div class="mt-3 flex items-center gap-2 text-xs sm:text-sm text-white/80 flex-wrap">
                    <span class="material-symbols-outlined text-[18px] text-teal-300">near_me</span>
                    <span class="font-bold text-white">{{ $tour->start_location }}</span>
                    @foreach ($tour->destinations as $dest)
                        <span class="text-white/40">→</span>
                        <span class="font-bold text-teal-200">{{ $dest->destination_name }}</span>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center gap-5 text-xs text-white/70 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                        <span>{{ $tour->start_date->format('M d, Y') }} — {{ $tour->end_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">groups</span>
                        <span>{{ $tour->members->count() }} Members</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">receipt</span>
                        <span>{{ $tour->expenses->count() }} Expenses Logged</span>
                    </div>
                </div>

                @if ($tour->isEmergencyFinished() && !empty($tour->emergency_reason))
                    <div class="mt-4 p-3 rounded-xl bg-red-500/20 border border-red-400/40 text-xs text-red-200">
                        <span class="font-bold text-white block mb-0.5">Emergency Finish Reason:</span>
                        {{ $tour->emergency_reason }}
                    </div>
                @endif
            </div>

            <!-- Finish Tour Action Buttons (Requirements 5 & 6) -->
            @if (! $tour->isFinished())
                <div class="flex flex-col sm:flex-row lg:flex-col gap-2.5 shrink-0">
                    <!-- Normal Finish Tour Button -->
                    <button type="button" onclick="openModal('finishTourModal')"
                        class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-container text-white text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-md cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">flag</span>
                        <span>Finish Tour &amp; Settle</span>
                    </button>

                    <!-- Emergency Finish Tour Button -->
                    <button type="button" onclick="openModal('emergencyFinishModal')"
                        class="px-5 py-2.5 rounded-xl bg-red-600/90 hover:bg-red-600 text-white text-xs font-bold transition-all flex items-center justify-center gap-2 border border-red-400/30 shadow-md cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">e911_emergency</span>
                        <span>Emergency Finish Tour</span>
                    </button>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-white/10 border border-white/15 text-center shrink-0">
                    <span class="text-xs text-white/70 uppercase tracking-wider block">Tour State</span>
                    <span class="font-display text-lg font-bold text-white block mt-0.5">
                        {{ $tour->isEmergencyFinished() ? 'Emergency Closed' : 'Settled & Completed' }}
                    </span>
                    <span class="text-[11px] text-teal-300 block mt-1">
                        {{ $tour->finished_at ? $tour->finished_at->format('M d, Y h:i A') : '' }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- 5 Financial Summary Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- Budget -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-outline-variant/30">
            <span class="text-[11px] font-semibold text-secondary uppercase tracking-wider">Total Budget</span>
            <span class="font-display text-xl sm:text-2xl font-extrabold text-on-surface block mt-1">
                ৳{{ number_format($financials['estimated_budget'], 2) }}
            </span>
            <span class="text-[10px] text-outline mt-0.5 block">Estimated goal</span>
        </div>

        <!-- Actual Expenses -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-outline-variant/30">
            <span class="text-[11px] font-semibold text-secondary uppercase tracking-wider">Actual Expenses</span>
            <span class="font-display text-xl sm:text-2xl font-extrabold text-[#c44f32] block mt-1">
                ৳{{ number_format($financials['total_actual_expenses'], 2) }}
            </span>
            <span class="text-[10px] text-outline mt-0.5 block">{{ $tour->expenses->count() }} expenses</span>
        </div>

        <!-- Collected Money -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-outline-variant/30">
            <span class="text-[11px] font-semibold text-secondary uppercase tracking-wider">Total Collected</span>
            <span class="font-display text-xl sm:text-2xl font-extrabold text-primary block mt-1">
                ৳{{ number_format($financials['total_collected_money'], 2) }}
            </span>
            <span class="text-[10px] text-outline mt-0.5 block">Received manually</span>
        </div>

        <!-- Remaining Balance -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-outline-variant/30">
            <span class="text-[11px] font-semibold text-secondary uppercase tracking-wider">Remaining Fund</span>
            <span class="font-display text-xl sm:text-2xl font-extrabold {{ $financials['remaining_fund_balance'] >= 0 ? 'text-emerald-700' : 'text-red-600' }} block mt-1">
                ৳{{ number_format($financials['remaining_fund_balance'], 2) }}
            </span>
            <span class="text-[10px] text-outline mt-0.5 block">Collected − Spent</span>
        </div>

        <!-- Total Refunds Due -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-outline-variant/30 col-span-2 sm:col-span-1">
            <span class="text-[11px] font-semibold text-secondary uppercase tracking-wider">Total Refunds Due</span>
            <span class="font-display text-xl sm:text-2xl font-extrabold text-amber-600 block mt-1">
                ৳{{ number_format($financials['total_refundable_amount'], 2) }}
            </span>
            <span class="text-[10px] text-outline mt-0.5 block">To returning members</span>
        </div>
    </div>

    <!-- Tabs Navigation Bar -->
    <div class="bg-white rounded-2xl border border-outline-variant/30 p-2 shadow-sm flex items-center gap-1.5 overflow-x-auto">
        <button type="button" onclick="switchTab('tab-members')" id="btn-tab-members"
            class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-primary text-white shadow-sm cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">group</span>
            <span>Members ({{ $tour->members->count() }})</span>
        </button>

        <button type="button" onclick="switchTab('tab-expenses')" id="btn-tab-expenses"
            class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-secondary hover:bg-surface-container cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
            <span>Expenses &amp; Equal Splits ({{ $tour->expenses->count() }})</span>
        </button>

        <button type="button" onclick="switchTab('tab-payments')" id="btn-tab-payments"
            class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-secondary hover:bg-surface-container cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">payments</span>
            <span>Manual Payment Logging</span>
        </button>

        <button type="button" onclick="switchTab('tab-settlement')" id="btn-tab-settlement"
            class="tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-secondary hover:bg-surface-container cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">currency_exchange</span>
            <span>Settlement &amp; Refunds</span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: MEMBERS (Requirement 2 & 4: Manual Members without App Account) -->
    <!-- ========================================================================= -->
    <div id="tab-members" class="tab-pane flex flex-col gap-4">
        <div class="bg-white rounded-2xl border border-outline-variant/30 p-5 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="font-display text-base font-bold text-on-surface">Tour Members &amp; Participants</h3>
                <p class="text-xs text-secondary mt-0.5">
                    Members do not need application accounts. You can add them with their name and phone directly.
                </p>
            </div>
            @if (! $tour->isFinished())
                <button type="button" onclick="openModal('addMemberModal')"
                    class="px-4 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container transition-all flex items-center gap-1.5 shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    <span>Add Member</span>
                </button>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="p-4">Member Name</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Contact Phone</th>
                            <th class="p-4 text-right">Total Paid (৳)</th>
                            <th class="p-4 text-right">Expense Share (৳)</th>
                            <th class="p-4 text-right">Net Balance (৳)</th>
                            <th class="p-4 text-center">Settlement Status</th>
                            @if (! $tour->isFinished())
                                <th class="p-4 text-center">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-on-surface">
                        @foreach ($financials['members'] as $m)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-4 font-bold flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center text-primary font-bold text-xs">
                                        {{ strtoupper(substr($m['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="block">{{ $m['name'] }}</span>
                                        @if ($m['tour_role'] === 'manager')
                                            <span class="text-[10px] text-primary font-semibold">Primary Manager</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if ($m['tour_role'] === 'manager')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary border border-primary/20">
                                            Tour Manager
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-secondary/10 text-secondary">
                                            Member
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-secondary">{{ $m['phone'] ?? '—' }}</td>
                                <td class="p-4 text-right font-bold text-primary">৳{{ number_format($m['total_paid'], 2) }}</td>
                                <td class="p-4 text-right font-semibold text-secondary">৳{{ number_format($m['total_share'], 2) }}</td>
                                <td class="p-4 text-right font-extrabold {{ $m['net_balance'] > 0 ? 'text-emerald-600' : ($m['net_balance'] < 0 ? 'text-red-600' : 'text-secondary') }}">
                                    ৳{{ number_format($m['net_balance'], 2) }}
                                </td>
                                <td class="p-4 text-center">
                                    @if ($m['settlement_status'] === 'Refund Due')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Refund ৳{{ number_format($m['refund_amount'], 2) }}
                                        </span>
                                    @elseif ($m['settlement_status'] === 'Payment Due')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                            Due ৳{{ number_format($m['due_amount'], 2) }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-surface-container text-secondary">
                                            Settled (৳0)
                                        </span>
                                    @endif
                                </td>
                                @if (! $tour->isFinished())
                                    <td class="p-4 text-center">
                                        @if ($m['tour_role'] !== 'manager')
                                            <form action="{{ route('manager.tours.members.remove', [$tour->id, $m['member_id']]) }}" method="POST"
                                                onsubmit="return confirm('Remove member {{ $m['name'] }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-outline hover:text-red-600 transition-colors p-1" title="Remove member">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[10px] text-outline">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: EXPENSES & EQUAL SPLITTING (Requirement 3)                         -->
    <!-- ========================================================================= -->
    <div id="tab-expenses" class="tab-pane hidden flex flex-col gap-4">
        <div class="bg-white rounded-2xl border border-outline-variant/30 p-5 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="font-display text-base font-bold text-on-surface">Tour Expenses &amp; Equal Splits</h3>
                <p class="text-xs text-secondary mt-0.5">
                    Every expense is divided equally among all {{ $tour->members->count() }} members including the Tour Manager.
                </p>
            </div>
            @if (! $tour->isFinished())
                <button type="button" onclick="openModal('addExpenseModal')"
                    class="px-4 py-2 rounded-xl bg-[#c44f32] text-white text-xs font-bold hover:bg-[#a3371c] transition-all flex items-center gap-1.5 shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">add_card</span>
                    <span>Add Expense</span>
                </button>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="p-4">Expense Title</th>
                            <th class="p-4">Category</th>
                            <th class="p-4">Date</th>
                            <th class="p-4 text-right">Total Amount (৳)</th>
                            <th class="p-4 text-right">Each Member's Share (৳)</th>
                            <th class="p-4 text-center">Payment Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-on-surface">
                        @forelse ($tour->expenses as $expense)
                            @php
                                $sharesCount = $expense->shares->count();
                                $avgShare = $sharesCount > 0 ? round($expense->total_amount / $sharesCount, 2) : 0;
                                $paidCount = $expense->shares->filter(fn($s) => $s->paymentStatus() === 'Paid')->count();
                            @endphp
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="p-4 font-bold text-on-surface flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px] text-[#c44f32]">receipt</span>
                                    <span>{{ $expense->expense_name }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-surface-container text-secondary">
                                        {{ $expense->category->category_name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="p-4 text-secondary">{{ $expense->expense_date->format('M d, Y') }}</td>
                                <td class="p-4 text-right font-extrabold text-[#c44f32]">
                                    ৳{{ number_format($expense->total_amount, 2) }}
                                </td>
                                <td class="p-4 text-right font-bold text-primary">
                                    ৳{{ number_format($avgShare, 2) }}
                                    <span class="text-[10px] text-outline font-normal block">÷ {{ $sharesCount }} members</span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $paidCount === $sharesCount ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $paidCount }} / {{ $sharesCount }} Paid
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-secondary">
                                    No expenses logged for this tour yet. Tap "Add Expense" to record one!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: MANUAL PAYMENT MANAGEMENT (Requirement 4)                          -->
    <!-- ========================================================================= -->
    <div id="tab-payments" class="tab-pane hidden flex flex-col gap-5">
        <div class="bg-white rounded-2xl border border-outline-variant/30 p-5 shadow-sm">
            <h3 class="font-display text-base font-bold text-on-surface">Manual Payment Management</h3>
            <p class="text-xs text-secondary mt-0.5">
                No payment gateway. When members pay you via Cash, bKash, Nagad, or Bank, record the received amount here. Status is calculated automatically.
            </p>
        </div>

        @forelse ($tour->expenses as $expense)
            <div class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden shadow-sm">
                <!-- Expense Header -->
                <div class="p-4 bg-surface-container-low border-b border-outline-variant/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-primary text-[20px]">receipt</span>
                        <div>
                            <span class="font-bold text-on-surface text-sm">{{ $expense->expense_name }}</span>
                            <span class="text-xs text-secondary ml-2">Total: ৳{{ number_format($expense->total_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="text-xs text-secondary">
                        Equal Share: <span class="font-bold text-primary">৳{{ number_format($expense->total_amount / max(1, $expense->shares->count()), 2) }}</span> per member
                    </div>
                </div>

                <!-- Members Payment Table for this Expense -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-white text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/10">
                            <tr>
                                <th class="p-3.5 pl-5">Member</th>
                                <th class="p-3.5 text-right">Required Share</th>
                                <th class="p-3.5 text-right">Total Paid</th>
                                <th class="p-3.5 text-right">Remaining Due</th>
                                <th class="p-3.5 text-center">Status</th>
                                <th class="p-3.5 text-center">Recent Payments</th>
                                @if (! $tour->isFinished())
                                    <th class="p-3.5 text-right pr-5">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($expense->shares as $share)
                                @php
                                    $paid = $share->paidAmount();
                                    $due = $share->remainingDue();
                                    $status = $share->paymentStatus();
                                @endphp
                                <tr class="hover:bg-surface-container-lowest">
                                    <td class="p-3.5 pl-5 font-bold text-on-surface">
                                        {{ $share->tourMember->display_name }}
                                        @if ($share->tourMember->tour_role === 'manager')
                                            <span class="text-[10px] text-primary font-normal">(Manager)</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-right text-secondary">৳{{ number_format($share->share_amount, 2) }}</td>
                                    <td class="p-3.5 text-right font-bold text-primary">৳{{ number_format($paid, 2) }}</td>
                                    <td class="p-3.5 text-right font-bold {{ $due > 0 ? 'text-red-600' : 'text-secondary' }}">
                                        ৳{{ number_format($due, 2) }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        @if ($status === 'Paid')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Paid
                                            </span>
                                        @elseif ($status === 'Partial')
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Partial
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-center text-secondary">
                                        @if ($share->payments->isNotEmpty())
                                            <span class="text-[11px]">
                                                {{ $share->payments->last()->method }} ({{ $share->payments->count() }} payments)
                                            </span>
                                        @else
                                            <span class="text-outline text-[11px]">None</span>
                                        @endif
                                    </td>
                                    @if (! $tour->isFinished())
                                        <td class="p-3.5 text-right pr-5">
                                            <button type="button"
                                                onclick="openPaymentModal({{ $tour->id }}, {{ $expense->id }}, {{ $share->tourMember->id }}, '{{ addslashes($share->tourMember->display_name) }}', {{ $due }})"
                                                class="px-3 py-1.5 rounded-lg bg-surface-container hover:bg-primary hover:text-white text-primary text-xs font-bold transition-all shadow-sm cursor-pointer">
                                                Record Payment
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center text-secondary border border-outline-variant/30">
                Log at least one expense under the "Expenses" tab before recording payments.
            </div>
        @endforelse
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: SETTLEMENT & REFUNDS (Requirements 5, 6 & 7)                       -->
    <!-- ========================================================================= -->
    <div id="tab-settlement" class="tab-pane hidden flex flex-col gap-6">
        <!-- Settlement Report Header -->
        <div class="bg-white rounded-2xl border border-outline-variant/30 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="font-display text-base font-bold text-on-surface">Final Financial Settlement Report</h3>
                <p class="text-xs text-secondary mt-0.5">
                    Net Balance = Total Amount Paid − Equal Share of Actual Expenses.
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="px-3 py-1 rounded-xl bg-surface-container font-bold text-on-surface">
                    Fund Balance: ৳{{ number_format($financials['remaining_fund_balance'], 2) }}
                </span>
                <span class="px-3 py-1 rounded-xl bg-amber-50 text-amber-900 border border-amber-200 font-bold">
                    Refunds Due: ৳{{ number_format($financials['total_refundable_amount'], 2) }}
                </span>
            </div>
        </div>

        <!-- Settlement Table -->
        <div class="bg-white rounded-2xl border border-outline-variant/30 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="p-4">Member Name</th>
                            <th class="p-4 text-right">Total Paid (P)</th>
                            <th class="p-4 text-right">Equal Share (S)</th>
                            <th class="p-4 text-right">Net Balance (P − S)</th>
                            <th class="p-4 text-center">Settlement Status</th>
                            <th class="p-4 text-right">Refund Amount</th>
                            <th class="p-4 text-center">Refund Status</th>
                            <th class="p-4 text-center">Refund Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-on-surface">
                        @foreach ($financials['members'] as $m)
                            <tr class="hover:bg-surface-container-lowest">
                                <td class="p-4 font-bold">
                                    {{ $m['name'] }}
                                    @if ($m['tour_role'] === 'manager')
                                        <span class="text-[10px] text-primary font-normal block">Tour Manager</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right font-bold text-primary">৳{{ number_format($m['total_paid'], 2) }}</td>
                                <td class="p-4 text-right font-semibold text-secondary">৳{{ number_format($m['total_share'], 2) }}</td>
                                <td class="p-4 text-right font-extrabold {{ $m['net_balance'] > 0 ? 'text-emerald-600' : ($m['net_balance'] < 0 ? 'text-red-600' : 'text-secondary') }}">
                                    ৳{{ number_format($m['net_balance'], 2) }}
                                </td>
                                <td class="p-4 text-center">
                                    @if ($m['settlement_status'] === 'Refund Due')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Overpaid
                                        </span>
                                    @elseif ($m['settlement_status'] === 'Payment Due')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                            Underpaid (Owes ৳{{ number_format($m['due_amount'], 2) }})
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-surface-container text-secondary">
                                            Settled
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-right font-bold text-emerald-700">
                                    @if ($m['refund_amount'] > 0)
                                        ৳{{ number_format($m['refund_amount'], 2) }}
                                    @else
                                        ৳0.00
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    @if ($m['refund_amount'] > 0)
                                        @if ($m['refund_record'] && $m['refund_record']->status === 'refunded')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Refunded ({{ $m['refund_record']->method }})
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                Pending
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-outline text-[11px]">No Refund Due</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    @if ($m['refund_amount'] > 0 && $m['refund_record'])
                                        <button type="button"
                                            onclick="openRefundModal({{ $m['refund_record']->id }}, '{{ addslashes($m['name']) }}', {{ $m['refund_amount'] }}, '{{ $m['refund_record']->status }}')"
                                            class="px-2.5 py-1 rounded-lg bg-surface-container hover:bg-primary hover:text-white text-xs font-semibold transition-colors cursor-pointer">
                                            Update Status
                                        </button>
                                    @else
                                        <span class="text-outline text-[11px]">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD MEMBER (No login/account needed!)                               -->
<!-- ========================================================================= -->
<div id="addMemberModal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display text-lg font-bold text-on-surface">Add Tour Member</h3>
            <button type="button" onclick="closeModal('addMemberModal')" class="text-outline hover:text-on-surface">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <form action="{{ route('manager.tours.members.add', $tour->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Member Name *
                </label>
                <input type="text" name="name" required placeholder="e.g. Rahim Uddin"
                    class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary focus:bg-white outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Phone Number (Optional)
                </label>
                <input type="text" name="phone" placeholder="e.g. 01712345678"
                    class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary focus:bg-white outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Role in Tour
                </label>
                <select name="tour_role" class="w-full h-11 px-3 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none text-secondary">
                    <option value="member" selected>Tour Member</option>
                    <option value="manager">Co-Manager</option>
                </select>
            </div>
            <p class="text-[11px] text-secondary">
                * Tour members do not require login accounts. All expenses will automatically re-balance equally across all members.
            </p>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeModal('addMemberModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-secondary">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container">Add Member</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ADD EXPENSE (Requirement 3: Equal Splitting)                       -->
<!-- ========================================================================= -->
<div id="addExpenseModal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display text-lg font-bold text-on-surface">Add Tour Expense</h3>
            <button type="button" onclick="closeModal('addExpenseModal')" class="text-outline hover:text-on-surface">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <form action="{{ route('manager.tours.expenses.add', $tour->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Expense Title *
                </label>
                <input type="text" name="expense_name" required placeholder="e.g. Scania Bus Tickets (Dhaka to Cox's)"
                    class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary focus:bg-white outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                        Total Amount (৳) *
                    </label>
                    <input type="number" step="0.01" min="1" name="total_amount" required placeholder="e.g. 40000"
                        class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary focus:bg-white outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                        Date *
                    </label>
                    <input type="date" name="expense_date" value="{{ now()->format('Y-m-d') }}" required
                        class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none">
                </div>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Category
                </label>
                <input type="text" name="category_name" placeholder="e.g. Transportation, Hotel, Meals, Activities"
                    class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary focus:bg-white outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Description / Notes
                </label>
                <textarea name="description" rows="2" placeholder="Optional notes about this expense…"
                    class="w-full p-3 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary focus:bg-white outline-none"></textarea>
            </div>
            <div class="p-3 rounded-xl bg-surface-container-low border border-primary/20 text-xs text-secondary">
                <span class="font-bold text-on-surface">Auto Equal Split:</span> This amount will be split equally among all {{ $tour->members->count() }} members including you.
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeModal('addExpenseModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-secondary">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#c44f32] text-white text-xs font-bold hover:bg-[#a3371c]">Save &amp; Split Equally</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: RECORD MANUAL PAYMENT (Requirement 4)                              -->
<!-- ========================================================================= -->
<div id="recordPaymentModal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-display text-lg font-bold text-on-surface">Record Manual Payment</h3>
                <span id="paymentModalMember" class="text-xs text-primary font-bold"></span>
            </div>
            <button type="button" onclick="closeModal('recordPaymentModal')" class="text-outline hover:text-on-surface">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <form id="recordPaymentForm" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="tour_member_id" id="paymentTourMemberId">
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Amount Received (৳) *
                </label>
                <input type="number" step="0.01" min="0.01" name="amount" id="paymentAmountInput" required
                    class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary focus:bg-white outline-none font-bold text-primary">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                        Payment Method *
                    </label>
                    <select name="method" required class="w-full h-11 px-3 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none">
                        <option value="Cash" selected>Cash</option>
                        <option value="bKash">bKash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="Bank">Bank Transfer</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                        Date Received
                    </label>
                    <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}"
                        class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none">
                </div>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Trx ID / Notes (Optional)
                </label>
                <input type="text" name="transaction_id" placeholder="e.g. bKash TrxID or Cash reference"
                    class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary focus:bg-white outline-none">
            </div>
            <p class="text-[11px] text-secondary">
                * You are recording money you have already received. Remaining due and payment status will update automatically.
            </p>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeModal('recordPaymentModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-secondary">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container">Save Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: FINISH TOUR (Requirement 5)                                        -->
<!-- ========================================================================= -->
<div id="finishTourModal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[24px]">task_alt</span>
                </div>
                <div>
                    <h3 class="font-display text-lg font-bold text-on-surface">Finish Tour &amp; Final Settlement</h3>
                    <span class="text-xs text-secondary">Normal Expedition Completion</span>
                </div>
            </div>
            <button type="button" onclick="closeModal('finishTourModal')" class="text-outline hover:text-on-surface">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Settlement Summary Preview -->
        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/30 space-y-2 text-xs mb-4">
            <div class="flex justify-between">
                <span class="text-secondary">Total Actual Expenses:</span>
                <span class="font-bold text-[#c44f32]">৳{{ number_format($financials['total_actual_expenses'], 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-secondary">Total Collected Money:</span>
                <span class="font-bold text-primary">৳{{ number_format($financials['total_collected_money'], 2) }}</span>
            </div>
            <div class="flex justify-between border-t border-outline-variant/20 pt-1.5 font-bold">
                <span>Remaining Fund Balance:</span>
                <span class="{{ $financials['remaining_fund_balance'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                    ৳{{ number_format($financials['remaining_fund_balance'], 2) }}
                </span>
            </div>
            <div class="flex justify-between text-amber-700">
                <span>Total Refundable to Members:</span>
                <span class="font-bold">৳{{ number_format($financials['total_refundable_amount'], 2) }}</span>
            </div>
        </div>

        <form action="{{ route('manager.tours.finish', $tour->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Completion Notes / Closing Remarks
                </label>
                <textarea name="notes" rows="2" placeholder="e.g. Trip completed safely. All hotels and buses settled."
                    class="w-full p-3 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none"></textarea>
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeModal('finishTourModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-secondary">Cancel</button>
                <button type="submit" class="px-6 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container shadow-sm">
                    Confirm &amp; Finish Tour
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EMERGENCY FINISH TOUR (Requirement 6 - Complete Financial Preview) -->
<!-- ========================================================================= -->
<div id="emergencyFinishModal" class="modal-backdrop fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl p-6 shadow-2xl border border-red-300 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-red-100">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
                    <span class="material-symbols-outlined text-[24px]">crisis_alert</span>
                </div>
                <div>
                    <h3 class="font-display text-lg font-bold text-red-900">Emergency Finish Tour</h3>
                    <span class="text-xs text-red-600 font-semibold">Immediate early termination with financial settlement preview</span>
                </div>
            </div>
            <button type="button" onclick="closeModal('emergencyFinishModal')" class="text-outline hover:text-on-surface">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Settlement Preview Callout -->
        <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-900 space-y-1.5">
            <p class="font-bold">Important Refund Rule:</p>
            <p>Member's Net Balance = Total Amount Paid − Equal Share of Actual Expenses.</p>
            <p>• Positive: Receives refund.</p>
            <p>• Zero: No refund due.</p>
            <p>• Negative: Member still owes money.</p>
            <p>• A member who paid nothing will NOT receive any refund.</p>
        </div>

        <!-- Live Financial Preview Table -->
        <div class="border border-outline-variant/30 rounded-xl overflow-hidden mb-4">
            <table class="w-full text-left text-xs">
                <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="p-3">Member</th>
                        <th class="p-3 text-right">Paid (৳)</th>
                        <th class="p-3 text-right">Actual Share (৳)</th>
                        <th class="p-3 text-right">Net Balance (৳)</th>
                        <th class="p-3 text-center">Settlement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @foreach ($financials['members'] as $m)
                        <tr>
                            <td class="p-3 font-bold">{{ $m['name'] }}</td>
                            <td class="p-3 text-right font-bold text-primary">৳{{ number_format($m['total_paid'], 2) }}</td>
                            <td class="p-3 text-right text-secondary">৳{{ number_format($m['total_share'], 2) }}</td>
                            <td class="p-3 text-right font-extrabold {{ $m['net_balance'] > 0 ? 'text-emerald-700' : ($m['net_balance'] < 0 ? 'text-red-600' : 'text-secondary') }}">
                                ৳{{ number_format($m['net_balance'], 2) }}
                            </td>
                            <td class="p-3 text-center">
                                @if ($m['refund_amount'] > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Refund ৳{{ number_format($m['refund_amount'], 2) }}
                                    </span>
                                @elseif ($m['due_amount'] > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                        Owes ৳{{ number_format($m['due_amount'], 2) }}
                                    </span>
                                @else
                                    <span class="text-outline">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Emergency Form -->
        <form action="{{ route('manager.tours.emergency-finish', $tour->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-red-900 block mb-1">
                    Emergency Reason *
                </label>
                <input type="text" name="emergency_reason" required placeholder="e.g. Flash flooding in Sylhet / Road blockage / Medical emergency"
                    class="w-full h-11 px-4 rounded-xl bg-red-50/50 text-sm border border-red-300 focus:border-red-600 outline-none">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Additional Notes (Optional)
                </label>
                <textarea name="notes" rows="2" placeholder="Describe instructions given to members, transport arrangements, etc."
                    class="w-full p-3 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none"></textarea>
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeModal('emergencyFinishModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-secondary">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow-md cursor-pointer">
                    Confirm Emergency Finish
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: UPDATE REFUND STATUS (Requirement 7)                               -->
<!-- ========================================================================= -->
<div id="refundStatusModal" class="modal-backdrop fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border border-outline-variant/30">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-display text-lg font-bold text-on-surface">Update Refund Status</h3>
                <span id="refundModalMember" class="text-xs text-primary font-bold"></span>
            </div>
            <button type="button" onclick="closeModal('refundStatusModal')" class="text-outline hover:text-on-surface">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <form id="refundStatusForm" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Refund Status *
                </label>
                <select name="status" id="refundStatusSelect" class="w-full h-11 px-3 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none">
                    <option value="pending">Pending (Not yet returned)</option>
                    <option value="refunded">Refunded (Money returned to member)</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Payment Method Used for Refund
                </label>
                <select name="method" class="w-full h-11 px-3 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none">
                    <option value="Cash" selected>Cash Handover</option>
                    <option value="bKash">bKash</option>
                    <option value="Nagad">Nagad</option>
                    <option value="Bank">Bank Transfer</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-on-surface block mb-1">
                    Notes / Receipt Memo
                </label>
                <input type="text" name="notes" placeholder="e.g. Cash returned in person / bKash TrxID"
                    class="w-full h-11 px-4 rounded-xl bg-surface-container-low text-sm border border-outline-variant/30 focus:border-primary outline-none">
            </div>
            <p class="text-[11px] text-secondary">
                * No automatic gateway. Mark as refunded only after physically returning the money to the member.
            </p>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeModal('refundStatusModal')" class="px-4 py-2 rounded-xl text-xs font-semibold text-secondary">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-container">Update Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Tab & Modal Vanilla JavaScript -->
<script>
    function switchTab(tabId) {
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.getElementById(tabId).classList.remove('hidden');

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white', 'shadow-sm');
            btn.classList.add('text-secondary');
        });

        const activeBtn = document.getElementById('btn-' + tabId);
        if (activeBtn) {
            activeBtn.classList.remove('text-secondary');
            activeBtn.classList.add('bg-primary', 'text-white', 'shadow-sm');
        }
    }

    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openPaymentModal(tourId, expenseId, memberId, memberName, remainingDue) {
        document.getElementById('paymentTourMemberId').value = memberId;
        document.getElementById('paymentModalMember').innerText = 'Member: ' + memberName;
        document.getElementById('paymentAmountInput').value = remainingDue > 0 ? remainingDue : '';
        document.getElementById('recordPaymentForm').action = '/manager/tours/' + tourId + '/expenses/' + expenseId + '/payment';
        openModal('recordPaymentModal');
    }

    function openRefundModal(refundId, memberName, amount, currentStatus) {
        document.getElementById('refundModalMember').innerText = memberName + ' (Refund: ৳' + amount + ')';
        document.getElementById('refundStatusSelect').value = currentStatus;
        document.getElementById('refundStatusForm').action = '/manager/refunds/' + refundId + '/status';
        openModal('refundStatusModal');
    }

    // Close on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                this.classList.remove('flex');
            }
        });
    });
</script>
@endsection
