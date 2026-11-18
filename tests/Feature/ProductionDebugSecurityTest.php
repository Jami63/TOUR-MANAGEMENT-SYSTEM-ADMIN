<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductionDebugSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register a test crash endpoint for testing exception rendering
        Route::get('/api/test-server-crash', function () {
            throw new \RuntimeException('Simulated fatal database error: root:SuperSecretPassword123@127.0.0.1');
        });
    }

    public function test_production_mode_masks_unhandled_exceptions_and_leaks_zero_secrets(): void
    {
        // Enforce production mode with debug disabled
        config(['app.debug' => false]);
        config(['app.env' => 'production']);

        $response = $this->getJson('/api/test-server-crash');

        // 1. Must return 500
        $response->assertStatus(500);

        // 2. Must return standardized SafarFly error response
        $response->assertJson([
            'success' => false,
            'message' => 'An unexpected server error occurred. Please try again later.',
        ]);

        $content = $response->getContent();

        // 3. Must NEVER leak internal error message, database credentials, or stack traces
        $this->assertStringNotContainsString('SuperSecretPassword123', $content);
        $this->assertStringNotContainsString('trace', $content);
        $this->assertStringNotContainsString('RuntimeException', $content);
        $this->assertStringNotContainsString('file', $content);
        $this->assertStringNotContainsString('line', $content);
    }

    public function test_unauthenticated_api_request_returns_clean_401_json(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated. Please log in to continue.',
            ]);
    }

    public function test_nonexistent_api_route_returns_clean_404_json(): void
    {
        $response = $this->getJson('/api/unknown-endpoint-xyz-999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Requested resource or API endpoint not found.',
            ]);
    }

    public function test_validation_errors_return_clean_422_without_stack_traces(): void
    {
        config(['app.debug' => false]);

        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);

        $content = $response->getContent();
        $this->assertStringNotContainsString('trace', $content);
        $this->assertStringNotContainsString('exception', $content);
    }

    public function test_production_environment_file_has_debug_mode_disabled(): void
    {
        $prodEnvPath = base_path('.env.production');

        $this->assertFileExists($prodEnvPath);

        $prodEnvContent = file_get_contents($prodEnvPath);

        $this->assertStringContainsString('APP_ENV=production', $prodEnvContent);
        $this->assertStringContainsString('APP_DEBUG=false', $prodEnvContent);
    }
}
