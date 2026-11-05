@extends('layouts.admin', ['title' => $tour->tour_name . ' – Super Admin Tour Audit'])

@section('content')
<div class="flex flex-col gap-6">
    <!-- Top Back Link & Status Row -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.tours.index') }}" class="text-xs font-semibold text-secondary hover:text-primary flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Back to All Tours Monitoring</span>
        </a>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 flex items-center gap-1.5 border border-amber-200 shadow-sm">
                <span class="material-symbols-outlined text-[16px] text-amber-600">visibility</span>
                <span>Super Admin Audit Mode (Read-Only)</span>
            </span>
        </div>
    </div>

    <!-- Tour Hero Banner -->
    <div class="bg-gradient-to-br from-[#071c36] via-[#0b2b4f] to-[#123e70] text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-primary-fixed text-xs mb-3 border border-white/15">
                    <span class="material-symbols-outlined text-[14px]">supervisor_account</span>
                    <span>Tour Manager: <strong>{{ $tour->creator?->full_name ?? 'N/A' }}</strong> ({{ $tour->creator?->email ?? '' }})</span>
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
                        <span>{{ $tour->start_date ? \Carbon\Carbon::parse($tour->start_date)->format('M d, Y') : 'TBD' }} — {{ $tour->end_date ? \Carbon\Carbon::parse($tour->end_date)->format('M d, Y') : 'TBD' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">groups</span>
                        <span>{{ $tour->members->count() }} Members</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                        <span>{{ $tour->expenses->count() }} Logged Expenses</span>
                    </div>
                </div>

                @if ($tour->isEmergencyFinished())
                    <div class="mt-4 p-3.5 rounded-xl bg-red-500/25 border border-red-400/40 text-xs text-red-100 flex items-start gap-2">
                        <span class="material-symbols-outlined text-[18px] text-red-300 shrink-0">emergency</span>
                        <div>
                            <span class="font-bold text-white block">Emergency Terminated:</span>
                            <span>{{ $tour->emergency_reason ?: 'No emergency reason was recorded.' }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tour Status Display Card -->
            <div class="p-5 rounded-2xl bg-white/10 border border-white/15 text-center shrink-0 min-w-[220px]">
                <span class="text-xs text-white/70 uppercase tracking-wider block font-semibold">Tour Status</span>
                <span class="font-display text-xl font-bold text-white block mt-1">
                    @if ($tour->isEmergencyFinished())
                        <span class="text-red-300">Emergency Finished</span>
                    @elseif ($tour->status === 'completed')
                        <span class="text-purple-300">Completed &amp; Settled</span>
                    @elseif ($tour->status === 'ongoing')
                        <span class="text-emerald-300">Ongoing</span>
                    @elseif ($tour->status === 'upcoming')
                        <span class="text-blue-300">Upcoming</span>
                    @else
                        <span>{{ ucfirst($tour->status) }}</span>
                    @endif
                </span>
                @if ($tour->finished_at)
                    <span class="text-[11px] text-teal-200 block mt-1">
                        Settled: {{ \Carbon\Carbon::parse($tour->finished_at)->format('M d, Y h:i A') }}
                    </span>
                @endif
            </div>
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
            <span class="text-[10px] text-outline mt-0.5 block">Recorded payments</span>
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
            <span class="font-display text-xl sm:text-2xl font-extrabold text-purple-700 block mt-1">
                ৳{{ number_format($financials['total_refundable_amount'], 2) }}
            </span>
            <span class="text-[10px] text-outline mt-0.5 block">Net overpayments</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-2 flex flex-wrap gap-2 text-xs font-bold" id="adminTabs">
        <button type="button" onclick="showTab('membersTab', this)" class="tab-btn px-4 py-2 rounded-xl bg-primary text-white transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">groups</span>
            <span>Tour Members ({{ $tour->members->count() }})</span>
        </button>
        <button type="button" onclick="showTab('expensesTab', this)" class="tab-btn px-4 py-2 rounded-xl bg-surface-container text-secondary hover:text-on-surface transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
            <span>Expenses &amp; Equal Splits ({{ $tour->expenses->count() }})</span>
        </button>
        <button type="button" onclick="showTab('paymentsTab', this)" class="tab-btn px-4 py-2 rounded-xl bg-surface-container text-secondary hover:text-on-surface transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">payments</span>
            <span>Payments History</span>
        </button>
        <button type="button" onclick="showTab('settlementTab', this)" class="tab-btn px-4 py-2 rounded-xl bg-surface-container text-secondary hover:text-on-surface transition-all flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
            <span>Settlement &amp; Refunds Audit</span>
        </button>
    </div>

    <!-- TAB 1: MEMBERS OVERVIEW -->
    <div id="membersTab" class="tab-content flex flex-col gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
                <div>
                    <h2 class="font-display text-base font-bold text-on-surface">Tour Members &amp; Financial Summary</h2>
                    <p class="text-xs text-secondary mt-0.5">Tour Manager plus all participants. Each member bears an equal share of every expense.</p>
                </div>
                <span class="text-xs text-secondary font-medium">{{ count($financials['members']) }} members total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="py-3 px-4">Member Name &amp; Contact</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4 text-right">Total Paid</th>
                            <th class="py-3 px-4 text-right">Equal Expense Share</th>
                            <th class="py-3 px-4 text-right">Net Balance (Paid − Share)</th>
                            <th class="py-3 px-4 text-center">Settlement Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($financials['members'] as $m)
                            <tr class="hover:bg-surface-container-low/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-on-surface text-sm flex items-center gap-2">
                                        <span>{{ $m['name'] }}</span>
                                        @if($m['tour_role'] === 'manager')
                                            <span class="px-2 py-0.5 rounded-md bg-primary/10 text-primary text-[10px] font-bold">Manager</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-secondary flex items-center gap-3 mt-0.5">
                                        @if($m['phone'])
                                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">call</span>{{ $m['phone'] }}</span>
                                        @endif
                                        @if($m['email'])
                                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">mail</span>{{ $m['email'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-secondary capitalize">
                                    {{ $m['tour_role'] }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-emerald-700">
                                    ৳{{ number_format($m['total_paid'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-[#c44f32]">
                                    ৳{{ number_format($m['total_share'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold {{ $m['net_balance'] > 0 ? 'text-purple-700' : ($m['net_balance'] < 0 ? 'text-red-600' : 'text-secondary') }}">
                                    @if($m['net_balance'] > 0)
                                        +৳{{ number_format($m['net_balance'], 2) }}
                                    @elseif($m['net_balance'] < 0)
                                        -৳{{ number_format(abs($m['net_balance']), 2) }}
                                    @else
                                        ৳0.00
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($m['settlement_status'] === 'Refund Due')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 font-bold text-[10px] uppercase tracking-wide border border-purple-200">
                                            Refund Due (৳{{ number_format($m['refund_amount'], 2) }})
                                        </span>
                                    @elseif($m['settlement_status'] === 'Payment Due')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-100 text-red-800 font-bold text-[10px] uppercase tracking-wide border border-red-200">
                                            Owes ৳{{ number_format($m['due_amount'], 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase tracking-wide border border-emerald-200">
                                            Fully Settled
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: EXPENSES & EQUAL SPLITS -->
    <div id="expensesTab" class="tab-content hidden flex-col gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
                <div>
                    <h2 class="font-display text-base font-bold text-on-surface">Tour Expenses &amp; Equal Share Distribution</h2>
                    <p class="text-xs text-secondary mt-0.5">Every expense is divided equally among all tour members (including Tour Manager).</p>
                </div>
                <span class="text-xs font-bold text-primary">Total: ৳{{ number_format($financials['total_actual_expenses'], 2) }}</span>
            </div>

            @if ($tour->expenses->count() > 0)
                <div class="p-4 flex flex-col gap-4">
                    @foreach ($tour->expenses as $expense)
                        <div class="p-4 rounded-xl border border-outline-variant/30 bg-surface-container-low/30 flex flex-col gap-3">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-outline-variant/20 pb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-display text-sm font-bold text-on-surface">{{ $expense->expense_name }}</h3>
                                        <span class="px-2 py-0.5 rounded-full bg-secondary-container/40 text-secondary text-[10px] font-semibold">
                                            {{ $expense->category?->name ?? 'General' }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-secondary mt-0.5">
                                        Logged on {{ $expense->expense_date ? \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') : 'N/A' }}
                                        @if($expense->notes) &bull; <em>{{ $expense->notes }}</em> @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="font-display text-lg font-extrabold text-[#c44f32]">
                                        ৳{{ number_format($expense->total_amount, 2) }}
                                    </div>
                                    <div class="text-[11px] text-outline">
                                        Equal Share: <strong>৳{{ number_format($expense->total_amount / max(1, $tour->members->count()), 2) }}</strong> / member
                                    </div>
                                </div>
                            </div>

                            <!-- Individual member shares table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-surface-container-low text-secondary uppercase text-[9px] tracking-wider">
                                        <tr>
                                            <th class="py-2 px-3">Member</th>
                                            <th class="py-2 px-3 text-right">Equal Share</th>
                                            <th class="py-2 px-3 text-right">Amount Paid</th>
                                            <th class="py-2 px-3 text-right">Remaining Due</th>
                                            <th class="py-2 px-3 text-center">Payment Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-outline-variant/10">
                                        @foreach ($expense->shares as $share)
                                            <tr class="hover:bg-white transition-colors">
                                                <td class="py-2 px-3 font-semibold text-on-surface">
                                                    {{ $share->tourMember?->display_name ?? 'N/A' }}
                                                </td>
                                                <td class="py-2 px-3 text-right font-bold text-on-surface">
                                                    ৳{{ number_format($share->share_amount, 2) }}
                                                </td>
                                                <td class="py-2 px-3 text-right font-bold text-emerald-700">
                                                    ৳{{ number_format($share->paidAmount(), 2) }}
                                                </td>
                                                <td class="py-2 px-3 text-right font-bold {{ $share->remainingDue() > 0 ? 'text-red-600' : 'text-secondary' }}">
                                                    ৳{{ number_format($share->remainingDue(), 2) }}
                                                </td>
                                                <td class="py-2 px-3 text-center">
                                                    @php $pStatus = $share->paymentStatus(); @endphp
                                                    @if($pStatus === 'Paid')
                                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Paid</span>
                                                    @elseif($pStatus === 'Partial')
                                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Partial</span>
                                                    @else
                                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-[10px] font-bold">Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-secondary text-xs">
                    No expenses have been recorded for this tour yet.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: PAYMENTS HISTORY (MANUAL PAYMENTS AUDIT) -->
    <div id="paymentsTab" class="tab-content hidden flex-col gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
                <div>
                    <h2 class="font-display text-base font-bold text-on-surface">Manual Payment Audit Trail</h2>
                    <p class="text-xs text-secondary mt-0.5">Recorded directly by the Tour Manager after receiving physical/mobile payment. No payment gateway.</p>
                </div>
                <span class="text-xs font-bold text-emerald-700">Total Collected: ৳{{ number_format($financials['total_collected_money'], 2) }}</span>
            </div>

            @php
                $allTourPayments = \App\Models\Payment::whereHas('share.expense', function ($q) use ($tour) {
                    $q->where('tour_id', $tour->id);
                })->with(['share.expense', 'share.tourMember', 'receivedBy'])->latest('submitted_at')->get();
            @endphp

            @if($allTourPayments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                            <tr>
                                <th class="py-3 px-4">Date &amp; Time</th>
                                <th class="py-3 px-4">Tour Member</th>
                                <th class="py-3 px-4">Expense Item</th>
                                <th class="py-3 px-4">Payment Method</th>
                                <th class="py-3 px-4 text-right">Amount</th>
                                <th class="py-3 px-4">Transaction / Receipt ID</th>
                                <th class="py-3 px-4">Recorded By</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($allTourPayments as $pay)
                                <tr class="hover:bg-surface-container-low/40 transition-colors">
                                    <td class="py-3 px-4 text-secondary whitespace-nowrap">
                                        {{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('M d, Y') : ($pay->submitted_at ? \Carbon\Carbon::parse($pay->submitted_at)->format('M d, Y h:i A') : 'N/A') }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-on-surface">
                                        {{ $pay->share?->tourMember?->display_name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 text-secondary">
                                        {{ $pay->share?->expense?->expense_name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-secondary uppercase">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-surface-container text-[11px]">
                                            <span class="material-symbols-outlined text-[13px]">payments</span>
                                            {{ $pay->method }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-extrabold text-emerald-700">
                                        ৳{{ number_format($pay->amount, 2) }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[11px] text-outline">
                                        {{ $pay->transaction_id ?: 'Manual Receipt' }}
                                    </td>
                                    <td class="py-3 px-4 text-secondary">
                                        {{ $pay->receivedBy?->full_name ?? ($tour->creator?->full_name ?? 'Manager') }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($pay->status === 'verified')
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Verified</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">{{ ucfirst($pay->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-secondary text-xs">
                    No manual payment records logged for this tour yet.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 4: SETTLEMENT & REFUNDS AUDIT -->
    <div id="settlementTab" class="tab-content hidden flex-col gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
            <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
                <div>
                    <h2 class="font-display text-base font-bold text-on-surface">Final Settlement &amp; Refund Audit</h2>
                    <p class="text-xs text-secondary mt-0.5">
                        Calculation Rule: <code>Member Net Balance = Total Paid − Equal Share of Actual Expenses</code>.
                        A member who paid nothing never receives a refund.
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-secondary block">Total Refunds:</span>
                    <span class="text-sm font-extrabold text-purple-700">৳{{ number_format($financials['total_refundable_amount'], 2) }}</span>
                </div>
            </div>

            <!-- Refunds breakdown table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="py-3 px-4">Member Name</th>
                            <th class="py-3 px-4 text-right">Total Paid</th>
                            <th class="py-3 px-4 text-right">Equal Expense Share</th>
                            <th class="py-3 px-4 text-right">Net Financial Balance</th>
                            <th class="py-3 px-4 text-right">Refund Amount</th>
                            <th class="py-3 px-4 text-center">Refund Status</th>
                            <th class="py-3 px-4">Refund Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($financials['members'] as $m)
                            <tr class="hover:bg-surface-container-low/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-on-surface">
                                    {{ $m['name'] }}
                                    @if($m['tour_role'] === 'manager')
                                        <span class="text-[10px] text-primary font-normal">(Tour Manager)</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-700">
                                    ৳{{ number_format($m['total_paid'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-[#c44f32]">
                                    ৳{{ number_format($m['total_share'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $m['net_balance'] > 0 ? 'text-purple-700' : ($m['net_balance'] < 0 ? 'text-red-600' : 'text-secondary') }}">
                                    @if($m['net_balance'] > 0)
                                        +৳{{ number_format($m['net_balance'], 2) }} (Overpaid)
                                    @elseif($m['net_balance'] < 0)
                                        -৳{{ number_format(abs($m['net_balance']), 2) }} (Underpaid)
                                    @else
                                        ৳0.00 (Balanced)
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-extrabold text-purple-700">
                                    ৳{{ number_format($m['refund_amount'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($m['refund_amount'] > 0)
                                        @if($m['refund_record'] && $m['refund_record']->status === 'refunded')
                                            <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wide">
                                                Refunded
                                            </span>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-wide">
                                                Pending Refund
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-secondary text-[11px]">N/A (No refund due)</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-secondary text-[11px]">
                                    @if($m['refund_record'])
                                        <div>Method: <strong>{{ strtoupper($m['refund_record']->method) }}</strong></div>
                                        @if($m['refund_record']->processed_at)
                                            <div class="text-[10px] text-outline">Processed: {{ \Carbon\Carbon::parse($m['refund_record']->processed_at)->format('M d, Y') }}</div>
                                        @endif
                                    @else
                                        <span>—</span>
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

<script>
    function showTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById(tabId).classList.remove('hidden');

        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('bg-primary', 'text-white');
            el.classList.add('bg-surface-container', 'text-secondary');
        });
        btn.classList.add('bg-primary', 'text-white');
        btn.classList.remove('bg-surface-container', 'text-secondary');
    }
</script>
@endsection
