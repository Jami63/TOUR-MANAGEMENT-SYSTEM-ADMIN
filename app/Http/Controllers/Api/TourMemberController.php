<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseShare;
use App\Models\Tour;
use App\Models\TourMember;
use App\Models\TourTimeline;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourMemberController extends Controller
{
    /**
     * List all participants of a specific tour with their individual financial stats
     */
    public function index(Request $request, int $tourId): JsonResponse
    {
        $tour = Tour::with(['creator'])->find($tourId);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

        $members = TourMember::where('tour_id', $tourId)
            ->with(['user', 'expenseShares.payments'])
            ->get();

        $participants = $members->map(function ($member) {
            $totalShare = (float) $member->expenseShares->sum('share_amount');

            // Total amount actually paid across all expense shares and advance payments
            $totalPaid = (float) $member->totalPaidAmount();

            $totalPending = (float) \App\Models\Payment::where(function ($q) use ($member) {
                $q->where('tour_member_id', $member->id)
                    ->orWhereHas('share', fn ($sq) => $sq->where('tour_member_id', $member->id));
            })->where('status', 'pending')->sum('amount');

            $remainingDue = max(0.0, round($totalShare - $totalPaid, 2));
            $netBalance = round($totalPaid - $totalShare, 2);

            $refundAmount = $netBalance > 0 ? $netBalance : 0.0;
            $dueAmount = $netBalance < 0 ? abs($netBalance) : 0.0;

            $settlementStatus = 'Due';
            if ($totalShare > 0 && $remainingDue <= 0.001) {
                $settlementStatus = 'Settled';
            } elseif ($totalPaid > 0 && $totalShare > 0) {
                $settlementStatus = 'Partial';
            } elseif ($totalPaid > 0 && $totalShare == 0) {
                $settlementStatus = 'Advance Paid';
            } elseif ($totalShare == 0) {
                $settlementStatus = 'Joined';
            }

            return [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'full_name' => $member->display_name,
                'email' => $member->display_email,
                'phone' => $member->display_phone,
                'profile_picture' => $member->user?->profile_picture,
                'tour_role' => $member->tour_role,
                'status' => $member->status,
                'joined_at' => $member->joined_at?->format('d M, Y') ?? $member->created_at?->format('d M, Y'),
                'total_share' => $totalShare,
                'total_paid' => $totalPaid,
                'total_pending' => $totalPending,
                'remaining_due' => $remainingDue,
                'net_balance' => $netBalance,
                'refund_amount' => $refundAmount,
                'due_amount' => $dueAmount,
                'settlement_status' => $settlementStatus,
            ];
        });

        return response()->json([
            'success' => true,
            'tour' => [
                'id' => $tour->id,
                'tour_name' => $tour->tour_name,
                'status' => $tour->status,
            ],
            'participants' => $participants,
            'total_participants' => $participants->count(),
        ]);
    }

    /**
     * Add a participant to the tour (Tour Manager only).
     * Tour members do NOT require registered user accounts!
     */
    public function store(Request $request, int $tourId): JsonResponse
    {
        $currentUser = $request->user();

        $tour = Tour::with(['expenses'])->find($tourId);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

        if ($tour->created_by !== $currentUser->id && $currentUser->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Only the Tour Manager can add participants'], 403);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'identifier' => ['nullable', 'string'],
        ]);

        $name = trim($validated['name'] ?? '');
        $phone = trim($validated['phone'] ?? '');
        $email = trim($validated['email'] ?? '');
        $identifier = trim($validated['identifier'] ?? '');

        $userId = null;

        // If identifier is provided, search if a registered user exists
        if (! empty($identifier)) {
            $matchedUser = User::where('email', $identifier)->orWhere('phone', $identifier)->first();
            if ($matchedUser) {
                $userId = $matchedUser->id;
                $name = $matchedUser->full_name;
                $phone = $matchedUser->phone ?? $phone;
                $email = $matchedUser->email;
            } elseif (empty($name)) {
                $name = $identifier;
            }
        }

        if (empty($name)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide the participant\'s name.',
            ], 422);
        }

        $newMember = DB::transaction(function () use ($tour, $userId, $name, $phone, $email, $currentUser) {
            $member = TourMember::create([
                'tour_id' => $tour->id,
                'user_id' => $userId,
                'name' => $name,
                'phone' => ! empty($phone) ? $phone : null,
                'email' => ! empty($email) ? $email : null,
                'tour_role' => 'member',
                'status' => 'joined',
                'joined_at' => now(),
            ]);

            // Rebalance equal expense shares for existing expenses
            $this->rebalanceTourExpenses($tour);

            TourTimeline::create([
                'tour_id' => $tour->id,
                'event_type' => 'member_joined',
                'description' => "{$name} was added to the tour by {$currentUser->full_name}",
                'created_by' => $currentUser->id,
            ]);

            return $member;
        });

        return response()->json([
            'success' => true,
            'message' => "{$name} added to expedition successfully",
            'participant' => [
                'id' => $newMember->id,
                'user_id' => $newMember->user_id,
                'full_name' => $newMember->display_name,
                'email' => $newMember->display_email,
                'phone' => $newMember->display_phone,
                'tour_role' => 'member',
                'status' => 'joined',
                'joined_at' => now()->format('d M, Y'),
                'total_share' => 0.0,
                'total_paid' => 0.0,
                'remaining_due' => 0.0,
                'settlement_status' => 'Settled',
            ],
        ], 201);
    }

    /**
     * Remove a participant from the tour (Tour Manager only).
     */
    public function destroy(Request $request, int $tourId, int $memberId): JsonResponse
    {
        $currentUser = $request->user();

        $tour = Tour::find($tourId);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

        if ($tour->created_by !== $currentUser->id && $currentUser->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $member = TourMember::where('tour_id', $tourId)->findOrFail($memberId);

        if ($member->tour_role === 'manager' || $member->user_id === $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'Tour Manager cannot be removed from the tour'], 422);
        }

        DB::transaction(function () use ($tour, $member) {
            $member->delete();
            $this->rebalanceTourExpenses($tour);
        });

        return response()->json([
            'success' => true,
            'message' => 'Participant removed and expense shares rebalanced',
        ]);
    }

    /**
     * Rebalance equal expense shares when members join or leave.
     */
    protected function rebalanceTourExpenses(Tour $tour): void
    {
        $tour->load(['members', 'expenses']);
        $members = $tour->members;
        $count = $members->count();

        if ($count === 0) {
            return;
        }

        foreach ($tour->expenses as $expense) {
            $baseShare = round($expense->total_amount / $count, 2);
            $calculatedSum = $baseShare * $count;
            $remainder = round($expense->total_amount - $calculatedSum, 2);

            foreach ($members as $index => $m) {
                $amount = ($index === 0) ? round($baseShare + $remainder, 2) : $baseShare;
                ExpenseShare::updateOrCreate(
                    [
                        'expense_id' => $expense->id,
                        'tour_member_id' => $m->id,
                    ],
                    [
                        'share_amount' => $amount,
                    ]
                );
            }
        }
    }

    /**
     * Search available registered users to add (optional)
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 2) {
            return response()->json(['success' => true, 'users' => []]);
        }

        $users = User::where('full_name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'full_name', 'email', 'phone', 'role']);

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Traveler Directory: Returns all unique travelers enrolled across tours
     * accessible to the authenticated user (Requirement: All tour participants visible in Directory).
     */
    public function directory(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        // Get all tours accessible to the user:
        // As a manager, tours created by this user or where they are a member.
        // For admin, all tours.
        $tourIds = Tour::query()
            ->when($currentUser->role !== 'admin', function ($q) use ($currentUser) {
                $q->where('created_by', $currentUser->id)
                    ->orWhereHas('members', fn ($mq) => $mq->where('user_id', $currentUser->id));
            })
            ->pluck('id');

        $members = TourMember::whereIn('tour_id', $tourIds)
            ->with(['user', 'expenseShares', 'payments'])
            ->get();

        // Group members by identity:
        // 1. By user_id if present
        // 2. By email if present
        // 3. By phone if present
        // 4. By lowercase name
        // 5. By member id
        $grouped = [];

        foreach ($members as $member) {
            $key = null;
            if ($member->user_id) {
                $key = 'user_'.$member->user_id;
            } elseif (! empty($member->email)) {
                $key = 'email_'.strtolower(trim($member->email));
            } elseif (! empty($member->phone)) {
                $key = 'phone_'.trim($member->phone);
            } elseif (! empty($member->name)) {
                $key = 'name_'.strtolower(trim($member->name));
            } else {
                $key = 'member_'.$member->id;
            }

            $totalShare = (float) $member->expenseShares->sum('share_amount');
            $totalPaid = (float) $member->totalPaidAmount();
            $totalPending = (float) \App\Models\Payment::where(function ($q) use ($member) {
                $q->where('tour_member_id', $member->id)
                    ->orWhereHas('share', fn ($sq) => $sq->where('tour_member_id', $member->id));
            })->where('status', 'pending')->sum('amount');

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'id' => $member->id,
                    'user_id' => $member->user_id ?? 0,
                    'full_name' => $member->display_name,
                    'email' => $member->display_email ?? '',
                    'phone' => $member->display_phone ?? '',
                    'profile_picture' => $member->user?->profile_picture,
                    'tour_role' => $member->tour_role,
                    'status' => $member->status,
                    'joined_at' => $member->joined_at?->format('d M, Y') ?? $member->created_at?->format('d M, Y'),
                    'total_share' => 0.0,
                    'total_paid' => 0.0,
                    'total_pending' => 0.0,
                    'tours_count' => 0,
                ];
            }

            $grouped[$key]['total_share'] += $totalShare;
            $grouped[$key]['total_paid'] += $totalPaid;
            $grouped[$key]['total_pending'] += $totalPending;
            $grouped[$key]['tours_count'] += 1;

            if ($member->tour_role === 'manager') {
                $grouped[$key]['tour_role'] = 'manager';
            }
        }

        $travelers = array_values(array_map(function ($item) {
            $remainingDue = max(0.0, round($item['total_share'] - $item['total_paid'], 2));
            $netBalance = round($item['total_paid'] - $item['total_share'], 2);
            $refundAmount = $netBalance > 0 ? $netBalance : 0.0;
            $dueAmount = $netBalance < 0 ? abs($netBalance) : 0.0;

            $settlementStatus = 'Due';
            if ($item['total_share'] > 0 && $remainingDue <= 0.001) {
                $settlementStatus = 'Settled';
            } elseif ($item['total_paid'] > 0 && $item['total_share'] > 0) {
                $settlementStatus = 'Partial';
            } elseif ($item['total_paid'] > 0 && $item['total_share'] == 0) {
                $settlementStatus = 'Advance Paid';
            } elseif ($item['total_share'] == 0) {
                $settlementStatus = 'Joined';
            }

            $item['remaining_due'] = $remainingDue;
            $item['net_balance'] = $netBalance;
            $item['refund_amount'] = $refundAmount;
            $item['due_amount'] = $dueAmount;
            $item['settlement_status'] = $settlementStatus;

            return $item;
        }, $grouped));

        return response()->json([
            'success' => true,
            'travelers' => $travelers,
            'total_travelers' => count($travelers),
        ]);
    }
}
