<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseShare;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Tour;
use App\Models\TourMember;
use App\Models\User;
use App\Services\SettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourManagementFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $manager;

    protected ExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'full_name' => 'Super Administrator',
            'email' => 'admin@test.com',
            'phone' => '01711000000',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $this->manager = User::create([
            'full_name' => 'Tour Manager Jami',
            'email' => 'manager@test.com',
            'phone' => '01711000001',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        $this->category = ExpenseCategory::create([
            'category_name' => 'Transport',
            'created_by' => $this->manager->id,
        ]);
    }

    /**
     * Requirement 1: Strictly 2 User Roles & Access Control
     */
    public function test_super_admin_and_manager_roles_enforced(): void
    {
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertFalse($this->superAdmin->isManager());

        $this->assertTrue($this->manager->isManager());
        $this->assertFalse($this->manager->isSuperAdmin());

        // Manager cannot access Super Admin console -> redirected to manager dashboard
        $response = $this->actingAs($this->manager)->get('/admin/dashboard');
        $response->assertRedirect('/manager/dashboard');

        // Super Admin cannot access Manager creation routes -> redirected to admin dashboard
        $response = $this->actingAs($this->superAdmin)->get('/manager/tours/create');
        $response->assertRedirect('/admin/dashboard');
    }

    /**
     * Requirement 2: Tour Creation & Auto-Adding Manager as Member #1
     */
    public function test_manager_can_create_tour_and_is_auto_added_as_member(): void
    {
        $response = $this->actingAs($this->manager)->post('/manager/tours', [
            'tour_name' => "Cox's Bazar Tour",
            'start_location' => 'Dhaka',
            'destinations' => ["Cox's Bazar", 'Inani Beach'],
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'estimated_budget' => 200000,
        ]);

        $response->assertRedirect();

        $tour = Tour::where('tour_name', "Cox's Bazar Tour")->first();
        $this->assertNotNull($tour);
        $this->assertEquals(200000, (float) $tour->estimated_budget);
        $this->assertEquals($this->manager->id, $tour->created_by);

        // Check destinations
        $this->assertCount(2, $tour->destinations);

        // Manager must be automatically included as Member #1
        $this->assertCount(1, $tour->members);
        $member1 = $tour->members->first();
        $this->assertEquals($this->manager->id, $member1->user_id);
        $this->assertEquals('manager', $member1->tour_role);
    }

    /**
     * Requirement 2 & 4: Adding Tour Members without User Accounts
     */
    public function test_manager_can_add_tour_members_without_registered_accounts(): void
    {
        $tour = Tour::create([
            'tour_name' => 'Sylhet Adventure',
            'start_location' => 'Dhaka',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'estimated_budget' => 50000,
            'status' => 'upcoming',
            'created_by' => $this->manager->id,
        ]);

        // Add manager as member
        TourMember::create([
            'tour_id' => $tour->id,
            'user_id' => $this->manager->id,
            'name' => $this->manager->full_name,
            'phone' => $this->manager->phone,
            'email' => $this->manager->email,
            'tour_role' => 'manager',
        ]);

        // Add 4 members without user accounts (using name, phone, email)
        for ($i = 1; $i <= 4; $i++) {
            $response = $this->actingAs($this->manager)->post("/manager/tours/{$tour->id}/members", [
                'name' => "Friend Participant {$i}",
                'phone' => "0181100000{$i}",
                'email' => "friend{$i}@gmail.com",
            ]);
            $response->assertSessionHas('success');
        }

        $tour->refresh();
        $this->assertCount(5, $tour->members);

        // Verify member without account has correct display_name
        $friend = $tour->members->where('name', 'Friend Participant 1')->first();
        $this->assertNull($friend->user_id);
        $this->assertEquals('Friend Participant 1', $friend->display_name);
    }

    /**
     * Requirement 3: Equal Expense Sharing
     * Example from user: Bus Fare = ৳40,000, 5 members -> Each member's share = ৳8,000
     */
    public function test_equal_expense_sharing_is_divided_accurately(): void
    {
        $tour = Tour::create([
            'tour_name' => "Cox's Bazar Group",
            'start_location' => 'Dhaka',
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'estimated_budget' => 200000,
            'status' => 'ongoing',
            'created_by' => $this->manager->id,
        ]);

        // 5 members total
        for ($i = 1; $i <= 5; $i++) {
            TourMember::create([
                'tour_id' => $tour->id,
                'name' => "Member {$i}",
                'tour_role' => $i === 1 ? 'manager' : 'member',
            ]);
        }

        // Add Bus Fare expense of ৳40,000
        $response = $this->actingAs($this->manager)->post("/manager/tours/{$tour->id}/expenses", [
            'expense_name' => 'AC Bus Fare',
            'category_id' => $this->category->id,
            'total_amount' => 40000,
            'expense_date' => now()->toDateString(),
            'notes' => 'Dhaka to Cox round trip',
        ]);

        $response->assertSessionHas('success');

        $expense = Expense::where('tour_id', $tour->id)->first();
        $this->assertNotNull($expense);
        $this->assertEquals(40000, (float) $expense->total_amount);

        // Equal share: 40,000 / 5 = 8,000 each
        $shares = $expense->shares;
        $this->assertCount(5, $shares);

        foreach ($shares as $share) {
            $this->assertEquals(8000.00, (float) $share->share_amount);
            $this->assertEquals(8000.00, (float) $share->remainingDue());
            $this->assertEquals('Pending', $share->paymentStatus());
        }

        // Sum of shares must equal total amount exactly
        $this->assertEquals(40000.00, (float) $shares->sum('share_amount'));
    }

    /**
     * Requirement 4: Manual Payment Management (NO Gateway)
     * Statuses: Paid, Partial, Pending
     */
    public function test_manual_payment_logging_and_status_calculations(): void
    {
        $tour = Tour::create([
            'tour_name' => 'Payment Test Tour',
            'start_location' => 'Dhaka',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'estimated_budget' => 60000,
            'status' => 'ongoing',
            'created_by' => $this->manager->id,
        ]);

        $m1 = TourMember::create(['tour_id' => $tour->id, 'name' => 'Member One']);
        $m2 = TourMember::create(['tour_id' => $tour->id, 'name' => 'Member Two']);
        $m3 = TourMember::create(['tour_id' => $tour->id, 'name' => 'Member Three']);

        // Expense of ৳30,000 -> ৳10,000 each
        $expense = Expense::create([
            'tour_id' => $tour->id,
            'expense_name' => 'Resort Booking',
            'category_id' => $this->category->id,
            'total_amount' => 30000,
            'added_by' => $this->manager->id,
            'expense_date' => now()->toDateString(),
        ]);

        $s1 = ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m1->id, 'share_amount' => 10000]);
        $s2 = ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m2->id, 'share_amount' => 10000]);
        $s3 = ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m3->id, 'share_amount' => 10000]);

        // Member 1 pays full ৳10,000 via bKash -> Status must be 'Paid'
        $this->actingAs($this->manager)->post("/manager/tours/{$tour->id}/expenses/{$expense->id}/payment", [
            'tour_member_id' => $m1->id,
            'amount' => 10000,
            'method' => 'bkash',
            'transaction_id' => 'BKASH987654321',
            'payment_date' => now()->toDateString(),
        ]);

        // Member 2 pays partial ৳4,000 via Cash -> Status must be 'Partial'
        $this->actingAs($this->manager)->post("/manager/tours/{$tour->id}/expenses/{$expense->id}/payment", [
            'tour_member_id' => $m2->id,
            'amount' => 4000,
            'method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        // Member 3 pays ৳0 -> Status must be 'Pending'

        $s1->refresh();
        $s2->refresh();
        $s3->refresh();

        $this->assertEquals(10000.00, $s1->paidAmount());
        $this->assertEquals(0.00, $s1->remainingDue());
        $this->assertEquals('Paid', $s1->paymentStatus());

        $this->assertEquals(4000.00, $s2->paidAmount());
        $this->assertEquals(6000.00, $s2->remainingDue());
        $this->assertEquals('Partial', $s2->paymentStatus());

        $this->assertEquals(0.00, $s3->paidAmount());
        $this->assertEquals(10000.00, $s3->remainingDue());
        $this->assertEquals('Pending', $s3->paymentStatus());
    }

    /**
     * Requirement 5: Finish Tour & Final Settlement
     */
    public function test_finish_tour_generates_final_settlement(): void
    {
        $tour = Tour::create([
            'tour_name' => 'Completed Tour',
            'start_location' => 'Dhaka',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(1)->toDateString(),
            'estimated_budget' => 25000,
            'status' => 'ongoing',
            'created_by' => $this->manager->id,
        ]);

        $m1 = TourMember::create(['tour_id' => $tour->id, 'name' => 'Alice']);
        $m2 = TourMember::create(['tour_id' => $tour->id, 'name' => 'Bob']);

        $expense = Expense::create([
            'tour_id' => $tour->id,
            'expense_name' => 'Food & Hotel',
            'category_id' => $this->category->id,
            'total_amount' => 20000,
            'added_by' => $this->manager->id,
            'expense_date' => now()->subDays(2)->toDateString(),
        ]);

        $s1 = ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m1->id, 'share_amount' => 10000]);
        $s2 = ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m2->id, 'share_amount' => 10000]);

        // Alice paid ৳15,000 (overpaid by 5,000)
        Payment::create([
            'share_id' => $s1->id,
            'amount' => 15000,
            'method' => 'bkash',
            'status' => 'verified',
            'submitted_at' => now(),
        ]);

        // Bob paid ৳10,000 (exact)
        Payment::create([
            'share_id' => $s2->id,
            'amount' => 10000,
            'method' => 'cash',
            'status' => 'verified',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->manager)->post("/manager/tours/{$tour->id}/finish", [
            'settlement_notes' => 'Everything went smoothly',
        ]);

        $response->assertSessionHas('success');

        $tour->refresh();
        $this->assertEquals('completed', $tour->status);
        $this->assertEquals('normal', $tour->finish_type);
        $this->assertEquals(25000.00, (float) $tour->total_collected);
        $this->assertEquals(20000.00, (float) $tour->total_actual_expense);
        $this->assertEquals(5000.00, (float) $tour->total_refund_amount);

        // Refund record created for Alice (৳5,000)
        $refund = Refund::where('tour_id', $tour->id)->where('tour_member_id', $m1->id)->first();
        $this->assertNotNull($refund);
        $this->assertEquals(2500.00, (float) $refund->refund_amount);
        $this->assertEquals('pending', $refund->status);

        // Bob also gets his equal share of remaining budget refund (৳2,500)
        $bobRefund = Refund::where('tour_id', $tour->id)->where('tour_member_id', $m2->id)->first();
        $this->assertNotNull($bobRefund);
        $this->assertEquals(2500.00, (float) $bobRefund->refund_amount);
    }

    /**
     * Requirement: Tour must NOT be finished if any participant has an outstanding tour share or payment due.
     */
    public function test_tour_cannot_be_finished_if_participant_has_outstanding_due(): void
    {
        $tour = Tour::create([
            'tour_name' => 'Unpaid Member Tour',
            'start_location' => 'Dhaka',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'estimated_budget' => 20000,
            'status' => 'ongoing',
            'created_by' => $this->manager->id,
        ]);

        $m1 = TourMember::create(['tour_id' => $tour->id, 'name' => 'Charlie']);
        $m2 = TourMember::create(['tour_id' => $tour->id, 'name' => 'Dave']);

        $expense = Expense::create([
            'tour_id' => $tour->id,
            'expense_name' => 'Transport',
            'category_id' => $this->category->id,
            'total_amount' => 2000,
            'added_by' => $this->manager->id,
            'expense_date' => now()->toDateString(),
        ]);

        // Each has share of 1,000
        $s1 = ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m1->id, 'share_amount' => 1000]);
        $s2 = ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m2->id, 'share_amount' => 1000]);

        // Charlie paid only 500 (500 outstanding)
        Payment::create([
            'share_id' => $s1->id,
            'amount' => 500,
            'method' => 'cash',
            'status' => 'verified',
            'submitted_at' => now(),
        ]);

        // Dave paid full 1000
        Payment::create([
            'share_id' => $s2->id,
            'amount' => 1000,
            'method' => 'cash',
            'status' => 'verified',
            'submitted_at' => now(),
        ]);

        // 1. Attempt API finish
        $apiResponse = $this->actingAs($this->manager)->postJson("/api/tours/{$tour->id}/finish");
        $apiResponse->assertStatus(422);
        $apiResponse->assertJsonFragment(['success' => false]);
        $this->assertStringContainsString('Charlie', $apiResponse->json('message'));
        $this->assertStringContainsString('500.00', $apiResponse->json('message'));

        // Tour status MUST NOT have changed
        $tour->refresh();
        $this->assertEquals('ongoing', $tour->status);

        // 2. Now Charlie pays remaining 500
        Payment::create([
            'share_id' => $s1->id,
            'amount' => 500,
            'method' => 'cash',
            'status' => 'verified',
            'submitted_at' => now(),
        ]);

        // 3. Now finish succeeds
        $apiResponse2 = $this->actingAs($this->manager)->postJson("/api/tours/{$tour->id}/finish");
        $apiResponse2->assertStatus(200);
        $apiResponse2->assertJsonFragment(['success' => true]);

        $tour->refresh();
        $this->assertEquals('completed', $tour->status);
    }

    /**
     * Requirement 6: Emergency Finish Tour & Strict Refund Calculation:
     * "Member's Net Balance = Total Amount Paid − Equal Share of Actual Expenses.
     * If positive -> refund; If zero -> no refund; If negative -> owes money.
     * A member who paid nothing must not receive a refund."
     *
     * Example from prompt:
     * 5 members. Only one member paid ৳100. The other four paid ৳0.
     * If no actual expense occurred (0 expense), first member receives ৳100 back, other 4 receive ৳0.
     */
    public function test_emergency_finish_zero_expense_refunds_only_payer(): void
    {
        $tour = Tour::create([
            'tour_name' => 'Sudden Cancel Tour',
            'start_location' => 'Dhaka',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'estimated_budget' => 50000,
            'status' => 'ongoing',
            'created_by' => $this->manager->id,
        ]);

        // 5 members
        $members = [];
        for ($i = 1; $i <= 5; $i++) {
            $members[] = TourMember::create([
                'tour_id' => $tour->id,
                'name' => "Member {$i}",
            ]);
        }

        /** @var SettlementService $service */
        $service = app(SettlementService::class);

        $expense = Expense::create([
            'tour_id' => $tour->id,
            'expense_name' => 'Initial Advance',
            'category_id' => $this->category->id,
            'total_amount' => 0,
            'added_by' => $this->manager->id,
            'expense_date' => now()->toDateString(),
        ]);

        $share = ExpenseShare::create([
            'expense_id' => $expense->id,
            'tour_member_id' => $members[0]->id,
            'share_amount' => 0,
        ]);

        Payment::create([
            'share_id' => $share->id,
            'amount' => 100,
            'method' => 'bkash',
            'status' => 'verified',
            'submitted_at' => now(),
        ]);

        $financials = $service->calculateTourFinancials($tour);

        // Member 1 paid 100, share is 0 -> Net Balance = +100 -> Refund 100
        $this->assertEquals(100.00, $financials['members'][0]['refund_amount']);
        $this->assertEquals(100.00, $financials['members'][0]['net_balance']);

        // The other 4 members paid 0 -> Refund MUST be 0!
        for ($i = 1; $i <= 4; $i++) {
            $this->assertEquals(0.00, $financials['members'][$i]['refund_amount']);
            $this->assertEquals(0.00, $financials['members'][$i]['total_paid']);
            $this->assertEquals('Settled', $financials['members'][$i]['settlement_status']);
        }

        // Now test emergency finish action
        $response = $this->actingAs($this->manager)->post("/manager/tours/{$tour->id}/emergency-finish", [
            'emergency_reason' => 'Extreme weather forecast and flash floods',
        ]);

        $response->assertSessionHas('success');

        $tour->refresh();
        $this->assertEquals('emergency', $tour->finish_type);
        $this->assertEquals('completed', $tour->status);
        $this->assertEquals('Extreme weather forecast and flash floods', $tour->emergency_reason);

        // Member 1 has a refund of 100
        $refund1 = Refund::where('tour_id', $tour->id)->where('tour_member_id', $members[0]->id)->first();
        $this->assertNotNull($refund1);
        $this->assertEquals(100.00, (float) $refund1->refund_amount);

        // Members 2 to 5 have NO refund records
        $otherRefunds = Refund::where('tour_id', $tour->id)->where('tour_member_id', '!=', $members[0]->id)->count();
        $this->assertEquals(0, $otherRefunds);
    }

    /**
     * Requirement 7: Refund Management (Pending -> Refunded)
     */
    public function test_manager_can_update_refund_status_manually(): void
    {
        $tour = Tour::create([
            'tour_name' => 'Refund Tracking Tour',
            'start_location' => 'Dhaka',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'estimated_budget' => 50000,
            'status' => 'completed',
            'created_by' => $this->manager->id,
        ]);

        $member = TourMember::create(['tour_id' => $tour->id, 'name' => 'Farhan']);

        $refund = Refund::create([
            'tour_id' => $tour->id,
            'tour_member_id' => $member->id,
            'refund_amount' => 2500.00,
            'status' => 'pending',
            'method' => 'bkash',
        ]);

        // Manager physically returns money and updates status
        $response = $this->actingAs($this->manager)->post("/manager/refunds/{$refund->id}/status", [
            'status' => 'refunded',
            'method' => 'bkash',
            'notes' => 'Returned via bKash Trx 772183921',
        ]);

        $response->assertSessionHas('success');

        $refund->refresh();
        $this->assertEquals('refunded', $refund->status);
        $this->assertEquals($this->manager->id, $refund->refunded_by);
        $this->assertNotNull($refund->refund_date);
    }

    /**
     * Requirement 8: Super Admin Monitoring (Strictly Read-Only)
     */
    public function test_super_admin_can_monitor_everything_read_only(): void
    {
        $tour = Tour::create([
            'tour_name' => 'Sreemangal Tea Garden',
            'start_location' => 'Dhaka',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'estimated_budget' => 80000,
            'status' => 'completed',
            'finish_type' => 'emergency',
            'emergency_reason' => 'Landslide blocked roads',
            'created_by' => $this->manager->id,
        ]);

        $member = TourMember::create(['tour_id' => $tour->id, 'name' => 'Naveed']);

        $expense = Expense::create([
            'tour_id' => $tour->id,
            'expense_name' => 'Microbus Rent',
            'category_id' => $this->category->id,
            'total_amount' => 15000,
            'added_by' => $this->manager->id,
            'expense_date' => now()->toDateString(),
        ]);

        $share = ExpenseShare::create([
            'expense_id' => $expense->id,
            'tour_member_id' => $member->id,
            'share_amount' => 15000,
        ]);

        Payment::create([
            'share_id' => $share->id,
            'amount' => 15000,
            'method' => 'cash',
            'status' => 'verified',
            'submitted_at' => now(),
        ]);

        // 1. Super Admin can view All Tours
        $response = $this->actingAs($this->superAdmin)->get('/admin/tours');
        $response->assertStatus(200);
        $response->assertSee('Sreemangal Tea Garden');
        $response->assertSee('Emergency Finished');

        // 2. Super Admin can view Detailed Tour Audit (Read-Only)
        $response = $this->actingAs($this->superAdmin)->get("/admin/tours/{$tour->id}");
        $response->assertStatus(200);
        $response->assertSee('Super Admin Audit Mode (Read-Only)');
        $response->assertSee('Landslide blocked roads');
        $response->assertSee('Microbus Rent');
        $response->assertSee('Naveed');
        // Ensure no mutating action buttons exist
        $response->assertDontSee('Finish Tour & Settle');
        $response->assertDontSee('Record Manual Payment');

        // 3. Super Admin can view Master Expenses
        $response = $this->actingAs($this->superAdmin)->get('/admin/financials/expenses');
        $response->assertStatus(200);
        $response->assertSee('Microbus Rent');

        // 4. Super Admin can view Master Payments
        $response = $this->actingAs($this->superAdmin)->get('/admin/financials/payments');
        $response->assertStatus(200);
        $response->assertSee('Naveed');

        // 5. Super Admin can view Master Refunds
        $response = $this->actingAs($this->superAdmin)->get('/admin/financials/refunds');
        $response->assertStatus(200);
    }
}
