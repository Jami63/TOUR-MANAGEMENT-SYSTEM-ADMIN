@extends('layouts.admin', ['title' => 'Master Payments Audit – SafarFlow Super Admin'])

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-6 lg:p-7 shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-primary text-[20px]">payments</span>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Manual Payments Audit</span>
            </div>
            <h1 class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface">
                Master Payments Audit Trail
            </h1>
            <p class="text-xs sm:text-sm text-secondary mt-1">
                Live monitoring of all manual payments recorded by Tour Managers (Cash, bKash, Nagad, Bank Transfers).
            </p>
        </div>
        <div>
            <div class="bg-emerald-50 px-4 py-2.5 rounded-xl border border-emerald-200 flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-700 text-[24px]">price_check</span>
                <div>
                    <span class="text-[10px] text-emerald-900 uppercase tracking-wider block font-semibold">Total Verified Collected</span>
                    <span class="font-display text-lg font-extrabold text-emerald-800">৳{{ number_format($totalCollected, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl border border-outline-variant/30 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search txn ID, member, notes, or tour…"
                    class="h-9 pl-9 pr-3.5 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 focus:outline-none focus:ring-1 focus:ring-primary w-72">
            </div>

            <select name="method" onchange="this.form.submit()" class="h-9 px-3 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 text-secondary focus:outline-none">
                <option value="">All Payment Methods</option>
                <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="bkash" {{ request('method') === 'bkash' ? 'selected' : '' }}>bKash</option>
                <option value="nagad" {{ request('method') === 'nagad' ? 'selected' : '' }}>Nagad</option>
                <option value="bank" {{ request('method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
            </select>

            <button type="submit" class="h-9 px-3.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors">
                Filter
            </button>
            @if(request()->hasAny(['search', 'method']))
                <a href="{{ route('admin.payments.index') }}" class="text-xs text-secondary hover:underline">Clear Filter</a>
            @endif
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
            <h2 class="font-display text-base font-bold text-on-surface">Payment Transactions Audit</h2>
            <span class="text-xs text-secondary">{{ $payments->total() }} records</span>
        </div>

        @if($payments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Tour Member</th>
                            <th class="py-3 px-4">Tour &amp; Expense</th>
                            <th class="py-3 px-4">Method</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4">Transaction / Reference</th>
                            <th class="py-3 px-4">Recorded By</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach($payments as $pay)
                            <tr class="hover:bg-surface-container-low/40 transition-colors">
                                <td class="py-3.5 px-4 text-secondary whitespace-nowrap">
                                    {{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('M d, Y') : ($pay->submitted_at ? \Carbon\Carbon::parse($pay->submitted_at)->format('M d, Y h:i A') : 'N/A') }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-on-surface">
                                    {{ $pay->share?->tourMember?->display_name ?? 'N/A' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-on-surface">{{ $pay->share?->expense?->tour?->tour_name ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-secondary">{{ $pay->share?->expense?->expense_name ?? 'N/A' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-secondary uppercase">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-surface-container text-[11px]">
                                        <span class="material-symbols-outlined text-[13px]">payments</span>
                                        {{ $pay->method }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-emerald-700">
                                    ৳{{ number_format($pay->amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px] text-outline">
                                    {{ $pay->transaction_id ?: 'Manual Receipt' }}
                                </td>
                                <td class="py-3.5 px-4 text-secondary">
                                    {{ $pay->receivedBy?->full_name ?? 'Manager' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
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

            @if($payments->hasPages())
                <div class="p-4 border-t border-outline-variant/20">
                    {{ $payments->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center text-secondary text-xs">
                No payment transactions found.
            </div>
        @endif
    </div>
</div>
@endsection
