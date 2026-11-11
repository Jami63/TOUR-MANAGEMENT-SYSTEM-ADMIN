<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefaultBackpackItem;
use App\Models\Tour;
use App\Models\TourBackpackItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourBackpackController extends Controller
{
    /**
     * Get backpack items for a specific tour.
     * Automatically initializes items from default templates if not yet created.
     */
    public function index(Request $request, int $tourId): JsonResponse
    {
        $tour = Tour::findOrFail($tourId);

        // Normalize tour categories
        $categories = $tour->categories ?? [];
        if (is_string($categories)) {
            $decoded = json_decode($categories, true);
            $categories = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $categories)));
        }

        if (empty($categories)) {
            // If tour has no specific category assigned, use the first category from defaults as fallback
            $fallback = DefaultBackpackItem::distinct()->pluck('category')->first() ?? 'Beach & Island Tours';
            $categories = [$fallback];
        }

        // Initialize any missing categories from default template items
        foreach ($categories as $cat) {
            $catTrimmed = trim($cat);
            $exists = TourBackpackItem::where('tour_id', $tourId)->where('category', $catTrimmed)->exists();
            if (! $exists) {
                $defaults = DefaultBackpackItem::where('category', $catTrimmed)->orderBy('order_index')->get();
                foreach ($defaults as $d) {
                    TourBackpackItem::create([
                        'tour_id' => $tourId,
                        'category' => $catTrimmed,
                        'item_name' => $d->item_name,
                        'is_packed' => false,
                        'is_custom' => false,
                    ]);
                }
            }
        }

        $query = TourBackpackItem::where('tour_id', $tourId);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $items = $query->orderBy('id')->get();

        // Also fetch all available categories in the system dynamically from DB
        $allSystemCategories = DefaultBackpackItem::distinct()->pluck('category')->values();

        return response()->json([
            'success' => true,
            'tour_id' => $tourId,
            'tour_categories' => array_values($categories),
            'all_categories' => $allSystemCategories,
            'total_items' => $items->count(),
            'packed_items' => $items->where('is_packed', true)->count(),
            'items' => $items,
        ]);
    }

    /**
     * Add a custom backpack item.
     */
    public function store(Request $request, int $tourId): JsonResponse
    {
        $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'item_name' => ['required', 'string', 'max:255'],
        ]);

        $tour = Tour::findOrFail($tourId);

        $item = TourBackpackItem::create([
            'tour_id' => $tourId,
            'category' => trim($request->category),
            'item_name' => trim($request->item_name),
            'is_packed' => false,
            'is_custom' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Backpack item added successfully',
            'item' => $item,
        ], 201);
    }

    /**
     * Toggle the packed status of a backpack item.
     */
    public function toggle(int $tourId, int $itemId): JsonResponse
    {
        $item = TourBackpackItem::where('tour_id', $tourId)->findOrFail($itemId);
        $item->is_packed = ! $item->is_packed;
        $item->save();

        return response()->json([
            'success' => true,
            'is_packed' => $item->is_packed,
            'item' => $item,
        ]);
    }

    /**
     * Delete a backpack item (default or custom).
     */
    public function destroy(int $tourId, int $itemId): JsonResponse
    {
        $item = TourBackpackItem::where('tour_id', $tourId)->findOrFail($itemId);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Backpack item deleted successfully',
        ]);
    }

    /**
     * Reset items for a category back to default items.
     */
    public function resetCategory(Request $request, int $tourId): JsonResponse
    {
        $request->validate([
            'category' => ['required', 'string', 'max:100'],
        ]);

        $category = trim($request->category);
        TourBackpackItem::where('tour_id', $tourId)->where('category', $category)->delete();

        $defaults = DefaultBackpackItem::where('category', $category)->orderBy('order_index')->get();
        foreach ($defaults as $d) {
            TourBackpackItem::create([
                'tour_id' => $tourId,
                'category' => $category,
                'item_name' => $d->item_name,
                'is_packed' => false,
                'is_custom' => false,
            ]);
        }

        $items = TourBackpackItem::where('tour_id', $tourId)->where('category', $category)->orderBy('id')->get();

        return response()->json([
            'success' => true,
            'message' => "Category '$category' reset to default items",
            'items' => $items,
        ]);
    }
}
