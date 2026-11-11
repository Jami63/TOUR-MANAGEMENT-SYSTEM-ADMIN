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
     * Get backpack items for a specific tour and category.
     * Dynamically initializes each category from its own default templates if not yet initialized.
     */
    public function index(Request $request, int $tourId): JsonResponse
    {
        $tour = Tour::findOrFail($tourId);

        // Normalize tour's assigned categories
        $tourCategories = $tour->categories ?? [];
        if (is_string($tourCategories)) {
            $decoded = json_decode($tourCategories, true);
            $tourCategories = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $tourCategories)));
        }

        // Get requested category from query parameter, if specified
        $requestedCategory = $request->query('category');
        if (! empty($requestedCategory)) {
            $requestedCategory = trim($requestedCategory);
        }

        // Determine active category:
        // Priority:
        // 1. Explicitly requested category ($request->query('category'))
        // 2. First category assigned to the tour
        // 3. First category already existing in this tour's backpack items
        // 4. Default to 'Beach & Island Tours'
        $activeCategory = $requestedCategory;
        if (empty($activeCategory)) {
            if (! empty($tourCategories)) {
                $activeCategory = trim($tourCategories[0]);
            } else {
                $existingCat = TourBackpackItem::where('tour_id', $tourId)->value('category');
                $activeCategory = $existingCat ?: 'Beach & Island Tours';
            }
        }

        // Ensure the active category has its items initialized from default templates
        if (! empty($activeCategory)) {
            $exists = TourBackpackItem::where('tour_id', $tourId)
                ->where('category', $activeCategory)
                ->exists();

            if (! $exists) {
                $defaults = DefaultBackpackItem::where('category', $activeCategory)
                    ->orderBy('order_index')
                    ->get();

                foreach ($defaults as $d) {
                    TourBackpackItem::create([
                        'tour_id' => $tourId,
                        'category' => $activeCategory,
                        'item_name' => $d->item_name,
                        'is_packed' => false,
                        'is_custom' => false,
                    ]);
                }

                // If tour had no category assigned in database, link this category to tour
                if (empty($tourCategories)) {
                    $tour->categories = [$activeCategory];
                    $tour->save();
                    $tourCategories = [$activeCategory];
                }
            }
        }

        // If tour had multiple categories assigned, ensure all of them are also initialized
        foreach ($tourCategories as $cat) {
            $catTrimmed = trim($cat);
            if (! empty($catTrimmed) && $catTrimmed !== $activeCategory) {
                $exists = TourBackpackItem::where('tour_id', $tourId)
                    ->where('category', $catTrimmed)
                    ->exists();

                if (! $exists) {
                    $defaults = DefaultBackpackItem::where('category', $catTrimmed)
                        ->orderBy('order_index')
                        ->get();

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
        }

        // Query items strictly for the active category
        $items = TourBackpackItem::where('tour_id', $tourId)
            ->where('category', $activeCategory)
            ->orderBy('id')
            ->get();

        // Get all categories that currently exist for this tour in backpack
        $existingCategories = TourBackpackItem::where('tour_id', $tourId)
            ->distinct()
            ->pluck('category')
            ->values();

        $allTourCategories = collect($tourCategories)
            ->merge($existingCategories)
            ->unique()
            ->values();

        // All 20 system categories from default catalog
        $allSystemCategories = DefaultBackpackItem::distinct()
            ->orderBy('id')
            ->pluck('category')
            ->values();

        return response()->json([
            'success' => true,
            'tour_id' => $tourId,
            'active_category' => $activeCategory,
            'tour_categories' => $allTourCategories,
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
        $category = trim($request->category);

        $item = TourBackpackItem::create([
            'tour_id' => $tourId,
            'category' => $category,
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
