<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_reset_password(): void
    {
        $user = User::factory()->create([
            'role' => 'manager',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/profile/password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword456',
            'new_password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword456', $user->password));
    }

    public function test_password_reset_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'manager',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/profile/password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword456',
        ]);

        $response->assertStatus(422);
    }
}
