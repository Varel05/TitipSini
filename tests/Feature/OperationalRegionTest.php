<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\OperationalRegion;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalRegionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $adminJkt;

    protected User $adminSolo;

    protected User $adminUnassigned;

    protected OperationalRegion $regionJkt;

    protected OperationalRegion $regionDiy;

    protected Listing $listingJkt;

    protected Listing $listingDiy;

    protected VendorProfile $vendorJkt;

    protected VendorProfile $vendorDiy;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Roles
        $this->superadmin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'is_active' => true,
        ]);

        $this->adminJkt = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->adminSolo = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->adminUnassigned = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        // 2. Setup Operational Regions (Policy 2.1 & 3.1)
        $this->regionJkt = OperationalRegion::create([
            'name' => 'Wilayah DKI Jakarta & Sekitarnya',
            'code' => 'REG-JKT',
            'admin_id' => $this->adminJkt->id,
            'provinces' => ['DKI Jakarta'],
            'cities' => ['Jakarta Pusat', 'Jakarta Selatan', 'Jakarta Barat'],
            'is_active' => true,
            'is_under_supervision' => false,
        ]);

        $this->regionDiy = OperationalRegion::create([
            'name' => 'Wilayah DI Yogyakarta & Surakarta',
            'code' => 'REG-DIY',
            'admin_id' => $this->adminSolo->id,
            'provinces' => ['DI Yogyakarta', 'Jawa Tengah'],
            'cities' => ['Yogyakarta', 'Sleman', 'Bantul', 'Surakarta'],
            'is_active' => true,
            'is_under_supervision' => false,
        ]);

        $category = Category::create([
            'name' => 'Penitipan Traveling',
            'slug' => 'penitipan-traveling',
            'description' => 'Penitipan koper kabin & travel',
            'icon' => 'briefcase',
        ]);

        // 3. Setup Vendors & Listings in Region JKT
        $vendorUser1 = User::factory()->create(['role' => UserRole::Vendor]);
        $this->vendorJkt = VendorProfile::factory()->create([
            'user_id' => $vendorUser1->id,
            'business_name' => 'Titip Gambir Jaya',
            'city' => 'Jakarta Pusat',
            'verification_status' => VendorVerificationStatus::Pending,
        ]);

        $this->listingJkt = Listing::create([
            'vendor_profile_id' => $this->vendorJkt->id,
            'category_id' => $category->id,
            'operational_region_id' => $this->regionJkt->id,
            'title' => 'Loker Gambir Eksekutif',
            'slug' => 'loker-gambir-eksekutif',
            'tagline' => 'Nyaman dan aman',
            'description' => 'Deskripsi loker gambir',
            'address' => 'Jl. Kebon Sirih No. 1',
            'city' => 'Jakarta Pusat',
            'district' => 'Gambir',
            'postal_code' => '10110',
            'latitude' => -6.175,
            'longitude' => 106.825,
            'price_per_hour' => 10000,
            'price_per_day' => 35000,
            'capacity_total' => 10,
            'capacity_available' => 10,
            'status' => ListingStatus::Submitted,
        ]);

        // 4. Setup Vendors & Listings in Region DIY
        $vendorUser2 = User::factory()->create(['role' => UserRole::Vendor]);
        $this->vendorDiy = VendorProfile::factory()->create([
            'user_id' => $vendorUser2->id,
            'business_name' => 'Titip Malioboro',
            'city' => 'Yogyakarta',
            'verification_status' => VendorVerificationStatus::Pending,
        ]);

        $this->listingDiy = Listing::create([
            'vendor_profile_id' => $this->vendorDiy->id,
            'category_id' => $category->id,
            'operational_region_id' => $this->regionDiy->id,
            'title' => 'Loker Stasiun Tugu',
            'slug' => 'loker-stasiun-tugu',
            'tagline' => 'Loker dekat stasiun tugu',
            'description' => 'Deskripsi loker stasiun tugu',
            'address' => 'Jl. Mangkubumi No. 10',
            'city' => 'Yogyakarta',
            'district' => 'Gedongtengen',
            'postal_code' => '55271',
            'latitude' => -7.789,
            'longitude' => 110.364,
            'price_per_hour' => 10000,
            'price_per_day' => 35000,
            'capacity_total' => 10,
            'capacity_available' => 10,
            'status' => ListingStatus::Submitted,
        ]);
    }

    public function test_admin_can_only_see_listings_in_their_assigned_region(): void
    {
        $response = $this->actingAs($this->adminJkt)->get(route('admin.listings.index'));

        $response->assertStatus(200);
        $response->assertSee('Loker Gambir Eksekutif');
        $response->assertDontSee('Loker Stasiun Tugu');
    }

    public function test_admin_cannot_view_listing_details_outside_their_region(): void
    {
        // Admin JKT attempts to view DIY listing detail
        $response = $this->actingAs($this->adminJkt)->get(route('admin.listings.show', $this->listingDiy->id));

        $response->assertStatus(403);
    }

    public function test_admin_cannot_publish_or_reject_listing_outside_their_region(): void
    {
        // Admin JKT attempts to publish DIY listing
        $publishResponse = $this->actingAs($this->adminJkt)->post(route('admin.listings.publish', $this->listingDiy->id));
        $publishResponse->assertStatus(403);

        // Admin JKT attempts to reject DIY listing
        $rejectResponse = $this->actingAs($this->adminJkt)->post(route('admin.listings.reject', $this->listingDiy->id));
        $rejectResponse->assertStatus(403);

        $this->assertEquals(ListingStatus::Submitted, $this->listingDiy->fresh()->status);
    }

    public function test_admin_can_publish_listing_in_their_region(): void
    {
        $response = $this->actingAs($this->adminJkt)->post(route('admin.listings.publish', $this->listingJkt->id));

        $response->assertRedirect(route('admin.listings.show', $this->listingJkt->id));
        $this->assertEquals(ListingStatus::Published, $this->listingJkt->fresh()->status);
    }

    public function test_admin_can_only_see_and_moderate_vendors_in_their_region(): void
    {
        // Index list check
        $response = $this->actingAs($this->adminJkt)->get(route('admin.vendors.index'));
        $response->assertStatus(200);
        $response->assertSee('Titip Gambir Jaya');
        $response->assertDontSee('Titip Malioboro');

        // Show detail check
        $forbiddenResponse = $this->actingAs($this->adminJkt)->get(route('admin.vendors.show', $this->vendorDiy->id));
        $forbiddenResponse->assertStatus(403);

        // Approve check
        $forbiddenApprove = $this->actingAs($this->adminJkt)->post(route('admin.vendors.approve', $this->vendorDiy->id));
        $forbiddenApprove->assertStatus(403);
    }

    public function test_superadmin_has_unrestricted_nationwide_access(): void
    {
        // Superadmin sees all listings across regions
        $response = $this->actingAs($this->superadmin)->get(route('admin.listings.index'));
        $response->assertStatus(200);
        $response->assertSee('Loker Gambir Eksekutif');
        $response->assertSee('Loker Stasiun Tugu');

        // Superadmin can view and publish any listing
        $detailResponse = $this->actingAs($this->superadmin)->get(route('admin.listings.show', $this->listingDiy->id));
        $detailResponse->assertStatus(200);

        $publishResponse = $this->actingAs($this->superadmin)->post(route('admin.listings.publish', $this->listingDiy->id));
        $publishResponse->assertRedirect(route('admin.listings.show', $this->listingDiy->id));
        $this->assertEquals(ListingStatus::Published, $this->listingDiy->fresh()->status);
    }

    public function test_admin_without_assigned_region_sees_empty_queue(): void
    {
        $response = $this->actingAs($this->adminUnassigned)->get(route('admin.listings.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Loker Gambir Eksekutif');
        $response->assertDontSee('Loker Stasiun Tugu');
    }

    public function test_admin_is_forbidden_from_internal_audit_logs(): void
    {
        // Policy 13.2: Operational Admin must be denied access to audit logs
        $response = $this->actingAs($this->adminJkt)->get(route('admin.audit_logs.index'));
        $response->assertStatus(403);

        // Superadmin is authorized
        $superadminResponse = $this->actingAs($this->superadmin)->get(route('admin.audit_logs.index'));
        $superadminResponse->assertStatus(200);
    }

    public function test_admin_is_forbidden_from_financial_reconciliation(): void
    {
        // Policy 13.3: Operational Admin must be denied global financial reconciliation
        $response = $this->actingAs($this->adminJkt)->get(route('admin.finance.index'));
        $response->assertStatus(403);

        // Superadmin is authorized
        $superadminResponse = $this->actingAs($this->superadmin)->get(route('admin.finance.index'));
        $superadminResponse->assertStatus(200);
    }
}
