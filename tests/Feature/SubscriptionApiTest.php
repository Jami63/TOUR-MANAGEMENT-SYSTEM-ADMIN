<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_dynamic_subscription_plans(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/subscription/plans');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'plans')
            ->assertJsonPath('plans.0.id', 'pro_monthly')
            ->assertJsonPath('plans.1.id', 'pro_yearly')
            ->assertJsonPath('plans.1.badge', 'SAVE 30%');
    }

    public function test_free_user_has_free_tier_status(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'free',
            'subscription_expires_at' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/subscription/status');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_pro', false)
            ->assertJsonPath('tier', 'free');
    }

    public function test_user_can_subscribe_to_pro_monthly(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'free',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/subscription/subscribe', [
            'plan_id' => 'pro_monthly',
            'payment_method' => 'google_play',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.is_pro', true)
            ->assertJsonPath('user.subscription_tier', 'pro')
            ->assertJsonPath('user.subscription_plan', 'pro_monthly');

        $user->refresh();
        $this->assertTrue($user->isPro());
        $this->assertEquals('pro', $user->subscription_tier);
        $this->assertEquals('pro_monthly', $user->subscription_plan);
        $this->assertNotNull($user->subscription_expires_at);
        $this->assertTrue($user->subscription_expires_at->isFuture());

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => 'pro_monthly',
            'status' => 'active',
            'payment_method' => 'google_play',
        ]);
    }

    public function test_user_can_subscribe_to_pro_yearly_and_cancel_renewal(): void
    {
        $user = User::factory()->create([
            'subscription_tier' => 'free',
        ]);

        // Subscribe to yearly
        $subResponse = $this->actingAs($user, 'sanctum')->postJson('/api/subscription/subscribe', [
            'plan_id' => 'pro_yearly',
            'payment_method' => 'apple_iap',
        ]);

        $subResponse->assertOk()
            ->assertJsonPath('user.subscription_plan', 'pro_yearly')
            ->assertJsonPath('user.is_pro', true);

        // Cancel auto-renewal
        $cancelResponse = $this->actingAs($user, 'sanctum')->postJson('/api/subscription/cancel');

        $cancelResponse->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => 'pro_yearly',
            'status' => 'cancelled',
        ]);
    }

    public function test_invalid_plan_id_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/subscription/subscribe', [
            'plan_id' => 'invalid_plan_xyz',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan_id']);
    }

    public function test_unauthenticated_access_is_rejected(): void
    {
        $response = $this->getJson('/api/subscription/status');

        $response->assertStatus(401);
    }

    public function test_subscribing_when_already_active_stacks_and_extends_future_expiration(): void
    {
        $futureDate = now()->addDays(20);
        $user = User::factory()->create([
            'subscription_tier' => 'pro',
            'subscription_plan' => 'pro_monthly',
            'subscription_expires_at' => $futureDate,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/subscription/subscribe', [
            'plan_id' => 'pro_monthly',
        ]);

        $response->assertOk();
        $user->refresh();

        // Must be extended from the 20-day future date by 1 month (~50 days from now), NOT from today
        $this->assertTrue($user->subscription_expires_at->gt(now()->addDays(45)));
    }
}
