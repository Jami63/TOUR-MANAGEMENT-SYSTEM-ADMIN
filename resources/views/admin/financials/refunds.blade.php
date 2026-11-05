@extends('layouts.admin', ['title' => 'Master Refunds Monitoring – SafarFly Super Admin'])

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-6 lg:p-7 shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-primary text-[20px]">currency_exchange</span>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Settlement &amp; Refunds</span>
            </div>
            <h1 class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface">
                Master Refunds Monitoring
            </h1>
            <p class="text-xs sm:text-sm text-secondary mt-1">
                Super Admin audit of all refund calculations and physical refund statuses across normal and emergency-finished tours.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 rounded-xl bg-purple-50 text-xs font-bold text-purple-900 flex items-center gap-2 border border-purple-200 shadow-sm">
                <span class="material-symbols-outlined text-[18px] text-purple-700">security</span>
                <span>Audit &amp; Oversight</span>
            </span>
        </div>
    </div>

    <!-- 3 Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Total Refundable</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface block mt-1">
                    ৳{{ number_format($totalRefundable, 2) }}
                </span>
                <span class="text-[11px] text-outline mt-0.5 block">Overall net overpayments</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-primary shadow-sm">
                <span class="material-symbols-outlined text-[26px]">account_balance_wallet</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Actually Refunded</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-emerald-700 block mt-1">
                    ৳{{ number_format($totalRefunded, 2) }}
                </span>
                <span class="text-[11px] text-outline mt-0.5 block">Returned to members</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                <span class="material-symbols-outlined text-[26px]">check_circle</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Pending Refunds</span>
                <span class="font-display text-2xl lg:text-3xl font-extrabold text-amber-700 block mt-1">
                    ৳{{ number_format($totalPendingRefunds, 2) }}
                </span>
                <span class="text-[11px] text-outline mt-0.5 block">Awaiting physical return</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shadow-sm">
                <span class="material-symbols-outlined text-[26px]">pending</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-outline-variant/30 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <form method="GET" action="{{ route('admin.refunds.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <select name="status" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 text-secondary focus:outline-none">
                <option value="">All Refund Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Refunds</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Completed Refunds</option>
            </select>

            <button type="submit" class="h-9 px-3.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors">
                Filter
            </button>
            @if(request()->filled('status'))
                <a href="{{ route('admin.refunds.index') }}" class="text-xs text-secondary hover:underline">Clear</a>
            @endif
        </form>
    </div>

    <!-- Refunds Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
            <h2 class="font-display text-base font-bold text-on-surface">Member Refund Records</h2>
            <span class="text-xs text-secondary">{{ $refunds->total() }} records</span>
        </div>

        @if($refunds->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="py-3 px-4">Tour Expedition</th>
                            <th class="py-3 px-4">Tour Manager</th>
                            <th class="py-3 px-4">Recipient Member</th>
                            <th class="py-3 px-4 text-right">Refund Amount</th>
                            <th class="py-3 px-4">Payment Method</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Processed Date</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach($refunds as $ref)
                            <tr class="hover:bg-surface-container-low/40 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-on-surface">
                                    {{ $ref->tour?->tour_name ?? 'N/A' }}
                                    <div class="text-[10px] text-secondary font-normal">
                                        {{ $ref->tour?->start_location ?? '' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-secondary">
                                    {{ $ref->tour?->creator?->full_name ?? 'Manager' }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-on-surface">
                                    {{ $ref->tourMember?->display_name ?? 'N/A' }}
                                    <div class="text-[10px] text-secondary font-normal">
                                        {{ $ref->tourMember?->display_phone ?? '' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-purple-700">
                                    ৳{{ number_format($ref->refund_amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-secondary uppercase">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-surface-container text-[11px]">
                                        <span class="material-symbols-outlined text-[13px]">currency_exchange</span>
                                        {{ $ref->method }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($ref->status === 'refunded')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Refunded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-secondary text-[11px] whitespace-nowrap">
                                    @if($ref->processed_at)
                                        {{ \Carbon\Carbon::parse($ref->processed_at)->format('M d, Y h:i A') }}
                                        <div class="text-[10px] text-outline">by {{ $ref->refundedBy?->full_name ?? 'Manager' }}</div>
                                    @else
                                        <span class="text-outline">Pending transfer</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($ref->tour_id)
                                        <a href="{{ route('admin.tours.show', $ref->tour_id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container text-primary font-bold text-xs hover:bg-primary hover:text-white transition-all">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                                            <span>Tour Audit</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($refunds->hasPages())
                <div class="p-4 border-t border-outline-variant/20">
                    {{ $refunds->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center text-secondary text-xs">
                No refund records found.
            </div>
        @endif
    </div>
</div>
@endsection
