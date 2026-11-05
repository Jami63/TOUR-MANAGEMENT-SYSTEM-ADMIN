<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Welcome back');
    }

    public function test_super_admin_can_login_and_see_account_creations(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@safarflow.test')->first();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Super Admin Console');
        $response->assertSee('User Registrations & Accounts');
        $response->assertSee('Tour Manager');
        $response->assertSee('Nusrat Jahan');
        $response->assertSee('Tariqul Islam');

        // Confirm Super Admin is strictly read-only
        $response->assertDontSee('+ Create New Tour');
        $response->assertDontSee('Verify Payments');
    }
}
