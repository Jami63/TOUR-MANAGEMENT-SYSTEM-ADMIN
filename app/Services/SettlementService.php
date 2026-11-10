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
     * Refund Amount = Total Tour Budget − Total Actual Expenses.
     * Example: Budget = 100,000, Expenses = 50,000, Refund = 50,000.
     */
    public function calculateTourFinancials(Tour $tour): array
    {
        $tour->loadMissing(['members.user', 'expenses.shares.payments']);

        $members = $tour->members;
        $memberCount = $members->count();
        $budget = (float) $tour->estimated_budget;
        $totalActualExpenses = (float) $tour->expenses()->sum('total_amount');

        // Sum of all verified payments across all expenses and advance payments for this tour
        $totalCollected = (float) $tour->totalCollectedMoney();

        // AUTOMATIC REFUND CALCULATION RULE:
        // When actual expenses occurred: Refund Amount = Total Tour Budget − Total Actual Expenses
        // When zero expenses occurred: Refund Amount = Total Collected Money (return deposited funds to payers)
        if ($totalActualExpenses > 0) {
            $totalRefundAmount = max(0.00, round($budget - $totalActualExpenses, 2));
            $perMemberRefund = $memberCount > 0 ? round($totalRefundAmount / $memberCount, 2) : 0.00;
        } else {
            $totalRefundAmount = round($totalCollected, 2);
            $perMemberRefund = 0.00;
        }

        $totalOutstandingDue = 0.00;
        $membersSettlement = [];

        foreach ($members as $member) {
            $totalPaid = (float) $member->totalPaidAmount();
            $totalShare = (float) $member->expenseShares()->sum('share_amount');
            $netBalance = round($totalPaid - $totalShare, 2);

            // A member has an outstanding due if their paid amount is less than their required expense share
            $remainingDue = max(0.00, round($totalShare - $totalPaid, 2));

            if ($remainingDue > 0.001) {
                $dueAmount = $remainingDue;
                $refundAmount = 0.00;
                $status = 'Payment Due';
                $totalOutstandingDue += $dueAmount;
            } else {
                $dueAmount = 0.00;
                if ($totalActualExpenses <= 0) {
                    $refundAmount = $totalPaid;
                } else {
                    $refundAmount = $perMemberRefund;
                }
                $status = $refundAmount > 0.0001 ? 'Refund Due' : 'Settled';
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
                'remaining_due' => $dueAmount,
                'refund_amount' => $refundAmount,
                'due_amount' => $dueAmount,
                'settlement_status' => $status,
                'refund_record' => $existingRefund,
            ];
        }

        $remainingFundBalance = $totalRefundAmount;

        return [
            'tour_id' => $tour->id,
            'tour_name' => $tour->tour_name,
            'status' => $tour->status,
            'finish_type' => $tour->finish_type,
            'finished_at' => $tour->finished_at,
            'estimated_budget' => $budget,
            'member_count' => $memberCount,
            'total_actual_expenses' => round($totalActualExpenses, 2),
            'total_collected_money' => round($totalCollected, 2),
            'remaining_fund_balance' => $remainingFundBalance,
            'total_refundable_amount' => $totalRefundAmount,
            'refund_amount' => $totalRefundAmount,
            'total_outstanding_due' => round($totalOutstandingDue, 2),
            'has_outstanding_due' => $totalOutstandingDue > 0.001,
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

            // Validation: Prevent finishing the tour if any participant has an outstanding payment
            if (($financials['total_outstanding_due'] ?? 0) > 0.001) {
                $unpaid = [];
                foreach ($financials['members'] as $m) {
                    if (($m['due_amount'] ?? 0) > 0.001) {
                        $unpaid[] = "{$m['name']} (৳" . number_format($m['due_amount'], 2) . " due)";
                    }
                }
                $listStr = implode(', ', $unpaid);
                throw new \DomainException("Cannot finish tour. The following participant(s) have outstanding payments: {$listStr}. All outstanding payments must be collected and recorded before finishing.");
            }

            $tour->status = 'completed';
            $tour->finish_type = 'normal';
            $tour->finished_at = now();
            $tour->settlement_notes = $data['notes'] ?? $data['settlement_notes'] ?? null;
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
