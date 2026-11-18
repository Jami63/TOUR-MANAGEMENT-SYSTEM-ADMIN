<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Tour;
use App\Models\TourBackpackItem;
use App\Models\TourMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourAuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $managerA;
    protected User $managerB;
    protected User $memberUser;
    protected User $outsiderUser;
    protected User $superAdmin;
    protected Tour $tourA;
    protected TourMember $memberRecord;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tour Manager A (Owner of Tour A)
        $this->managerA = User::create([
            'full_name' => 'Manager Alpha',
            'email' => 'manager.a@test.com',
            'phone' => '01711111111',
            'password' => bcrypt('Password123!'),
            'role' => 'manager',
        ]);

        // 2. Tour Manager B (Unrelated third-party manager)
        $this->managerB = User::create([
            'full_name' => 'Manager Beta',
            'email' => 'manager.b@test.com',
            'phone' => '01722222222',
            'password' => bcrypt('Password123!'),
            'role' => 'manager',
        ]);

        // 3. Member User (Legitimate participant in Tour A)
        $this->memberUser = User::create([
            'full_name' => 'Participant Charlie',
            'email' => 'member.c@test.com',
            'phone' => '01733333333',
            'password' => bcrypt('Password123!'),
            'role' => 'member',
        ]);

        // 4. Outsider User (Has an account, but has zero relation to Tour A)
        $this->outsiderUser = User::create([
            'full_name' => 'Outsider Dave',
            'email' => 'outsider.d@test.com',
            'phone' => '01744444444',
            'password' => bcrypt('Password123!'),
            'role' => 'member',
        ]);

        // 5. Super Admin
        $this->superAdmin = User::create([
            'full_name' => 'Admin Boss',
            'email' => 'admin@test.com',
            'phone' => '01755555555',
            'password' => bcrypt('Password123!'),
            'role' => 'super_admin',
        ]);

        // Create Tour A owned by Manager A
        $this->tourA = Tour::create([
            'created_by' => $this->managerA->id,
            'tour_name' => 'Sylhet Green Hills Tour',
            'start_location' => 'Dhaka',
            'categories' => ['Hill & Forest Tours'],
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'estimated_budget' => 60000,
            'status' => 'upcoming',
        ]);

        // Auto-add Manager A as Member #1
        TourMember::create([
            'tour_id' => $this->tourA->id,
            'user_id' => $this->managerA->id,
            'tour_role' => 'manager',
            'status' => 'joined',
        ]);

        // Add Participant Charlie as Member #2
        $this->memberRecord = TourMember::create([
            'tour_id' => $this->tourA->id,
            'user_id' => $this->memberUser->id,
            'name' => 'Participant Charlie',
            'tour_role' => 'member',
            'status' => 'joined',
        ]);
    }

    /**
     * Test 1: Outsider and Manager B are blocked from viewing Tour A details (IDOR Prevention)
     */
    public function test_unauthorized_users_cannot_view_tour_details(): void
    {
        // Manager B attempts to view Tour A details
        $resB = $this->actingAs($this->managerB, 'sanctum')->getJson("/api/tours/{$this->tourA->id}");
        $resB->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour details']);

        // Outsider attempts to view Tour A details
        $resOutsider = $this->actingAs($this->outsiderUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}");
        $resOutsider->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour details']);

        // Manager A (Owner) can view
        $resA = $this->actingAs($this->managerA, 'sanctum')->getJson("/api/tours/{$this->tourA->id}");
        $resA->assertStatus(200)->assertJson(['success' => true]);

        // Member C can view
        $resC = $this->actingAs($this->memberUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}");
        $resC->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test 2: Outsider and Manager B are blocked from reading expenses of Tour A
     */
    public function test_unauthorized_users_cannot_view_tour_expenses(): void
    {
        // Manager B attempts to read expenses of Tour A
        $resB = $this->actingAs($this->managerB, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/expenses");
        $resB->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour expenses']);

        // Outsider attempts to read expenses of Tour A
        $resOutsider = $this->actingAs($this->outsiderUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/expenses");
        $resOutsider->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour expenses']);

        // Manager A can read expenses
        $resA = $this->actingAs($this->managerA, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/expenses");
        $resA->assertStatus(200)->assertJson(['success' => true]);

        // Member C can read expenses
        $resC = $this->actingAs($this->memberUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/expenses");
        $resC->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test 3: Only the Tour Manager of Tour A can add expenses
     */
    public function test_only_tour_creator_can_add_expenses(): void
    {
        // Manager B attempts to add an expense to Tour A
        $resB = $this->actingAs($this->managerB, 'sanctum')->postJson("/api/tours/{$this->tourA->id}/expenses", [
            'expense_name' => 'Illicit Manager B Hotel Bill',
            'total_amount' => 5000,
            'category_name' => 'Hotel',
        ]);
        $resB->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Only the Tour Manager can add expenses to this tour']);

        // Participant Member C attempts to add an expense to Tour A
        $resC = $this->actingAs($this->memberUser, 'sanctum')->postJson("/api/tours/{$this->tourA->id}/expenses", [
            'expense_name' => 'Member C Fuel Bill',
            'total_amount' => 1200,
            'category_name' => 'Transport',
        ]);
        $resC->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Only the Tour Manager can add expenses to this tour']);

        // Legitimate Tour Manager A adds an expense to Tour A
        $resA = $this->actingAs($this->managerA, 'sanctum')->postJson("/api/tours/{$this->tourA->id}/expenses", [
            'expense_name' => 'Legitimate Resort Booking',
            'total_amount' => 10000,
            'category_name' => 'Resort',
        ]);
        $resA->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    /**
     * Test 4: Settlement preview is restricted to tour participants and creator
     */
    public function test_settlement_preview_is_restricted(): void
    {
        // Outsider attempts settlement preview
        $resOutsider = $this->actingAs($this->outsiderUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/settlement-preview");
        $resOutsider->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour settlement preview']);

        // Manager B attempts settlement preview
        $resB = $this->actingAs($this->managerB, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/settlement-preview");
        $resB->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour settlement preview']);

        // Manager A can preview settlement
        $resA = $this->actingAs($this->managerA, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/settlement-preview");
        $resA->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test 5: Tour payments list is restricted to tour participants
     */
    public function test_tour_payments_list_is_restricted(): void
    {
        // Outsider attempts to read Tour A payments
        $resOutsider = $this->actingAs($this->outsiderUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/payments");
        $resOutsider->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour payments']);

        // Manager B attempts to read Tour A payments
        $resB = $this->actingAs($this->managerB, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/payments");
        $resB->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour payments']);

        // Manager A can read payments
        $resA = $this->actingAs($this->managerA, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/payments");
        $resA->assertStatus(200)->assertJson(['success' => true]);

        // Member C can read payments
        $resC = $this->actingAs($this->memberUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/payments");
        $resC->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test 6: Tour members index is restricted to tour participants
     */
    public function test_tour_members_index_is_restricted(): void
    {
        // Outsider attempts to read participants of Tour A
        $resOutsider = $this->actingAs($this->outsiderUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/members");
        $resOutsider->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour members']);

        // Manager B attempts to read participants of Tour A
        $resB = $this->actingAs($this->managerB, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/members");
        $resB->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to tour members']);

        // Manager A can read participants
        $resA = $this->actingAs($this->managerA, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/members");
        $resA->assertStatus(200)->assertJson(['success' => true]);

        // Member C can read participants
        $resC = $this->actingAs($this->memberUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/members");
        $resC->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test 7: Manager B cannot add or remove participants on Manager A's tour
     */
    public function test_manager_b_cannot_add_or_delete_members_on_tour_a(): void
    {
        // Manager B attempts to add a member to Tour A
        $resAdd = $this->actingAs($this->managerB, 'sanctum')->postJson("/api/tours/{$this->tourA->id}/members", [
            'name' => 'Unauthorized Friend',
            'phone' => '01999999999',
        ]);
        $resAdd->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Only the Tour Manager can add participants']);

        // Manager B attempts to delete Member Charlie from Tour A
        $resDel = $this->actingAs($this->managerB, 'sanctum')->deleteJson("/api/tours/{$this->tourA->id}/members/{$this->memberRecord->id}");
        $resDel->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized']);

        // Legitimate Manager A CAN add a member
        $resAddA = $this->actingAs($this->managerA, 'sanctum')->postJson("/api/tours/{$this->tourA->id}/members", [
            'name' => 'Authorized Traveler',
            'phone' => '01888888888',
        ]);
        $resAddA->assertStatus(201)->assertJson(['success' => true]);
    }

    /**
     * Test 8: Tour Backpack checklist is locked down from non-participants
     */
    public function test_backpack_checklist_is_protected_from_unauthorized_users(): void
    {
        // Outsider attempts to read backpack items
        $resOutsider = $this->actingAs($this->outsiderUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/backpack-items");
        $resOutsider->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to backpack checklist']);

        // Manager B attempts to read backpack items
        $resB = $this->actingAs($this->managerB, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/backpack-items");
        $resB->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to backpack checklist']);

        // Outsider attempts to add an item
        $resAdd = $this->actingAs($this->outsiderUser, 'sanctum')->postJson("/api/tours/{$this->tourA->id}/backpack-items", [
            'category' => 'Hill & Forest Tours',
            'item_name' => 'Outsider Tent',
        ]);
        $resAdd->assertStatus(403)
            ->assertJson(['success' => false, 'message' => 'Unauthorized access to backpack checklist']);

        // Authorized Member C can read and add items
        $resReadC = $this->actingAs($this->memberUser, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/backpack-items");
        $resReadC->assertStatus(200)->assertJson(['success' => true]);

        $resAddC = $this->actingAs($this->memberUser, 'sanctum')->postJson("/api/tours/{$this->tourA->id}/backpack-items", [
            'category' => 'Hill & Forest Tours',
            'item_name' => 'Member C Flashlight',
        ]);
        $resAddC->assertStatus(201)->assertJson(['success' => true]);

        $itemId = $resAddC->json('item.id');

        // Outsider attempts to toggle or delete the item
        $resToggle = $this->actingAs($this->outsiderUser, 'sanctum')->patchJson("/api/tours/{$this->tourA->id}/backpack-items/{$itemId}/toggle");
        $resToggle->assertStatus(403);

        $resDelete = $this->actingAs($this->outsiderUser, 'sanctum')->deleteJson("/api/tours/{$this->tourA->id}/backpack-items/{$itemId}");
        $resDelete->assertStatus(403);
    }

    /**
     * Test 9: Super Admin has global override permission
     */
    public function test_super_admin_has_global_access(): void
    {
        $resDetails = $this->actingAs($this->superAdmin, 'sanctum')->getJson("/api/tours/{$this->tourA->id}");
        $resDetails->assertStatus(200)->assertJson(['success' => true]);

        $resExpenses = $this->actingAs($this->superAdmin, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/expenses");
        $resExpenses->assertStatus(200)->assertJson(['success' => true]);

        $resMembers = $this->actingAs($this->superAdmin, 'sanctum')->getJson("/api/tours/{$this->tourA->id}/members");
        $resMembers->assertStatus(200)->assertJson(['success' => true]);
    }
}
