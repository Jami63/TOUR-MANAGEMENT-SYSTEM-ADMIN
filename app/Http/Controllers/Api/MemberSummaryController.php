<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseShare;
use App\Models\Tour;
use App\Models\TourMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberSummaryController extends Controller
{
    /**
     * Get Member's complete tour expense ledger & dues
     */
    public function myTourSummary(Request $request, int $tourId): JsonResponse
    {
        $user = $request->user();

        $tour = Tour::find($tourId);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

        $tourMember = TourMember::where('tour_id', $tourId)
            ->where('user_id', $user->id)
            ->first();

        if (! $tourMember) {
            return response()->json(['success' => false, 'message' => 'You are not a member of this tour'], 403);
        }

        // Get all expense shares for this member
        $shares = ExpenseShare::where('tour_member_id', $tourMember->id)
            ->with(['expense.category', 'payments.verification'])
            ->get();

        $totalShare = $shares->sum('share_amount');

        $totalVerifiedPaid = 0;
        $totalPendingPaid = 0;

        $expenseBreakdown = [];

        foreach ($shares as $share) {
            $verifiedPaid = $share->payments->where('status', 'verified')->sum('amount');
            $pendingPaid = $share->payments->where('status', 'pending')->sum('amount');

            $totalVerifiedPaid += $verifiedPaid;
            $totalPendingPaid += $pendingPaid;

            $due = max(0, $share->share_amount - $verifiedPaid);

            $status = 'Pending';
            if ($due <= 0) {
                $status = 'Paid';
            } elseif ($verifiedPaid > 0) {
                $status = 'Partial';
            }

            $expenseBreakdown[] = [
                'share_id' => $share->id,
                'expense_id' => $share->expense_id,
                'expense_name' => $share->expense->expense_name,
                'category' => $share->expense->category?->category_name ?? 'General',
                'expense_date' => $share->expense->expense_date->format('Y-m-d'),
                'total_expense_amount' => (float) $share->expense->total_amount,
                'my_share_amount' => (float) $share->share_amount,
                'paid_amount' => (float) $verifiedPaid,
                'due_amount' => (float) $due,
                'status' => $status,
                'payments' => $share->payments->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'amount' => (float) $p->amount,
                        'method' => $p->method,
                        'transaction_id' => $p->transaction_id,
                        'status' => $p->status,
                        'submitted_at' => $p->submitted_at?->format('d M, Y h:i A'),
                        'reject_reason' => $p->verification?->reject_reason,
                    ];
                }),
            ];
        }

        $totalRemainingDue = max(0, $totalShare - $totalVerifiedPaid);

        return response()->json([
            'success' => true,
            'tour' => [
                'id' => $tour->id,
                'tour_name' => $tour->tour_name,
                'status' => $tour->status,
                'start_date' => $tour->start_date?->format('Y-m-d'),
                'end_date' => $tour->end_date?->format('Y-m-d'),
            ],
            'summary' => [
                'total_share' => (float) $totalShare,
                'total_paid' => (float) $totalVerifiedPaid,
                'total_pending' => (float) $totalPendingPaid,
                'remaining_due' => (float) $totalRemainingDue,
            ],
            'expenses' => $expenseBreakdown,
        ]);
    }
}
