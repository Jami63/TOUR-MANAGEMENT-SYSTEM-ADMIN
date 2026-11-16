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

    public function test_profile_photo_endpoint_rejects_json_files_and_json_urls(): void
    {
        $this->seed();

        $user = User::where('email', 'manager@safarflow.test')->first();
        $token = $user->createToken('test')->plainTextToken;

        // 1. Uploaded .json file must be rejected with 422
        $tempJson = tempnam(sys_get_temp_dir(), 'test').'.json';
        file_put_contents($tempJson, '{"attack": true}');
        $file = new \Illuminate\Http\UploadedFile($tempJson, 'avatar.json', 'application/json', null, true);

        $res1 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/profile/photo', ['photo' => $file]);
        $res1->assertStatus(422);
        @unlink($tempJson);

        // 2. URL pointing to a .json file must be rejected with 422
        $res2 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/profile/photo', ['profile_picture' => 'https://example.com/avatar.json']);
        $res2->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // 3. Raw JSON text payload must be rejected with 422
        $res3 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/profile/photo', ['profile_picture' => '{"id": 1, "name": "Fake"}']);
        $res3->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        // 4. Valid image URL is accepted with 200
        $res4 = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/profile/photo', ['profile_picture' => 'https://example.com/photos/avatar.png']);
        $res4->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
