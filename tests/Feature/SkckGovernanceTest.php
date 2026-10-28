<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Models\OperationalRegion;
use App\Models\SkckAccessRequest;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkckGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $adminJkt;

    protected OperationalRegion $regionJkt;

    protected VendorProfile $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'is_active' => true,
        ]);

        $this->adminJkt = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $this->regionJkt = OperationalRegion::create([
            'name' => 'Wilayah DKI Jakarta & Sekitarnya',
            'code' => 'REG-JKT',
            'admin_id' => $this->adminJkt->id,
            'cities' => ['Jakarta Pusat', 'Jakarta Selatan'],
            'provinces' => ['DKI Jakarta'],
            'is_active' => true,
        ]);

        $vendorUser = User::factory()->create(['role' => UserRole::Vendor]);
        $this->vendor = VendorProfile::factory()->create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Loker Gambir Sejahtera',
            'city' => 'Jakarta Pusat',
            'verification_status' => VendorVerificationStatus::Pending,
            'is_open' => true,
            'skck_path' => 'documents/skck_sample.pdf',
            'skck_expires_at' => now()->addMonths(6),
        ]);
    }

    public function test_admin_can_request_revision_for_vendor_kyc(): void
    {
        $response = $this->actingAs($this->adminJkt)->post(route('admin.vendors.request_revision', $this->vendor->id), [
            'revision_notes' => 'Foto KTP buram dan SKCK tidak terbaca jelas. Mohon unggah ulang.',
        ]);

        $response->assertRedirect(route('admin.vendors.show', $this->vendor->id));

        $this->vendor->refresh();
        $this->assertEquals(VendorVerificationStatus::NeedsRevision, $this->vendor->verification_status);
        $this->assertEquals('Foto KTP buram dan SKCK tidak terbaca jelas. Mohon unggah ulang.', $this->vendor->revision_notes);
        $this->assertFalse($this->vendor->is_open);
    }

    public function test_admin_cannot_access_raw_skck_without_superadmin_approval(): void
    {
        // Policy 6.3: Operational admin is denied raw document access without explicit approval
        $response = $this->actingAs($this->adminJkt)->get(route('admin.vendors.download_skck', $this->vendor->id));

        $response->assertStatus(403);
    }

    public function test_admin_can_request_skck_access_and_superadmin_can_approve(): void
    {
        // 1. Admin submits access request
        $requestResponse = $this->actingAs($this->adminJkt)->post(route('admin.vendors.request_skck', $this->vendor->id), [
            'reason' => 'Perlu memverifikasi keaslian stempel kepolisian pada berkas SKCK.',
        ]);
        $requestResponse->assertRedirect();

        $accessRequest = SkckAccessRequest::where('admin_id', $this->adminJkt->id)->first();
        $this->assertNotNull($accessRequest);
        $this->assertEquals('pending', $accessRequest->status);

        // 2. Superadmin approves the request
        $approveResponse = $this->actingAs($this->superadmin)->post(route('admin.skck_requests.approve', $accessRequest->id));
        $approveResponse->assertRedirect();

        $accessRequest->refresh();
        $this->assertEquals('approved', $accessRequest->status);
        $this->assertTrue($accessRequest->isValidAccess());

        // 3. Admin is now granted time-bound access
        $documentResponse = $this->actingAs($this->adminJkt)->get(route('admin.vendors.download_skck', $this->vendor->id));
        $documentResponse->assertStatus(200);
    }

    public function test_superadmin_has_direct_skck_document_access(): void
    {
        $response = $this->actingAs($this->superadmin)->get(route('admin.vendors.download_skck', $this->vendor->id));

        $response->assertStatus(200);
    }

    public function test_vendor_can_still_accept_bookings_during_30_day_grace_period(): void
    {
        // SKCK expired 5 days ago (still in 30-day grace period per Policy 6.2)
        $this->vendor->update([
            'verification_status' => VendorVerificationStatus::Verified,
            'is_open' => true,
            'skck_expires_at' => now()->subDays(5),
            'skck_grace_period_ends_at' => now()->addDays(25),
        ]);

        $this->assertTrue($this->vendor->isInSkckGracePeriod());
        $this->assertFalse($this->vendor->isSkckBlocked());
        $this->assertTrue($this->vendor->canAcceptBookings());
    }

    public function test_vendor_is_blocked_from_new_bookings_when_grace_period_expires(): void
    {
        // SKCK expired 40 days ago (grace period ended 10 days ago)
        $this->vendor->update([
            'verification_status' => VendorVerificationStatus::Verified,
            'is_open' => true,
            'skck_expires_at' => now()->subDays(40),
            'skck_grace_period_ends_at' => now()->subDays(10),
        ]);

        $this->artisan('titipsini:check-skck-expiry')->assertSuccessful();

        $this->vendor->refresh();
        $this->assertEquals('expired_blocked', $this->vendor->skck_status);
        $this->assertFalse($this->vendor->is_open);
        $this->assertFalse($this->vendor->canAcceptBookings());
    }

    public function test_skck_raw_file_is_pruned_after_7_days_post_verification(): void
    {
        // Policy 6.3: Document retention auto-pruning after 7 days
        $this->vendor->update([
            'verification_status' => VendorVerificationStatus::Verified,
            'verified_at' => now()->subDays(8),
            'skck_path' => 'documents/old_skck.pdf',
            'skck_pruned_at' => null,
        ]);

        $this->artisan('titipsini:check-skck-expiry')->assertSuccessful();

        $this->vendor->refresh();
        $this->assertNull($this->vendor->skck_path);
        $this->assertNotNull($this->vendor->skck_pruned_at);
    }

    public function test_privacy_request_anonymizes_sensitive_documents(): void
    {
        $this->vendor->update([
            'verification_status' => VendorVerificationStatus::Verified,
            'skck_path' => 'documents/skck.pdf',
            'id_card_path' => 'documents/ktp.pdf',
        ]);

        $response = $this->actingAs($this->adminJkt)->post(route('admin.vendors.privacy_anonymize', $this->vendor->id));
        $response->assertRedirect();

        $this->vendor->refresh();
        $this->assertNull($this->vendor->skck_path);
        $this->assertNull($this->vendor->id_card_path);
        $this->assertNotNull($this->vendor->privacy_anonymized_at);
    }
}
