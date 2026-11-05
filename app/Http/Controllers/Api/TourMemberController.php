<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourMember;
use App\Models\TourTimeline;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            $totalShare = $member->expenseShares->sum('share_amount');

            $totalPaid = 0;
            $totalPending = 0;

            foreach ($member->expenseShares as $share) {
                $totalPaid += $share->payments->where('status', 'verified')->sum('amount');
                $totalPending += $share->payments->where('status', 'pending')->sum('amount');
            }

            $remainingDue = max(0, $totalShare - $totalPaid);

            $settlementStatus = 'Due';
            if ($totalShare > 0 && $remainingDue <= 0) {
                $settlementStatus = 'Settled';
            } elseif ($totalPaid > 0) {
                $settlementStatus = 'Partial';
            } elseif ($totalShare == 0) {
                $settlementStatus = 'No Expenses';
            }

            return [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'full_name' => $member->user?->full_name ?? 'Traveler',
                'email' => $member->user?->email ?? '',
                'phone' => $member->user?->phone ?? '',
                'profile_picture' => $member->user?->profile_picture,
                'tour_role' => $member->tour_role,
                'status' => $member->status,
                'joined_at' => $member->joined_at?->format('d M, Y') ?? $member->created_at?->format('d M, Y'),
                'total_share' => (float) $totalShare,
                'total_paid' => (float) $totalPaid,
                'total_pending' => (float) $totalPending,
                'remaining_due' => (float) $remainingDue,
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
     * Add a participant to the tour (Tour Manager only)
     */
    public function store(Request $request, int $tourId): JsonResponse
    {
        $currentUser = $request->user();

        $tour = Tour::find($tourId);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

        // Only manager can add members
        if ($tour->created_by !== $currentUser->id && $currentUser->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Only the Tour Manager can add participants'], 403);
        }

        $validated = $request->validate([
            'identifier' => ['required', 'string'], // phone or email
        ]);

        $identifier = trim($validated['identifier']);

        // Find user by email or phone
        $userToAdd = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->orWhere('phone', 'like', "%{$identifier}%")
            ->first();

        if (! $userToAdd) {
            return response()->json([
                'success' => false,
                'message' => "No user found matching '{$identifier}'. Please ask them to register on SafarFlow first.",
            ], 404);
        }

        // Check if already in tour
        $existing = TourMember::where('tour_id', $tourId)
            ->where('user_id', $userToAdd->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => "{$userToAdd->full_name} is already a participant of this tour.",
            ], 422);
        }

        $newMember = TourMember::create([
            'tour_id' => $tourId,
            'user_id' => $userToAdd->id,
            'tour_role' => 'member',
            'status' => 'joined',
            'joined_at' => now(),
        ]);

        // Add timeline record
        TourTimeline::create([
            'tour_id' => $tourId,
            'event_type' => 'member_joined',
            'description' => "{$userToAdd->full_name} was added to the expedition by {$currentUser->full_name}",
            'created_by' => $currentUser->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$userToAdd->full_name} added to expedition successfully",
            'participant' => [
                'id' => $newMember->id,
                'user_id' => $userToAdd->id,
                'full_name' => $userToAdd->full_name,
                'email' => $userToAdd->email,
                'phone' => $userToAdd->phone ?? '',
                'tour_role' => 'member',
                'status' => 'joined',
                'joined_at' => now()->format('d M, Y'),
                'total_share' => 0.0,
                'total_paid' => 0.0,
                'remaining_due' => 0.0,
                'settlement_status' => 'No Expenses',
            ],
        ], 201);
    }

    /**
     * Search available registered users to add
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
}
