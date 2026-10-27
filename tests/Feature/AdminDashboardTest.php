<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\SettlementStatus;
use App\Enums\VendorVerificationStatus;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorSettlement;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    private User $admin;

    private User $vendorUser;

    private VendorProfile $vendorProfile;

    private User $customer;

    private Listing $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->superadmin()->create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@example.com',
        ]);

        $this->admin = User::factory()->admin()->create([
            'name' => 'Ops Admin Test',
            'email' => 'opsadmin@example.com',
        ]);

        $this->customer = User::factory()->customer()->create([
            'name' => 'Customer Biasa',
            'email' => 'customer@example.com',
        ]);

        $this->vendorUser = User::factory()->vendor()->create([
            'name' => 'Vendor Test',
            'email' => 'vendor@example.com',
        ]);

        $this->vendorProfile = VendorProfile::factory()->create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Loker Manggarai Express',
            'verification_status' => VendorVerificationStatus::Pending,
            'is_open' => false,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Mitra Manggarai',
        ]);

        $category = Category::create([
            'name' => 'Koper Wisata',
            'slug' => 'koper-wisata',
            'is_active' => true,
        ]);

        $this->listing = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $category->id,
            'title' => 'Loker Manggarai Lantai 1',
            'slug' => 'loker-manggarai-lantai-1',
            'description' => 'Dekat tangga peron',
            'address' => 'Jl. Stasiun Manggarai',
            'city' => 'Jakarta Selatan',
            'price_per_hour' => 12000,
            'price_per_day' => 45000,
            'capacity_total' => 10,
            'capacity_available' => 10,
            'status' => ListingStatus::Submitted,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_admin(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_and_vendor_cannot_access_admin(): void
    {
        $this->actingAs($this->customer)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($this->vendorUser)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_admin_and_superadmin_can_view_dashboard(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSeeText('Platform Marketplace TitipSini');

        $responseSuper = $this->actingAs($this->superadmin)->get(route('admin.dashboard'));
        $responseSuper->assertOk();
        $responseSuper->assertSeeText('Pusat Kendali Eksekutif');
    }

    public function test_admin_can_view_listings_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.listings.index'));
        $response->assertOk();
        $response->assertSeeText($this->listing->title);
        $response->assertSeeText('Menunggu Moderasi');
    }

    public function test_admin_can_view_listing_detail(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.listings.show', $this->listing->id));
        $response->assertOk();
        $response->assertSeeText($this->listing->title);
    }

    public function test_admin_can_view_vendors_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.index'));
        $response->assertOk();
        $response->assertSeeText($this->vendorProfile->business_name);
    }

    public function test_admin_can_approve_vendor_kyc(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.vendors.approve', $this->vendorProfile->id));

        $response->assertRedirect(route('admin.vendors.show', $this->vendorProfile->id));
        $response->assertSessionHas('success');

        $fresh = $this->vendorProfile->fresh();
        $this->assertSame(VendorVerificationStatus::Verified, $fresh->verification_status);
        $this->assertTrue($fresh->is_open);
        $this->assertNotNull($fresh->verified_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_vendor_kyc_approved',
            'auditable_id' => $this->vendorProfile->id,
        ]);
    }

    public function test_admin_can_reject_vendor_kyc_with_reason(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.vendors.reject', $this->vendorProfile->id), [
                'rejection_reason' => 'Foto KTP buram dan tidak terbaca jelas.',
            ]);

        $response->assertRedirect(route('admin.vendors.show', $this->vendorProfile->id));

        $fresh = $this->vendorProfile->fresh();
        $this->assertSame(VendorVerificationStatus::Rejected, $fresh->verification_status);
        $this->assertSame('Foto KTP buram dan tidak terbaca jelas.', $fresh->rejection_reason);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_vendor_kyc_rejected',
            'auditable_id' => $this->vendorProfile->id,
        ]);
    }

    public function test_admin_can_publish_moderated_listing(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.listings.publish', $this->listing->id));

        $response->assertRedirect(route('admin.listings.show', $this->listing->id));

        $this->assertSame(ListingStatus::Published, $this->listing->fresh()->status);
        $this->assertNotNull($this->listing->fresh()->published_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_listing_published',
            'auditable_id' => $this->listing->id,
        ]);
    }

    public function test_admin_can_reject_moderated_listing(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.listings.reject', $this->listing->id));

        $response->assertRedirect(route('admin.listings.show', $this->listing->id));
        $this->assertSame(ListingStatus::Rejected, $this->listing->fresh()->status);
    }

    public function test_admin_can_view_financial_reconciliation_and_retry_payout(): void
    {
        $bookingService = app(BookingService::class);
        $booking = $bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->format('Y-m-d H:i'),
        ]);

        $settlement = VendorSettlement::create([
            'booking_id' => $booking->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'amount' => 36000.00,
            'status' => SettlementStatus::Pending,
        ]);

        $response = $this->actingAs($this->superadmin)->get(route('admin.finance.index'));
        $response->assertOk();
        $response->assertSeeText('Loker Manggarai Express');

        // Test manual retry payout
        $retryResponse = $this->actingAs($this->superadmin)
            ->post(route('admin.finance.retry_payout', $settlement->id));

        $retryResponse->assertRedirect();
        $retryResponse->assertSessionHas('success');

        $this->assertSame(SettlementStatus::Settled, $settlement->fresh()->status);
    }

    public function test_admin_can_view_system_audit_logs(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'admin_system_test_action',
            'auditable_type' => Listing::class,
            'auditable_id' => $this->listing->id,
            'new_values' => ['test' => 'sample'],
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audit_logs.index'));
        $response->assertOk();
        $response->assertSeeText('admin_system_test_action');
    }
}
