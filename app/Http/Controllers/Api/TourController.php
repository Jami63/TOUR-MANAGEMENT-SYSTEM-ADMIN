<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'estimated_budget' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        $tour = Tour::create([
            'created_by' => $user->id,
            'tour_name' => $validated['tour_name'],
            'start_location' => $validated['start_location'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'estimated_budget' => $validated['estimated_budget'],
            'description' => $validated['description'] ?? null,
            'status' => 'upcoming',
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
     * Get specific tour details
     */
    public function show(int $id): JsonResponse
    {
        $tour = Tour::with(['destinations', 'members.user', 'expenses.category', 'timeline'])
            ->find($id);

        if (! $tour) {
            return response()->json(['success' => false, 'message' => 'Tour not found'], 404);
        }

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
}
