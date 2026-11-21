<?php

namespace Tests\Feature;

use App\Models\EmailOtpVerification;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseShare;
use App\Models\Tour;
use App\Models\TourDestination;
use App\Models\TourMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PreLaunchEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Test 1: Tour Index returns both created tours and tours where user is a participant.
     */
    public function test_tour_index_returns_both_created_tours_and_joined_member_tours(): void
    {
        $creator = User::factory()->create(['role' => 'manager', 'full_name' => 'Alice Organizer']);
        $participantManager = User::factory()->create(['role' => 'manager', 'full_name' => 'Bob Traveler']);

        // Alice creates Tour 1
        $aliceTour = Tour::create([
            'created_by' => $creator->id,
            'tour_name' => 'Sylhet Tea Gardens Trek',
            'start_location' => 'Dhaka',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'estimated_budget' => 15000,
            'status' => 'ongoing',
        ]);
        TourDestination::create(['tour_id' => $aliceTour->id, 'destination_name' => 'Sreemangal', 'visit_order' => 1]);
        TourMember::create(['tour_id' => $aliceTour->id, 'user_id' => $creator->id, 'name' => 'Alice']);
        TourMember::create(['tour_id' => $aliceTour->id, 'user_id' => $participantManager->id, 'name' => 'Bob']);

        // Bob creates Tour 2
        $bobTour = Tour::create([
            'created_by' => $participantManager->id,
            'tour_name' => 'Cox\'s Bazar Coastal Expedition',
            'start_location' => 'Chittagong',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'estimated_budget' => 20000,
            'status' => 'ongoing',
        ]);
        TourDestination::create(['tour_id' => $bobTour->id, 'destination_name' => 'Inani Beach', 'visit_order' => 1]);
        TourMember::create(['tour_id' => $bobTour->id, 'user_id' => $participantManager->id, 'name' => 'Bob']);

        // Bob requests his tours
        $response = $this->actingAs($participantManager, 'sanctum')->getJson('/api/tours');

        $response->assertStatus(200);
        $tours = $response->json('tours');

        $this->assertCount(2, $tours);
        $tourIds = collect($tours)->pluck('id')->all();
        $this->assertContains($aliceTour->id, $tourIds, 'Invited member tour must be visible in list');
        $this->assertContains($bobTour->id, $tourIds, 'Self-created tour must be visible in list');
    }

    /**
     * Test 2: Pre-registration member invitations are auto-linked when the invitee signs up.
     */
    public function test_pre_registration_member_auto_links_when_user_registers(): void
    {
        $organizer = User::factory()->create(['role' => 'manager']);
        $tour = Tour::create([
            'created_by' => $organizer->id,
            'tour_name' => 'Bandarban Peak Climb',
            'start_location' => 'Dhaka',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'estimated_budget' => 25000,
            'status' => 'upcoming',
        ]);
        TourDestination::create(['tour_id' => $tour->id, 'destination_name' => 'Keokradong', 'visit_order' => 1]);

        // Organizer invites a friend who has not registered yet
        $unlinkedMember = TourMember::create([
            'tour_id' => $tour->id,
            'user_id' => null,
            'name' => 'Farhan Explorer',
            'email' => 'farhan@example.com',
            'phone' => '+8801700000001',
        ]);

        $this->assertNull($unlinkedMember->user_id);

        // Farhan registers and verifies OTP
        $otpRecord = EmailOtpVerification::create([
            'email' => 'farhan@example.com',
            'otp' => '654321',
            'payload' => [
                'full_name' => 'Farhan Explorer',
                'email' => 'farhan@example.com',
                'phone' => '+8801700000001',
                'password_hash' => Hash::make('Secret123!'),
                'role' => 'manager',
            ],
            'expires_at' => now()->addMinutes(10),
        ]);

        $verifyRes = $this->postJson('/api/verify-otp', [
            'email' => 'farhan@example.com',
            'otp' => '654321',
        ]);

        $verifyRes->assertStatus(201)->assertJson(['success' => true]);

        // The TourMember record must now be claimed and linked to Farhan's user_id
        $unlinkedMember->refresh();
        $this->assertNotNull($unlinkedMember->user_id);
        $this->assertEquals(User::where('email', 'farhan@example.com')->first()->id, $unlinkedMember->user_id);

        // Farhan can immediately retrieve the tour
        $farhan = User::where('email', 'farhan@example.com')->first();
        $toursRes = $this->actingAs($farhan, 'sanctum')->getJson('/api/tours');
        $toursRes->assertStatus(200);
        $this->assertCount(1, $toursRes->json('tours'));
        $this->assertEquals($tour->id, $toursRes->json('tours.0.id'));
    }

    /**
     * Test 3: Forgot password dispatches 6-digit OTP and reset-password updates password.
     */
    public function test_forgot_password_and_otp_reset_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'traveler@safarfly.com',
            'full_name' => 'Kamal Hossain',
            'password' => Hash::make('InitialPassword123'),
        ]);

        // Step 1: Request Password Reset
        $forgotRes = $this->postJson('/api/forgot-password', [
            'email' => 'traveler@safarfly.com',
        ]);

        $forgotRes->assertStatus(200)->assertJson([
            'success' => true,
            'email' => 'traveler@safarfly.com',
        ]);

        // Confirm OTP record exists with purpose = password_reset
        $otp = EmailOtpVerification::where('email', 'traveler@safarfly.com')
            ->latest()
            ->first();

        $this->assertNotNull($otp);
        $this->assertEquals('password_reset', $otp->payload['purpose']);
        $this->assertEquals(6, strlen($otp->otp));

        // Step 2: Reset Password with OTP
        $resetRes = $this->postJson('/api/reset-password', [
            'email' => 'traveler@safarfly.com',
            'otp' => $otp->otp,
            'password' => 'BrandNewPassword2026!',
        ]);

        $resetRes->assertStatus(200)->assertJson(['success' => true]);

        // Verify user can now log in with the new password
        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword2026!', $user->password));

        $loginRes = $this->postJson('/api/login', [
            'email' => 'traveler@safarfly.com',
            'password' => 'BrandNewPassword2026!',
        ]);
        $loginRes->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test 4: Emergency Tour Finish allows final settlement even when members have dues.
     */
    public function test_emergency_finish_tour_api(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $tour = Tour::create([
            'created_by' => $manager->id,
            'tour_name' => 'Sajek Valley Monsoon Trip',
            'start_location' => 'Khagrachhari',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'estimated_budget' => 10000,
            'status' => 'ongoing',
        ]);

        $cat = ExpenseCategory::create(['category_name' => 'Transport', 'is_active' => true]);

        $m1 = TourMember::create(['tour_id' => $tour->id, 'user_id' => $manager->id, 'name' => 'Manager']);
        $m2 = TourMember::create(['tour_id' => $tour->id, 'name' => 'Traveler With Due']);

        // Create an expense of 2000 BDT split between m1 and m2 (1000 due for m2)
        $expense = Expense::create([
            'tour_id' => $tour->id,
            'category_id' => $cat->id,
            'added_by' => $manager->id,
            'expense_name' => 'Jeep Rental',
            'total_amount' => 2000,
            'expense_date' => now()->toDateString(),
        ]);
        ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m1->id, 'share_amount' => 1000]);
        ExpenseShare::create(['expense_id' => $expense->id, 'tour_member_id' => $m2->id, 'share_amount' => 1000]);

        // Regular finish must fail due to unpaid dues
        $regularFinishRes = $this->actingAs($manager, 'sanctum')->postJson("/api/tours/{$tour->id}/finish");
        $regularFinishRes->assertStatus(422);

        // Emergency finish succeeds
        $emergencyRes = $this->actingAs($manager, 'sanctum')->postJson("/api/tours/{$tour->id}/emergency-finish", [
            'emergency_reason' => 'Trip cut short due to landslide; remaining dues waived.',
            'settlement_notes' => 'Settled with transport union.',
        ]);

        $emergencyRes->assertStatus(200)->assertJson(['success' => true]);

        $tour->refresh();
        $this->assertEquals('completed', $tour->status);
        $this->assertEquals('emergency', $tour->finish_type);
        $this->assertEquals('Trip cut short due to landslide; remaining dues waived.', $tour->emergency_reason);
    }
}
