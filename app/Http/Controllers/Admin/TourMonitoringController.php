<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Tour;
use App\Models\User;
use App\Services\SettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TourMonitoringController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService
    ) {}

    /**
     * Super Admin Tour Monitoring List.
     */
    public function index(Request $request): View
    {
        $superAdmin = Auth::user();

        $query = Tour::with(['creator', 'destinations', 'members']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('manager_id')) {
            $query->where('created_by', $request->manager_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tour_name', 'like', "%{$search}%")
                    ->orWhere('start_location', 'like', "%{$search}%")
                    ->orWhereHas('creator', function ($cq) use ($search) {
                        $cq->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $tours = $query->latest()->paginate(12);

        $managers = User::where('role', 'manager')->get(['id', 'full_name', 'email']);

        // Monitoring Summary Totals
        $totalTours = Tour::count();
        $activeTours = Tour::whereIn('status', ['upcoming', 'ongoing'])->count();
        $completedTours = Tour::where('status', 'completed')->count();

        return view('admin.tours.index', compact(
            'superAdmin',
            'tours',
            'managers',
            'totalTours',
            'activeTours',
            'completedTours'
        ));
    }

    /**
     * Super Admin Tour Detailed Monitoring (Strictly Read-Only).
     */
    public function show(int $id): View
    {
        $superAdmin = Auth::user();

        $tour = Tour::with([
            'creator',
            'destinations',
            'members.user',
            'expenses.category',
            'expenses.shares.tourMember',
            'expenses.shares.payments.receivedBy',
            'refunds.tourMember',
            'refunds.refundedBy',
        ])->findOrFail($id);

        $financials = $this->settlementService->calculateTourFinancials($tour);

        return view('admin.tours.show', compact('superAdmin', 'tour', 'financials'));
    }

    /**
     * Master Expenses Monitoring across all tours.
     */
    public function allExpenses(Request $request): View
    {
        $superAdmin = Auth::user();

        $query = Expense::with(['tour.creator', 'category', 'shares.tourMember', 'shares.payments']);

        if ($request->filled('tour_id')) {
            $query->where('tour_id', $request->tour_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_name', 'like', "%{$search}%")
                    ->orWhereHas('tour', fn ($tq) => $tq->where('tour_name', 'like', "%{$search}%"));
            });
        }

        $expenses = $query->latest('expense_date')->paginate(15);
        $totalAmount = Expense::sum('total_amount');

        return view('admin.financials.expenses', compact('superAdmin', 'expenses', 'totalAmount'));
    }

    /**
     * Master Payments Monitoring across all tours.
     */
    public function allPayments(Request $request): View
    {
        $superAdmin = Auth::user();

        $query = Payment::with(['share.expense.tour', 'share.tourMember', 'receivedBy']);

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('share.tourMember', fn ($mq) => $mq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('share.expense.tour', fn ($tq) => $tq->where('tour_name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->latest('submitted_at')->paginate(15);
        $totalCollected = Payment::where('status', 'verified')->sum('amount');

        return view('admin.financials.payments', compact('superAdmin', 'payments', 'totalCollected'));
    }

    /**
     * Master Refunds Monitoring across all tours.
     */
    public function allRefunds(Request $request): View
    {
        $superAdmin = Auth::user();

        $query = Refund::with(['tour.creator', 'tourMember', 'refundedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $refunds = $query->latest()->paginate(15);
        $totalRefundable = Refund::sum('refund_amount');
        $totalRefunded = Refund::where('status', 'refunded')->sum('refund_amount');
        $totalPendingRefunds = Refund::where('status', 'pending')->sum('refund_amount');

        return view('admin.financials.refunds', compact(
            'superAdmin',
            'refunds',
            'totalRefundable',
            'totalRefunded',
            'totalPendingRefunds'
        ));
    }
}
