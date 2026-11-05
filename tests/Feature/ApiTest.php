<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_login_and_return_sanctum_token(): void
    {
        $this->seed();

        $response = $this->postJson('/api/login', [
            'email' => 'manager@safarflow.test',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'token',
            'user' => ['id', 'full_name', 'email', 'role'],
        ]);
    }

    public function test_api_can_fetch_tours_with_bearer_token(): void
    {
        $this->seed();

        $user = User::where('email', 'manager@safarflow.test')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/tours');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'tours',
        ]);
    }
}
