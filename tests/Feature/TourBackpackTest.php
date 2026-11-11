<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\TourBackpackItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourBackpackTest extends TestCase
{
    use RefreshDatabase;
    public function test_tour_backpack_auto_initializes_with_default_items(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $tour = Tour::create([
            'created_by' => $user->id,
            'tour_name' => 'Cox Bazar Sea Fun',
            'start_location' => 'Dhaka',
            'categories' => ['Beach & Island Tours'],
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(10)->format('Y-m-d'),
            'estimated_budget' => 50000,
            'status' => 'upcoming',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson("/api/tours/{$tour->id}/backpack-items");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tour_id' => $tour->id,
                'total_items' => 8,
                'packed_items' => 0,
            ]);

        $this->assertDatabaseHas('tour_backpack_items', [
            'tour_id' => $tour->id,
            'category' => 'Beach & Island Tours',
            'item_name' => 'Swimsuit',
            'is_packed' => false,
        ]);
    }

    public function test_user_can_add_custom_backpack_item(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $tour = Tour::create([
            'created_by' => $user->id,
            'tour_name' => 'Hill Climb',
            'start_location' => 'Chittagong',
            'categories' => ['Mountain & Hiking Tours'],
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(6)->format('Y-m-d'),
            'estimated_budget' => 30000,
            'status' => 'upcoming',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/tours/{$tour->id}/backpack-items", [
            'category' => 'Mountain & Hiking Tours',
            'item_name' => 'Custom Compass',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'item' => [
                    'item_name' => 'Custom Compass',
                    'is_custom' => true,
                ],
            ]);

        $this->assertDatabaseHas('tour_backpack_items', [
            'tour_id' => $tour->id,
            'item_name' => 'Custom Compass',
            'is_custom' => true,
        ]);
    }

    public function test_user_can_toggle_and_delete_backpack_item(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $tour = Tour::create([
            'created_by' => $user->id,
            'tour_name' => 'Wildlife Adventure',
            'start_location' => 'Khulna',
            'categories' => ['Wildlife & Safari Tours'],
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(6)->format('Y-m-d'),
            'estimated_budget' => 40000,
            'status' => 'upcoming',
        ]);

        $item = TourBackpackItem::create([
            'tour_id' => $tour->id,
            'category' => 'Wildlife & Safari Tours',
            'item_name' => 'Binoculars',
            'is_packed' => false,
            'is_custom' => false,
        ]);

        // Toggle
        $toggleRes = $this->actingAs($user, 'sanctum')->patchJson("/api/tours/{$tour->id}/backpack-items/{$item->id}/toggle");
        $toggleRes->assertStatus(200)->assertJson(['success' => true, 'is_packed' => true]);
        $this->assertDatabaseHas('tour_backpack_items', ['id' => $item->id, 'is_packed' => true]);

        // Delete
        $deleteRes = $this->actingAs($user, 'sanctum')->deleteJson("/api/tours/{$tour->id}/backpack-items/{$item->id}");
        $deleteRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('tour_backpack_items', ['id' => $item->id]);
    }

    public function test_category_wise_backpack_loads_distinct_items_for_different_categories(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $tour = Tour::create([
            'created_by' => $user->id,
            'tour_name' => 'Sajek Tour',
            'start_location' => 'Dhaka',
            'categories' => ['Mountain & Hiking Tours', 'Beach & Island Tours'],
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'estimated_budget' => 20000,
            'status' => 'upcoming',
        ]);

        // Request Beach & Island Tours
        $beachRes = $this->actingAs($user, 'sanctum')
            ->getJson("/api/tours/{$tour->id}/backpack-items?category=" . urlencode('Beach & Island Tours'));
        $beachRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'active_category' => 'Beach & Island Tours',
                'total_items' => 8,
            ]);
        $beachItemNames = collect($beachRes->json('items'))->pluck('item_name')->toArray();
        $this->assertContains('Swimsuit', $beachItemNames);
        $this->assertContains('Sunscreen', $beachItemNames);
        $this->assertNotContains('Hiking boots', $beachItemNames);

        // Request Mountain & Hiking Tours
        $mountainRes = $this->actingAs($user, 'sanctum')
            ->getJson("/api/tours/{$tour->id}/backpack-items?category=" . urlencode('Mountain & Hiking Tours'));
        $mountainRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'active_category' => 'Mountain & Hiking Tours',
                'total_items' => 8,
            ]);
        $mountainItemNames = collect($mountainRes->json('items'))->pluck('item_name')->toArray();
        $this->assertContains('Hiking boots', $mountainItemNames);
        $this->assertContains('Trekking poles', $mountainItemNames);
        $this->assertNotContains('Swimsuit', $mountainItemNames);
    }

    public function test_changes_in_one_category_do_not_affect_other_category(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $tour = Tour::create([
            'created_by' => $user->id,
            'tour_name' => 'Multi Category Tour',
            'start_location' => 'Sylhet',
            'categories' => ['Camping & Nature Tours', 'Food & Culinary Tours'],
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(4)->format('Y-m-d'),
            'estimated_budget' => 25000,
            'status' => 'upcoming',
        ]);

        // Add custom item to Camping & Nature Tours
        $this->actingAs($user, 'sanctum')->postJson("/api/tours/{$tour->id}/backpack-items", [
            'category' => 'Camping & Nature Tours',
            'item_name' => 'Custom Camping Light',
        ])->assertStatus(201);

        // Verify custom item is in Camping & Nature Tours
        $campingRes = $this->actingAs($user, 'sanctum')
            ->getJson("/api/tours/{$tour->id}/backpack-items?category=" . urlencode('Camping & Nature Tours'));
        $campingItems = collect($campingRes->json('items'))->pluck('item_name')->toArray();
        $this->assertContains('Custom Camping Light', $campingItems);

        // Verify Food & Culinary Tours does NOT contain custom camping light
        $foodRes = $this->actingAs($user, 'sanctum')
            ->getJson("/api/tours/{$tour->id}/backpack-items?category=" . urlencode('Food & Culinary Tours'));
        $foodItems = collect($foodRes->json('items'))->pluck('item_name')->toArray();
        $this->assertNotContains('Custom Camping Light', $foodItems);
    }
}
