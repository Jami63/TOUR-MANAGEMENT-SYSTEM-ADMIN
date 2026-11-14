<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseShare;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Tour;
use App\Models\TourDestination;
use App\Models\TourMember;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TourController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService
    ) {}

    /**
     * Tour Manager Dashboard & Tours Overview.
     */
    public function index(Request $request): View
    {
        $manager = Auth::user();

        $query = Tour::where('created_by', $manager->id)
            ->with(['destinations', 'members']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tour_name', 'like', "%{$search}%")
                    ->orWhere('start_location', 'like', "%{$search}%");
            });
        }

        $tours = $query->latest()->paginate(10);

        // Manager Summary Statistics
        $allTours = Tour::where('created_by', $manager->id)->get();
        $totalTours = $allTours->count();
        $activeTours = $allTours->whereIn('status', ['upcoming', 'ongoing'])->count();
        $completedTours = $allTours->where('status', 'completed')->count();
        $totalBudgetSum = $allTours->sum('estimated_budget');

        return view('manager.tours.index', compact(
            'manager',
            'tours',
            'totalTours',
            'activeTours',
            'completedTours',
            'totalBudgetSum'
        ));
    }

    /**
     * Show Create Tour Form.
     */
    public function create(): View
    {
        return view('manager.tours.create');
    }

    /**
     * Store a newly created Tour.
     * Automatically includes the Tour Manager as the first member!
     */
    public function store(Request $request): RedirectResponse
    {
        $manager = Auth::user();

        $validated = $request->validate([
            'tour_name' => ['required', 'string', 'max:255'],
            'start_location' => ['required', 'string', 'max:255'],
            'destinations' => ['required'], // String (comma/newline separated) or Array
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'estimated_budget' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($manager, $validated) {
            $tour = Tour::create([
                'created_by' => $manager->id,
                'tour_name' => $validated['tour_name'],
                'start_location' => $validated['start_location'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'estimated_budget' => $validated['estimated_budget'],
                'description' => $validated['description'] ?? null,
                'status' => 'upcoming',
            ]);

            // Parse and add destinations (handles array or string)
            $destArray = is_array($validated['destinations'])
                ? $validated['destinations']
                : preg_split('/[,\n]+/', (string) $validated['destinations']);

            $order = 1;
            foreach ($destArray as $dest) {
                $trimmed = trim((string) $dest);
                if (! empty($trimmed)) {
                    TourDestination::create([
                        'tour_id' => $tour->id,
                        'destination_name' => $trimmed,
                        'visit_order' => $order++,
                    ]);
                }
            }

            // Automatically add the Tour Manager himself as Member #1 (Requirement 2)
            TourMember::create([
                'tour_id' => $tour->id,
                'user_id' => $manager->id,
                'name' => $manager->full_name,
                'phone' => $manager->phone,
                'email' => $manager->email,
                'tour_role' => 'manager',
                'status' => 'joined',
                'joined_at' => now(),
            ]);
        });

        return redirect()->route('manager.dashboard')->with('success', 'Tour created successfully! You are added as the primary Tour Manager.');
    }

    /**
     * Tour Management Workspace.
     */
    public function show(int $id): View|RedirectResponse
    {
        $manager = Auth::user();
        $tour = Tour::where('created_by', $manager->id)
            ->with([
                'destinations',
                'members.user',
                'expenses.category',
                'expenses.shares.tourMember',
                'expenses.shares.payments.receivedBy',
                'refunds.tourMember',
            ])
            ->findOrFail($id);

        $financials = $this->settlementService->calculateTourFinancials($tour);
        $expenseCategories = ExpenseCategory::all();

        return view('manager.tours.show', compact('tour', 'financials', 'expenseCategories'));
    }

    /**
     * Add a member to the tour (Manual Member, no app account required).
     */
    public function addMember(Request $request, int $id): RedirectResponse
    {
        $manager = Auth::user();
        $tour = Tour::where('created_by', $manager->id)->findOrFail($id);

        if ($tour->isFinished()) {
            return back()->with('error', 'Cannot add members to a completed or finished tour.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tour_role' => ['nullable', 'in:member,manager'],
        ]);

        DB::transaction(function () use ($tour, $validated) {
            $member = TourMember::create([
                'tour_id' => $tour->id,
                'user_id' => null, // No login/app account needed!
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'tour_role' => $validated['tour_role'] ?? 'member',
                'status' => 'joined',
                'joined_at' => now(),
            ]);

            // Rebalance equal shares for existing expenses so every expense remains equally split
            $this->rebalanceTourExpenses($tour);
        });

        return back()->with('success', "Member '{$validated['name']}' added successfully! Expenses have been equally balanced.");
    }

    /**
     * Remove a member from the tour (if they haven't made payments).
     */
    public function removeMember(int $tourId, int $memberId): RedirectResponse
    {
        $manager = Auth::user();
        $tour = Tour::where('created_by', $manager->id)->findOrFail($tourId);

        if ($tour->isFinished()) {
            return back()->with('error', 'Cannot remove members from a finished tour.');
        }

        $member = TourMember::where('tour_id', $tour->id)->findOrFail($memberId);

        if ($member->tour_role === 'manager' && $member->user_id === $manager->id) {
            return back()->with('error', 'The primary Tour Manager cannot be removed from the tour.');
        }

        // Check if member made payments
        $paid = $member->totalPaidAmount();
        if ($paid > 0) {
            return back()->with('error', "Cannot remove member '{$member->display_name}' because ৳{$paid} in payments has already been recorded for them.");
        }

        DB::transaction(function () use ($tour, $member) {
            $member->delete();
            $this->rebalanceTourExpenses($tour);
        });

        return back()->with('success', "Member '{$member->display_name}' was removed and expenses rebalanced.");
    }

    /**
     * Add an Expense with Equal Splitting among all members (Requirement 3).
     */
    public function addExpense(Request $request, int $id): RedirectResponse
    {
        $manager = Auth::user();
        $tour = Tour::where('created_by', $manager->id)->with('members')->findOrFail($id);

        if ($tour->isFinished()) {
            return back()->with('error', 'Cannot add expenses to a finished tour.');
        }

        $validated = $request->validate([
            'expense_name' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'category_name' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $members = $tour->members;
        $memberCount = $members->count();

        if ($memberCount === 0) {
            return back()->with('error', 'Cannot add expense: Tour has 0 members.');
        }

        DB::transaction(function () use ($tour, $manager, $validated, $members, $memberCount) {
            $categoryId = null;
            if (! empty($validated['category_name'])) {
                $category = ExpenseCategory::firstOrCreate(
                    ['category_name' => $validated['category_name']],
                    ['created_by' => $manager->id]
                );
                $categoryId = $category->id;
            }

            $expense = Expense::create([
                'tour_id' => $tour->id,
                'category_id' => $categoryId,
                'added_by' => $manager->id,
                'expense_name' => $validated['expense_name'],
                'total_amount' => $validated['total_amount'],
                'expense_date' => $validated['expense_date'],
                'description' => $validated['description'] ?? null,
            ]);

            // Equal Splitting calculation with remainder handling to 100% precision
            $baseShare = round($validated['total_amount'] / $memberCount, 2);
            $totalCalculated = $baseShare * $memberCount;
            $remainder = round($validated['total_amount'] - $totalCalculated, 2);

            foreach ($members as $index => $member) {
                $shareAmount = ($index === 0) ? round($baseShare + $remainder, 2) : $baseShare;

                ExpenseShare::create([
                    'expense_id' => $expense->id,
                    'tour_member_id' => $member->id,
                    'share_amount' => $shareAmount,
                ]);
            }
        });

        $eachShare = round($validated['total_amount'] / $memberCount, 2);

        return back()->with('success', "Expense '{$validated['expense_name']}' of ৳{$validated['total_amount']} recorded! Divided equally into ৳{$eachShare} per member for all {$memberCount} members.");
    }

    /**
     * Manual Payment Recording (Requirement 4).
     * Tour Manager manually records payments after actually receiving money.
     */
    public function recordPayment(Request $request, int $tourId, int $expenseId): RedirectResponse
    {
        $manager = Auth::user();
        $tour = Tour::where('created_by', $manager->id)->findOrFail($tourId);
        $expense = Expense::where('tour_id', $tour->id)->findOrFail($expenseId);

        $validated = $request->validate([
            'tour_member_id' => ['required', 'exists:tour_members,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'max:50'],
            'payment_date' => ['nullable', 'date'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $share = ExpenseShare::where('expense_id', $expense->id)
            ->where('tour_member_id', $validated['tour_member_id'])
            ->firstOrFail();

        Payment::create([
            'tour_id' => $tour->id,
            'tour_member_id' => $validated['tour_member_id'],
            'share_id' => $share->id,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
            'transaction_id' => $validated['transaction_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'verified', // Manually entered by Manager who received the money!
            'received_by' => $manager->id,
            'submitted_at' => now(),
        ]);

        $tour->update([
            'total_collected' => $tour->totalCollectedMoney(),
        ]);

        $member = TourMember::find($validated['tour_member_id']);
        $memberName = $member ? $member->display_name : 'Member';
        $newDue = $share->remainingDue();
        $newStatus = $share->paymentStatus();

        return back()->with('success', "Payment of ৳{$validated['amount']} via {$validated['method']} recorded for {$memberName}. Status: {$newStatus} (Remaining Due: ৳{$newDue}).");
    }

    /**
     * Record Advance / Direct Payment for Tour Member before or independent of expenses.
     */
    public function recordAdvancePayment(Request $request, int $tourId): RedirectResponse
    {
        $manager = Auth::user();
        $tour = Tour::where('created_by', $manager->id)->findOrFail($tourId);

        if ($tour->isFinished()) {
            return back()->with('error', 'Cannot record payments for a finished tour.');
        }

        $validated = $request->validate([
            'tour_member_id' => ['required', 'exists:tour_members,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'max:50'],
            'payment_date' => ['nullable', 'date'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $member = TourMember::where('tour_id', $tour->id)->findOrFail($validated['tour_member_id']);

        Payment::create([
            'tour_id' => $tour->id,
            'tour_member_id' => $member->id,
            'share_id' => null,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
            'transaction_id' => $validated['transaction_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'verified',
            'received_by' => $manager->id,
            'submitted_at' => now(),
        ]);

        $tour->update([
            'total_collected' => $tour->totalCollectedMoney(),
        ]);

        return back()->with('success', "Advance payment of ৳{$validated['amount']} via {$validated['method']} recorded for {$member->display_name}.");
    }

    /**
     * Finish Tour (Requirement 5).
     */
    public function finishTour(Request $request, int $id): RedirectResponse
    {
        $manager = Auth::user();
        $tour = Tour::where('created_by', $manager->id)->findOrFail($id);

        if ($tour->isFinished()) {
            return back()->with('error', 'This tour has already been finished.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->settlementService->finishTour($tour, $manager, $validated);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Tour '{$tour->tour_name}' has been successfully completed! Final settlement report and refund records generated.");
    }

    /**
     * Manually Update Refund Status (Requirement 7).
     * Tour Manager updates status after actually returning money (Pending <-> Refunded).
     */
    public function updateRefundStatus(Request $request, int $refundId): RedirectResponse
    {
        $manager = Auth::user();
        $refund = Refund::findOrFail($refundId);

        $tour = $refund->tour;
        if (! $tour || $tour->created_by !== $manager->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,refunded'],
            'method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->settlementService->updateRefundStatus(
            $refund,
            $validated['status'],
            $validated['method'] ?? 'Cash',
            $manager,
            $validated['notes'] ?? null
        );

        $memberName = $refund->tourMember?->display_name ?? 'Member';
        $statusLabel = ucfirst($validated['status']);

        return back()->with('success', "Refund of ৳{$refund->refund_amount} for {$memberName} marked as {$statusLabel}.");
    }

    /**
     * Helper to rebalance equal expense shares when members are added or removed.
     */
    protected function rebalanceTourExpenses(Tour $tour): void
    {
        $members = $tour->members;
        $memberCount = $members->count();

        if ($memberCount === 0) {
            return;
        }

        foreach ($tour->expenses as $expense) {
            // Delete existing shares that have no payments
            $sharesWithPayments = ExpenseShare::where('expense_id', $expense->id)
                ->whereHas('payments')
                ->pluck('tour_member_id')
                ->toArray();

            // Equal base share
            $baseShare = round($expense->total_amount / $memberCount, 2);
            $totalCalculated = $baseShare * $memberCount;
            $remainder = round($expense->total_amount - $totalCalculated, 2);

            foreach ($members as $index => $member) {
                $shareAmount = ($index === 0) ? round($baseShare + $remainder, 2) : $baseShare;

                ExpenseShare::updateOrCreate(
                    [
                        'expense_id' => $expense->id,
                        'tour_member_id' => $member->id,
                    ],
                    [
                        'share_amount' => $shareAmount,
                    ]
                );
            }
        }
    }
}
