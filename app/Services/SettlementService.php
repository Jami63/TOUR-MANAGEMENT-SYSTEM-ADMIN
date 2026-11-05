<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    /**
     * Calculate financial summary and individual settlement balances for a tour.
     *
     * Net Balance = Total Amount Paid − Equal Share of Actual Expenses.
     * - Positive: Member receives refund.
     * - Zero: Fully settled.
     * - Negative: Member still owes money.
     * - A member who paid nothing NEVER receives a refund.
     */
    public function calculateTourFinancials(Tour $tour): array
    {
        $tour->loadMissing(['members.user', 'expenses.shares.payments']);

        $members = $tour->members;
        $memberCount = $members->count();
        $totalActualExpenses = (float) $tour->expenses()->sum('total_amount');

        // Sum of all verified payments across all expenses for this tour
        $totalCollected = (float) Payment::whereHas('share.expense', function ($q) use ($tour) {
            $q->where('tour_id', $tour->id);
        })->where('status', 'verified')->sum('amount');

        $membersSettlement = [];
        $totalRefundable = 0.00;
        $totalOutstandingDue = 0.00;

        foreach ($members as $member) {
            // Total amount actually paid by this member for all tour expenses
            $totalPaid = (float) Payment::whereHas('share', function ($q) use ($member) {
                $q->where('tour_member_id', $member->id);
            })->where('status', 'verified')->sum('amount');

            // Total equal shares assigned to this member across all tour expenses
            $totalShare = (float) $member->expenseShares()->sum('share_amount');

            // Net balance = Total Amount Paid − Equal Share of Actual Expenses
            $netBalance = round($totalPaid - $totalShare, 2);

            $refundAmount = 0.00;
            $dueAmount = 0.00;
            $status = 'Settled';

            if ($netBalance > 0.0001) {
                // Member overpaid compared to actual equal expenses -> receives refund
                // Only if member actually paid money!
                $refundAmount = $netBalance;
                $totalRefundable += $refundAmount;
                $status = 'Refund Due';
            } elseif ($netBalance < -0.0001) {
                // Member underpaid -> owes money
                $dueAmount = abs($netBalance);
                $totalOutstandingDue += $dueAmount;
                $status = 'Payment Due';
            }

            // Existing refund record (if any)
            $existingRefund = Refund::where('tour_member_id', $member->id)->first();

            $membersSettlement[] = [
                'member_id' => $member->id,
                'name' => $member->display_name,
                'phone' => $member->display_phone,
                'email' => $member->display_email,
                'tour_role' => $member->tour_role,
                'total_paid' => $totalPaid,
                'total_share' => $totalShare,
                'net_balance' => $netBalance,
                'refund_amount' => $refundAmount,
                'due_amount' => $dueAmount,
                'settlement_status' => $status,
                'refund_record' => $existingRefund,
            ];
        }

        $remainingFundBalance = round($totalCollected - $totalActualExpenses, 2);

        return [
            'tour_id' => $tour->id,
            'tour_name' => $tour->tour_name,
            'status' => $tour->status,
            'finish_type' => $tour->finish_type,
            'finished_at' => $tour->finished_at,
            'estimated_budget' => (float) $tour->estimated_budget,
            'member_count' => $memberCount,
            'total_actual_expenses' => round($totalActualExpenses, 2),
            'total_collected_money' => round($totalCollected, 2),
            'remaining_fund_balance' => $remainingFundBalance,
            'total_refundable_amount' => round($totalRefundable, 2),
            'total_outstanding_due' => round($totalOutstandingDue, 2),
            'members' => $membersSettlement,
        ];
    }

    /**
     * Finish a tour normally.
     */
    public function finishTour(Tour $tour, User $manager, array $data = []): array
    {
        return DB::transaction(function () use ($tour, $data) {
            $financials = $this->calculateTourFinancials($tour);

            $tour->status = 'completed';
            $tour->finish_type = 'normal';
            $tour->finished_at = now();
            $tour->settlement_notes = $data['notes'] ?? null;
            $tour->total_collected = $financials['total_collected_money'];
            $tour->total_actual_expense = $financials['total_actual_expenses'];
            $tour->total_refund_amount = $financials['total_refundable_amount'];
            $tour->save();

            // Create or update refund records for members with positive net balance
            foreach ($financials['members'] as $item) {
                if ($item['refund_amount'] > 0) {
                    Refund::updateOrCreate(
                        ['tour_member_id' => $item['member_id']],
                        [
                            'tour_id' => $tour->id,
                            'total_paid' => $item['total_paid'],
                            'final_share' => $item['total_share'],
                            'adjustment_total' => 0.00,
                            'refund_amount' => $item['refund_amount'],
                            'status' => 'pending',
                        ]
                    );
                }
            }

            return $financials;
        });
    }

    /**
     * Finish a tour early due to emergency.
     */
    public function emergencyFinishTour(Tour $tour, User $manager, string $reason, ?string $notes = null): array
    {
        return DB::transaction(function () use ($tour, $reason, $notes) {
            $financials = $this->calculateTourFinancials($tour);

            $tour->status = 'completed';
            $tour->finish_type = 'emergency';
            $tour->finished_at = now();
            $tour->emergency_reason = $reason;
            $tour->settlement_notes = $notes;
            $tour->total_collected = $financials['total_collected_money'];
            $tour->total_actual_expense = $financials['total_actual_expenses'];
            $tour->total_refund_amount = $financials['total_refundable_amount'];
            $tour->save();

            // Create or update refund records for members with positive net balance
            foreach ($financials['members'] as $item) {
                if ($item['refund_amount'] > 0) {
                    Refund::updateOrCreate(
                        ['tour_member_id' => $item['member_id']],
                        [
                            'tour_id' => $tour->id,
                            'total_paid' => $item['total_paid'],
                            'final_share' => $item['total_share'],
                            'adjustment_total' => 0.00,
                            'refund_amount' => $item['refund_amount'],
                            'status' => 'pending',
                        ]
                    );
                }
            }

            return $financials;
        });
    }

    /**
     * Manually update refund status (Pending <-> Refunded).
     */
    public function updateRefundStatus(Refund $refund, string $status, ?string $method = null, ?User $manager = null, ?string $notes = null): Refund
    {
        $refund->status = $status;
        if ($status === 'refunded') {
            $refund->refund_date = now();
            $refund->method = $method ?? $refund->method ?? 'Cash';
            if ($manager) {
                $refund->refunded_by = $manager->id;
            }
        } else {
            $refund->refund_date = null;
        }

        if ($notes !== null) {
            $refund->notes = $notes;
        }

        $refund->save();

        return $refund;
    }
}
