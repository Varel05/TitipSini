<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureUserHasRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'role:admin'])->get('/test/admin-only', function () {
            return response()->json(['message' => 'Admin area']);
        });

        Route::middleware(['web', 'role:vendor'])->get('/test/vendor-only', function () {
            return response()->json(['message' => 'Vendor area']);
        });

        Route::middleware(['web', 'role:customer'])->get('/test/customer-only', function () {
            return response()->json(['message' => 'Customer area']);
        });
    }

    public function test_unauthenticated_user_is_aborted(): void
    {
        $response = $this->getJson('/test/admin-only');
        $response->assertUnauthorized();
    }

    public function test_inactive_user_is_forbidden(): void
    {
        $inactiveAdmin = User::factory()->admin()->inactive()->create();

        $response = $this->actingAs($inactiveAdmin)->getJson('/test/admin-only');
        $response->assertForbidden();
    }

    public function test_customer_cannot_access_admin_route(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->getJson('/test/admin-only');
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_route(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->getJson('/test/admin-only');
        $response->assertOk()
            ->assertJson(['message' => 'Admin area']);
    }

    public function test_superadmin_can_access_admin_route(): void
    {
        $superadmin = User::factory()->superadmin()->create();

        $response = $this->actingAs($superadmin)->getJson('/test/admin-only');
        $response->assertOk()
            ->assertJson(['message' => 'Admin area']);
    }

    public function test_vendor_can_access_vendor_route(): void
    {
        $vendor = User::factory()->vendor()->create();

        $response = $this->actingAs($vendor)->getJson('/test/vendor-only');
        $response->assertOk()
            ->assertJson(['message' => 'Vendor area']);
    }
}
