@extends('layouts.admin', ['title' => 'Master Expenses Audit – SafarFlow Super Admin'])

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-6 lg:p-7 shadow-sm border border-outline-variant/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="material-symbols-outlined text-primary text-[20px]">receipt_long</span>
                <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Financial Audit</span>
            </div>
            <h1 class="font-display text-2xl lg:text-3xl font-extrabold text-on-surface">
                Master Expenses Audit
            </h1>
            <p class="text-xs sm:text-sm text-secondary mt-1">
                Audit trail of every expense logged by Tour Managers across all tours with equal share division monitoring.
            </p>
        </div>
        <div>
            <div class="bg-surface-container px-4 py-2.5 rounded-xl border border-outline-variant/30 flex items-center gap-3">
                <span class="material-symbols-outlined text-primary text-[24px]">account_balance</span>
                <div>
                    <span class="text-[10px] text-secondary uppercase tracking-wider block font-semibold">Total Platform Expenses</span>
                    <span class="font-display text-lg font-extrabold text-[#c44f32]">৳{{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Search / Filter -->
    <div class="bg-white p-4 rounded-xl border border-outline-variant/30 flex flex-wrap items-center justify-between gap-3 shadow-sm">
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search expense or tour name…"
                    class="h-9 pl-9 pr-3.5 rounded-lg bg-surface-container-low text-xs border border-outline-variant/30 focus:outline-none focus:ring-1 focus:ring-primary w-72">
            </div>

            <button type="submit" class="h-9 px-3.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary-container transition-colors">
                Search
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.expenses.index') }}" class="text-xs text-secondary hover:underline">Clear Search</a>
            @endif
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden">
        <div class="p-4 border-b border-outline-variant/20 flex items-center justify-between">
            <h2 class="font-display text-base font-bold text-on-surface">All Recorded Expenses</h2>
            <span class="text-xs text-secondary">{{ $expenses->total() }} records</span>
        </div>

        @if($expenses->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-surface-container-low text-secondary uppercase text-[10px] tracking-wider border-b border-outline-variant/20">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Expense Details</th>
                            <th class="py-3 px-4">Tour Expedition</th>
                            <th class="py-3 px-4">Tour Manager</th>
                            <th class="py-3 px-4 text-right">Total Amount</th>
                            <th class="py-3 px-4 text-center">Equal Splits</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach($expenses as $expense)
                            <tr class="hover:bg-surface-container-low/40 transition-colors">
                                <td class="py-3.5 px-4 text-secondary whitespace-nowrap">
                                    {{ $expense->expense_date ? \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-on-surface text-sm">{{ $expense->expense_name }}</div>
                                    <div class="text-[11px] text-secondary flex items-center gap-1.5 mt-0.5">
                                        <span class="px-2 py-0.2 rounded-full bg-surface-container text-secondary text-[10px]">{{ $expense->category?->name ?? 'General' }}</span>
                                        @if($expense->notes)
                                            <span class="text-outline truncate max-w-[200px]">&bull; {{ $expense->notes }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-on-surface">{{ $expense->tour?->tour_name ?? 'N/A' }}</span>
                                    <div class="text-[10px] text-outline">{{ $expense->tour?->start_location ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-secondary">
                                    {{ $expense->tour?->creator?->full_name ?? 'N/A' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-[#c44f32]">
                                    ৳{{ number_format($expense->total_amount, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-container/30 text-secondary text-[11px] font-semibold">
                                        {{ $expense->shares->count() }} member shares
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($expense->tour_id)
                                        <a href="{{ route('admin.tours.show', $expense->tour_id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-surface-container text-primary font-bold text-xs hover:bg-primary hover:text-white transition-all">
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

            @if($expenses->hasPages())
                <div class="p-4 border-t border-outline-variant/20">
                    {{ $expenses->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center text-secondary text-xs">
                No expense records found.
            </div>
        @endif
    </div>
</div>
@endsection
