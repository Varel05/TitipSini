<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\DisputeCategory;
use App\Enums\DisputeStatus;
use App\Enums\SettlementStatus;
use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Dispute;
use App\Models\Listing;
use App\Models\OperationalRegion;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorSettlement;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisputeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminJkt;

    protected User $adminBdg;

    protected User $superadmin;

    protected User $customer;

    protected User $vendorUser;

    protected VendorProfile $vendorProfile;

    protected Listing $listingJkt;

    protected Booking $booking;

    protected VendorSettlement $settlement;

    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = app(BookingService::class);

        $this->superadmin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'name' => 'Super Administrator',
        ]);

        $this->adminJkt = User::factory()->create([
            'role' => UserRole::Admin,
            'name' => 'Admin Jakarta',
        ]);
        $regionJkt = OperationalRegion::create([
            'name' => 'Wilayah Jakarta Pusat',
            'code' => 'REG-JKT',
            'admin_id' => $this->adminJkt->id,
            'cities' => ['Jakarta Pusat'],
            'is_active' => true,
        ]);

        $this->adminBdg = User::factory()->create([
            'role' => UserRole::Admin,
            'name' => 'Admin Bandung',
        ]);
        OperationalRegion::create([
            'name' => 'Wilayah Bandung',
            'code' => 'REG-BDG',
            'admin_id' => $this->adminBdg->id,
            'cities' => ['Bandung'],
            'is_active' => true,
        ]);

        $this->vendorUser = User::factory()->create(['role' => UserRole::Vendor]);
        $this->vendorProfile = VendorProfile::factory()->create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'SafeStore Gambir',
            'city' => 'Jakarta Pusat',
            'verification_status' => VendorVerificationStatus::Verified,
            'is_open' => true,
        ]);

        $category = Category::create([
            'name' => 'Penitipan Traveling',
            'slug' => 'penitipan-traveling',
            'is_active' => true,
        ]);

        $this->listingJkt = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'operational_region_id' => $regionJkt->id,
            'category_id' => $category->id,
            'title' => 'Loker Wisata Stasiun Gambir',
            'slug' => 'loker-wisata-gambir',
            'description' => 'Aman dan strategis',
            'address' => 'Jl. Stasiun Gambir No. 1',
            'city' => 'Jakarta Pusat',
            'price_per_hour' => 10000,
            'price_per_day' => 40000,
            'capacity_total' => 10,
            'capacity_available' => 10,
            'status' => 'published',
        ]);

        $this->customer = User::factory()->create([
            'role' => UserRole::Customer,
            'name' => 'Budi Customer',
            'phone' => '081234567890',
        ]);

        $this->booking = $this->bookingService->createBooking($this->customer, $this->listingJkt, [
            'duration_type' => 'day',
            'duration_units' => 2,
            'total_items' => 1,
            'check_in_plan_at' => now()->subDays(2),
            'status' => BookingStatus::ReturnedByVendor,
        ]);

        // Pre-create settlement in Pending state
        $this->settlement = VendorSettlement::create([
            'booking_id' => $this->booking->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'amount' => 64000, // 80% of 80,000
            'status' => SettlementStatus::Pending,
        ]);
    }

    public function test_customer_can_file_ordinary_dispute_and_triggers_escrow_hold(): void
    {
        $response = $this->actingAs($this->customer)->post(route('booking.dispute.store', $this->booking->booking_code), [
            'category' => DisputeCategory::OperationalDelay->value,
            'title' => 'Mitra terlambat 45 menit saat pengembalian',
            'description' => 'Saya harus menunggu di stasiun dan hampir tertinggal kereta karena mitra tidak standby.',
        ]);

        $response->assertRedirect(route('booking.show', $this->booking->booking_code));
        $response->assertSessionHas('warning');

        $this->booking->refresh();
        $this->assertSame(BookingStatus::Disputed, $this->booking->status);

        // Policy 11.1 Poin 5: Payout settlement must be placed on Escrow Hold
        $this->settlement->refresh();
        $this->assertSame(SettlementStatus::OnHold, $this->settlement->status);

        $this->assertDatabaseHas('disputes', [
            'booking_id' => $this->booking->id,
            'category' => DisputeCategory::OperationalDelay->value,
            'status' => DisputeStatus::Open->value,
            'is_escalated' => false,
        ]);
    }

    public function test_customer_filing_critical_category_triggers_automatic_superadmin_escalation(): void
    {
        // Lost goods is a mandatory escalation category (Policy 11.1)
        $response = $this->actingAs($this->customer)->post(route('booking.dispute.store', $this->booking->booking_code), [
            'category' => DisputeCategory::LostGoods->value,
            'title' => 'Koper tidak ada di lokasi saat proses retrieval',
            'description' => 'Mitra menyatakan barang tertukar atau hilang dari rak penyimpanan loker.',
        ]);

        $response->assertRedirect(route('booking.show', $this->booking->booking_code));

        $dispute = Dispute::where('booking_id', $this->booking->id)->first();
        $this->assertNotNull($dispute);
        $this->assertSame(DisputeCategory::LostGoods, $dispute->category);
        $this->assertSame(DisputeStatus::Escalated, $dispute->status);
        $this->assertTrue($dispute->is_escalated);
        $this->assertNotNull($dispute->escalated_at);

        // Escrow hold active
        $this->assertSame(SettlementStatus::OnHold, $this->settlement->fresh()->status);
    }

    public function test_admin_can_resolve_ordinary_dispute_and_releases_escrow(): void
    {
        $dispute = Dispute::create([
            'booking_id' => $this->booking->id,
            'customer_id' => $this->customer->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'category' => DisputeCategory::DamagedItem,
            'status' => DisputeStatus::Open,
            'is_escalated' => false,
            'title' => 'Resleting tas sobek kecil',
            'description' => 'Terjadi robekan minor pada resleting luar.',
        ]);
        $this->booking->update(['status' => BookingStatus::Disputed]);
        $this->settlement->update(['status' => SettlementStatus::OnHold]);

        // Admin Jakarta resolves with 10,000 penalty deducted from vendor payout
        $response = $this->actingAs($this->adminJkt)->post(route('admin.disputes.resolve', $dispute->id), [
            'resolution_notes' => 'Mitra dan pelanggan menyepakati potongan ongkos perbaikan Rp 10.000.',
            'vendor_penalty_amount' => 10000,
        ]);

        $response->assertRedirect(route('admin.disputes.show', $dispute->id));
        $response->assertSessionHas('success');

        $dispute->refresh();
        $this->assertSame(DisputeStatus::Resolved, $dispute->status);
        $this->assertEquals(10000, $dispute->vendor_penalty_amount);
        $this->assertSame($this->adminJkt->id, $dispute->resolved_by);

        // Settlement released back to Pending with deducted amount: 64,000 - 10,000 = 54,000
        $this->settlement->refresh();
        $this->assertSame(SettlementStatus::Pending, $this->settlement->status);
        $this->assertEquals(54000, $this->settlement->amount);

        // Booking completed
        $this->assertSame(BookingStatus::Completed, $this->booking->fresh()->status);
    }

    public function test_ordinary_admin_forbidden_from_resolving_escalated_dispute(): void
    {
        $dispute = Dispute::create([
            'booking_id' => $this->booking->id,
            'customer_id' => $this->customer->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'category' => DisputeCategory::LostGoods,
            'status' => DisputeStatus::Escalated,
            'is_escalated' => true,
            'title' => 'Barang hilang dari loker',
            'description' => 'Investigasi mendalam diperlukan.',
        ]);

        // Admin Jakarta attempts to resolve escalated dispute -> Forbidden (Policy 11.1)
        $response = $this->actingAs($this->adminJkt)->post(route('admin.disputes.resolve', $dispute->id), [
            'resolution_notes' => 'Mencoba menyelesaikan kasus eskalasi.',
        ]);

        $response->assertStatus(403);
    }

    public function test_superadmin_can_arbitrate_escalated_dispute_and_suspend_vendor(): void
    {
        $dispute = Dispute::create([
            'booking_id' => $this->booking->id,
            'customer_id' => $this->customer->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'category' => DisputeCategory::Fraud,
            'status' => DisputeStatus::Escalated,
            'is_escalated' => true,
            'title' => 'Indikasi penipuan barang titipan',
            'description' => 'Mitra diduga menggelapkan barang elektronik pelanggan.',
        ]);
        $this->settlement->update(['status' => SettlementStatus::OnHold]);

        // Superadmin arbitrates and freezes/suspends the vendor profile (Policy 7.3 & 11)
        $response = $this->actingAs($this->superadmin)->post(route('admin.disputes.arbitrate', $dispute->id), [
            'arbitration_decision' => 'resolved',
            'resolution_notes' => 'Ditemukan bukti kuat pelanggaran berat. Dana ditahan penuh dan akun dibekukan.',
            'vendor_penalty_amount' => 64000, // 100% penalty
            'suspend_vendor' => 1,
            'suspension_reason' => 'Pelanggaran berat penipuan barang dan sengketa kritis.',
        ]);

        $response->assertRedirect(route('admin.disputes.show', $dispute->id));
        $response->assertSessionHas('success');

        $dispute->refresh();
        $this->assertSame(DisputeStatus::Resolved, $dispute->status);

        // Vendor profile must be suspended
        $this->vendorProfile->refresh();
        $this->assertTrue($this->vendorProfile->is_suspended);
        $this->assertNotNull($this->vendorProfile->suspended_at);
        $this->assertEquals('Pelanggaran berat penipuan barang dan sengketa kritis.', $this->vendorProfile->suspension_reason);
        $this->assertSame($this->superadmin->id, $this->vendorProfile->suspended_by);

        // Vendor can no longer accept any bookings
        $this->assertFalse($this->vendorProfile->canAcceptBookings());
    }

    public function test_cross_region_admin_cannot_access_or_resolve_dispute(): void
    {
        $dispute = Dispute::create([
            'booking_id' => $this->booking->id,
            'customer_id' => $this->customer->id,
            'vendor_profile_id' => $this->vendorProfile->id,
            'category' => DisputeCategory::OperationalDelay,
            'status' => DisputeStatus::Open,
            'title' => 'Keterlambatan serah terima',
            'description' => 'Kasus di Jakarta Pusat.',
        ]);

        // Admin Bandung cannot view or resolve Jakarta dispute
        $viewRes = $this->actingAs($this->adminBdg)->get(route('admin.disputes.show', $dispute->id));
        $viewRes->assertStatus(403);

        $resolveRes = $this->actingAs($this->adminBdg)->post(route('admin.disputes.resolve', $dispute->id), [
            'resolution_notes' => 'Resolusi lintas daerah ilegal.',
        ]);
        $resolveRes->assertStatus(403);
    }
}
