<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthRouteSeparationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'full_name' => 'SafarFly Chief Admin',
            'email' => 'superadmin@safarfly.com',
            'phone' => '01711000099',
            'password' => Hash::make('AdminSecret123!'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        $this->manager = User::create([
            'full_name' => 'Tour Manager Rafiq',
            'email' => 'manager.rafiq@safarfly.com',
            'phone' => '01711000088',
            'password' => Hash::make('ManagerSecret123!'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Test 1: Dedicated Admin Login Screen renders high-security UI without registration links.
     */
    public function test_admin_login_screen_loads_with_security_terminal_ui(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Admin Terminal');
        $response->assertSee('Restricted Portal');
        $response->assertSee('Authorized Personnel Only');
        $response->assertSee('Access Console');

        // Crucial security guarantee: public registration is strictly removed from admin terminal
        $response->assertDontSee('Create an account');
        $response->assertDontSee("Don't have an account yet?");
    }

    /**
     * Test 2: Super Admin can authenticate via dedicated /admin/login and access dashboard.
     */
    public function test_super_admin_can_authenticate_via_admin_terminal(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'superadmin@safarfly.com',
            'password' => 'AdminSecret123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    /**
     * Test 3: Tour Manager entering valid credentials at /admin/login is strictly rejected & logged out.
     */
    public function test_tour_manager_is_strictly_rejected_at_admin_terminal(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'manager.rafiq@safarfly.com',
            'password' => 'ManagerSecret123!',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email']);

        // User must remain a guest (not authenticated)
        $this->assertGuest();
    }

    /**
     * Test 4: Unauthenticated access to /admin/* routes redirects strictly to /admin/login.
     */
    public function test_unauthenticated_admin_routes_redirect_to_admin_login(): void
    {
        $dashboardResponse = $this->get('/admin/dashboard');
        $dashboardResponse->assertRedirect(route('admin.login'));

        $toursResponse = $this->get('/admin/tours');
        $toursResponse->assertRedirect(route('admin.login'));

        $financialsResponse = $this->get('/admin/financials/expenses');
        $financialsResponse->assertRedirect(route('admin.login'));
    }

    /**
     * Test 5: Unauthenticated access to /manager/* routes redirects strictly to public /login.
     */
    public function test_unauthenticated_manager_routes_redirect_to_public_login(): void
    {
        $managerResponse = $this->get('/manager/dashboard');
        $managerResponse->assertRedirect(route('login'));

        $createResponse = $this->get('/manager/tours/create');
        $createResponse->assertRedirect(route('login'));
    }

    /**
     * Test 6: Super Admin logout terminates session and returns to /admin/login.
     */
    public function test_admin_logout_terminates_session_and_redirects_to_admin_login(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/admin/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    /**
     * Test 7: Public /login remains fully operational for Tour Managers.
     */
    public function test_public_login_allows_tour_manager_to_reach_manager_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'manager.rafiq@safarfly.com',
            'password' => 'ManagerSecret123!',
        ]);

        $response->assertRedirect(route('manager.dashboard'));
        $this->assertAuthenticatedAs($this->manager);
    }

    /**
     * Test 8: Public /login page provides dedicated navigational link to Admin Terminal.
     */
    public function test_public_login_page_links_to_super_admin_terminal(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee(route('admin.login'));
        $response->assertSee('Super Administrator Terminal');
    }

    /**
     * Test 9: Authenticated Super Admin visiting /admin/login is redirected to admin dashboard.
     */
    public function test_authenticated_admin_visiting_login_redirects_to_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/login');
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test 10: Authenticated Tour Manager visiting /admin/login is barred and redirected to manager dashboard.
     */
    public function test_authenticated_manager_visiting_admin_login_is_redirected_to_manager_dashboard(): void
    {
        $response = $this->actingAs($this->manager)->get('/admin/login');
        $response->assertRedirect(route('manager.dashboard'));
        $response->assertSessionHas('error');
    }
}
