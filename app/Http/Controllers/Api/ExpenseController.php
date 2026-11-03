<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseShare;
use App\Models\Tour;
use App\Models\TourTimeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * List all expenses for a tour
     */
    public function index(int $tourId): JsonResponse
    {
        $expenses = Expense::where('tour_id', $tourId)
            ->with(['category', 'addedBy', 'shares.tourMember.user'])
            ->latest('expense_date')
            ->get();

        return response()->json([
            'success' => true,
            'expenses' => $expenses,
        ]);
    }

    /**
     * Add expense and auto-split among members
     */
    public function store(Request $request, int $tourId): JsonResponse
    {
        $user = $request->user();

        $tour = Tour::with('members')->find($tourId);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

        $validated = $request->validate([
            'expense_name' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'category_name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'receipt_file' => ['nullable', 'image', 'max:5120'], // 5MB max
        ]);

        // Category
        $categoryId = null;
        if (! empty($validated['category_name'])) {
            $cat = ExpenseCategory::firstOrCreate(
                ['category_name' => $validated['category_name']],
                ['created_by' => $user->id]
            );
            $categoryId = $cat->id;
        }

        // Receipt file upload
        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('receipts', 'public');
        }

        $expense = Expense::create([
            'tour_id' => $tour->id,
            'category_id' => $categoryId,
            'added_by' => $user->id,
            'expense_name' => $validated['expense_name'],
            'total_amount' => $validated['total_amount'],
            'expense_date' => $validated['expense_date'],
            'description' => $validated['description'] ?? null,
            'receipt_file' => $receiptPath,
        ]);

        // Auto Equal Splitting Calculation (as per Chapter 10 of documentation)
        $members = $tour->members;
        $memberCount = $members->count();

        if ($memberCount > 0) {
            $baseShare = round($validated['total_amount'] / $memberCount, 2);
            $totalCalculated = $baseShare * $memberCount;
            $remainder = round($validated['total_amount'] - $totalCalculated, 2);

            foreach ($members as $index => $member) {
                // Attach the remainder (if any) to the first member so total shares sum up 100% accurately
                $shareAmount = ($index === 0) ? ($baseShare + $remainder) : $baseShare;

                ExpenseShare::create([
                    'expense_id' => $expense->id,
                    'tour_member_id' => $member->id,
                    'share_amount' => $shareAmount,
                ]);
            }
        }

        // Timeline event
        TourTimeline::create([
            'tour_id' => $tour->id,
            'event_type' => 'expense_added',
            'description' => "Expense '{$expense->expense_name}' of ৳{$expense->total_amount} added and split",
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense added and split successfully',
            'expense' => $expense->load(['shares.tourMember.user', 'category']),
        ], 201);
    }
}
