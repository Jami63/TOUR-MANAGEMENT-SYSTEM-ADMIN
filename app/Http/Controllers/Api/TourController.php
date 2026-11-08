<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Tour;
use App\Models\TourDestination;
use App\Models\TourMember;
use App\Models\TourTimeline;
use App\Services\SettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourController extends Controller
{
    /**
     * List tours accessible to the user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'manager') {
            $tours = Tour::where('created_by', $user->id)
                ->with(['destinations', 'members.user'])
                ->latest()
                ->get();
        } else {
            $tours = Tour::whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with(['destinations', 'creator'])
                ->latest()
                ->get();
        }

        foreach ($tours as $tour) {
            $tour->syncDynamicStatus();
        }

        return response()->json([
            'success' => true,
            'tours' => $tours,
        ]);
    }

    /**
     * Create a new tour (Tour Manager)
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'manager') {
            return response()->json(['success' => false, 'message' => 'Only Tour Managers can create tours'], 403);
        }

        $validated = $request->validate([
            'tour_name' => ['required', 'string', 'max:255'],
            'start_location' => ['required', 'string', 'max:255'],
            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*' => ['string'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'estimated_budget' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ], [
            'start_date.after_or_equal' => 'Tour start date cannot be in the past. Please choose today or a future date.',
            'end_date.after_or_equal' => 'Tour end date must be on or after the start date.',
        ]);

        $today = now()->startOfDay();
        $startDate = \Carbon\Carbon::parse($validated['start_date'])->startOfDay();
        $initialStatus = $startDate->equalTo($today) ? 'ongoing' : 'upcoming';

        $tour = Tour::create([
            'created_by' => $user->id,
            'tour_name' => $validated['tour_name'],
            'start_location' => $validated['start_location'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'estimated_budget' => $validated['estimated_budget'],
            'description' => $validated['description'] ?? null,
            'status' => $initialStatus,
        ]);

        // Add destinations
        foreach ($validated['destinations'] as $order => $destName) {
            TourDestination::create([
                'tour_id' => $tour->id,
                'destination_name' => trim($destName),
                'visit_order' => $order + 1,
            ]);
        }

        // Add Tour Manager as Member #1 (Rule: Manager is also a participant)
        TourMember::create([
            'tour_id' => $tour->id,
            'user_id' => $user->id,
            'tour_role' => 'manager',
            'status' => 'joined',
            'joined_at' => now(),
        ]);

        // Timeline event
        TourTimeline::create([
            'tour_id' => $tour->id,
            'event_type' => 'tour_created',
            'description' => "Tour {$tour->tour_name} was created by {$user->full_name}",
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tour created successfully',
            'tour' => $tour->load(['destinations', 'members.user']),
        ], 201);
    }

    /**
     * Update an existing tour (Tour Manager)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $tour = Tour::where('created_by', $user->id)->with('destinations')->find($id);

        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found or unauthorized'], 404);
        }

        if ($tour->isFinished()) {
            return response()->json(['success' => false, 'message' => 'Cannot modify a finished tour'], 422);
        }

        $validated = $request->validate([
            'tour_name' => ['required', 'string', 'max:255'],
            'start_location' => ['required', 'string', 'max:255'],
            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*' => ['string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'estimated_budget' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ], [
            'end_date.after_or_equal' => 'Tour end date must be on or after the start date.',
        ]);

        $tour->update([
            'tour_name' => $validated['tour_name'],
            'start_location' => $validated['start_location'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'estimated_budget' => $validated['estimated_budget'],
            'description' => $validated['description'] ?? null,
        ]);

        // Re-sync destinations
        TourDestination::where('tour_id', $tour->id)->delete();
        foreach ($validated['destinations'] as $order => $destName) {
            TourDestination::create([
                'tour_id' => $tour->id,
                'destination_name' => trim($destName),
                'visit_order' => $order + 1,
            ]);
        }

        $tour->syncDynamicStatus();

        return response()->json([
            'success' => true,
            'message' => 'Tour updated successfully',
            'tour' => $tour->fresh(['destinations', 'members.user']),
        ]);
    }

    /**
     * Delete a tour (Tour Manager)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $tour = Tour::where('created_by', $user->id)->find($id);

        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found or unauthorized'], 404);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($tour) {
            TourDestination::where('tour_id', $tour->id)->delete();
            TourMember::where('tour_id', $tour->id)->delete();
            Refund::where('tour_id', $tour->id)->delete();
            TourTimeline::where('tour_id', $tour->id)->delete();
            $tour->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Tour deleted successfully',
        ]);
    }

    /**
     * Get specific tour details
     */
    public function show(int $id): JsonResponse
    {
        $tour = Tour::with(['destinations', 'members.user', 'expenses.category', 'timeline'])
            ->find($id);

        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

        $tour->syncDynamicStatus();

        $totalSpent = $tour->expenses->sum('total_amount');

        return response()->json([
            'success' => true,
            'tour' => $tour,
            'financial_summary' => [
                'estimated_budget' => (float) $tour->estimated_budget,
                'total_spent' => (float) $totalSpent,
                'total_members' => $tour->members->count(),
            ],
        ]);
    }

    /**
     * Preview settlement calculations before completing tour
     */
    public function settlementPreview(int $id, SettlementService $service): JsonResponse
    {
        $tour = Tour::find($id);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

        $financials = $service->calculateTourFinancials($tour);

        return response()->json([
            'success' => true,
            'settlement' => $financials,
        ]);
    }

    /**
     * Finish Tour normally
     */
    public function finish(Request $request, int $id, SettlementService $service): JsonResponse
    {
        $user = $request->user();
        $tour = Tour::where('created_by', $user->id)->find($id);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found or unauthorized'], 404);
        }

        $financials = $service->finishTour($tour, $user, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tour finished successfully',
            'settlement' => $financials,
        ]);
    }

    /**
     * Emergency Finish Tour
     */
    public function emergencyFinish(Request $request, int $id, SettlementService $service): JsonResponse
    {
        $user = $request->user();
        $tour = Tour::where('created_by', $user->id)->find($id);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found or unauthorized'], 404);
        }

        $validated = $request->validate([
            'emergency_reason' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $financials = $service->emergencyFinishTour($tour, $user, $validated['emergency_reason'], $validated['notes'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Tour finished early due to emergency',
            'settlement' => $financials,
        ]);
    }

    /**
     * Get tour refunds list (Requirement 7)
     */
    public function refunds(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $tour = Tour::where('created_by', $user->id)->find($id);
        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found or unauthorized'], 404);
        }

        $refunds = Refund::where('tour_id', $tour->id)
            ->with(['tourMember', 'refundedBy'])
            ->get();

        $data = $refunds->map(function ($r) {
            return [
                'id' => $r->id,
                'tour_id' => $r->tour_id,
                'tour_member_id' => $r->tour_member_id,
                'member_name' => $r->tourMember?->display_name ?? 'Member',
                'member_phone' => $r->tourMember?->display_phone ?? '',
                'refund_amount' => (float) $r->refund_amount,
                'status' => $r->status,
                'method' => $r->method ?? 'Cash',
                'refund_date' => $r->refund_date?->format('d M, Y'),
                'notes' => $r->notes,
                'refunded_by' => $r->refundedBy?->full_name,
            ];
        });

        return response()->json([
            'success' => true,
            'refunds' => $data,
            'total_refundable' => (float) $refunds->sum('refund_amount'),
            'total_refunded' => (float) $refunds->where('status', 'refunded')->sum('refund_amount'),
            'total_pending' => (float) $refunds->where('status', 'pending')->sum('refund_amount'),
        ]);
    }

    /**
     * Update refund status manually (Requirement 7)
     */
    public function updateRefundStatus(Request $request, int $refundId, SettlementService $service): JsonResponse
    {
        $user = $request->user();
        $refund = Refund::with('tour')->findOrFail($refundId);

        if (! $refund->tour || $refund->tour->created_by !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,refunded'],
            'method' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $service->updateRefundStatus(
            $refund,
            $validated['status'],
            $validated['method'] ?? 'Cash',
            $user,
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Refund status updated successfully',
            'refund' => [
                'id' => $refund->id,
                'status' => $refund->status,
                'method' => $refund->method,
                'refund_date' => $refund->refund_date?->format('d M, Y'),
                'notes' => $refund->notes,
            ],
        ]);
    }
}
