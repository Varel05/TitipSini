<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Listing;
use App\Models\OperationalRegion;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\BookingService;
use App\Services\Payment\XenditPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrisAttemptLimitTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminJkt;

    protected User $adminBdg;

    protected User $superadmin;

    protected User $customer;

    protected OperationalRegion $regionJkt;

    protected OperationalRegion $regionBdg;

    protected Listing $listingJkt;

    protected Booking $booking;

    protected BookingService $bookingService;

    protected XenditPaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = app(BookingService::class);
        $this->paymentGateway = app(XenditPaymentGateway::class);

        // Superadmin
        $this->superadmin = User::factory()->create([
            'role' => UserRole::Superadmin,
            'name' => 'Super Admin TitipSini',
        ]);

        // Admin Jakarta
        $this->adminJkt = User::factory()->create([
            'role' => UserRole::Admin,
            'name' => 'Admin Jakarta',
        ]);
        $this->regionJkt = OperationalRegion::create([
            'name' => 'Wilayah Jakarta Pusat',
            'code' => 'REG-JKT',
            'admin_id' => $this->adminJkt->id,
            'cities' => ['Jakarta Pusat'],
            'is_active' => true,
        ]);

        // Admin Bandung
        $this->adminBdg = User::factory()->create([
            'role' => UserRole::Admin,
            'name' => 'Admin Bandung',
        ]);
        $this->regionBdg = OperationalRegion::create([
            'name' => 'Wilayah Bandung Raya',
            'code' => 'REG-BDG',
            'admin_id' => $this->adminBdg->id,
            'cities' => ['Kota Bandung'],
            'is_active' => true,
        ]);

        // Vendor
        $vendorUser = User::factory()->create(['role' => UserRole::Vendor]);
        $vendorProfile = VendorProfile::factory()->create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Loker Stasiun Gambir',
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
            'vendor_profile_id' => $vendorProfile->id,
            'operational_region_id' => $this->regionJkt->id,
            'category_id' => $category->id,
            'title' => 'Loker Wisata Stasiun Gambir',
            'slug' => 'loker-wisata-gambir',
            'description' => 'Aman dan strategis',
            'address' => 'Jl. Stasiun Gambir No. 1',
            'city' => 'Jakarta Pusat',
            'price_per_hour' => 10000,
            'price_per_day' => 35000,
            'capacity_total' => 10,
            'capacity_available' => 10,
            'status' => 'published',
        ]);

        $this->customer = User::factory()->create([
            'role' => UserRole::Customer,
            'name' => 'Andi Pelanggan',
            'phone' => '081233445566',
        ]);

        $this->booking = $this->bookingService->createBooking($this->customer, $this->listingJkt, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->subDay(),
            'status' => BookingStatus::RetrievalRequested,
        ]);
    }

    public function test_system_creates_qris_with_15_minute_expiry(): void
    {
        $attempt = $this->paymentGateway->createCloseoutInvoice($this->booking);

        $this->assertNotNull($attempt->expires_at);
        $this->assertEquals(1, $attempt->cycle_number);
        $this->assertSame(PaymentStatus::Pending, $attempt->status);
        $this->assertTrue($attempt->expires_at->isFuture());
        $this->assertEquals(15, (int) round(now()->diffInMinutes($attempt->expires_at)));
    }

    public function test_system_allows_up_to_3_attempts_in_same_cycle(): void
    {
        // 1st attempt
        $att1 = $this->paymentGateway->createCloseoutInvoice($this->booking);
        $this->assertEquals(1, $this->booking->getCurrentCycleAttemptsCount());

        // Mark 1st attempt expired
        $att1->update([
            'status' => PaymentStatus::Expired,
            'expires_at' => now()->subMinute(),
        ]);

        // 2nd attempt
        $att2 = $this->paymentGateway->createCloseoutInvoice($this->booking);
        $this->assertEquals(2, $this->booking->getCurrentCycleAttemptsCount());

        // Mark 2nd attempt expired
        $att2->update([
            'status' => PaymentStatus::Expired,
            'expires_at' => now()->subMinute(),
        ]);

        // 3rd attempt
        $att3 = $this->paymentGateway->createCloseoutInvoice($this->booking);
        $this->assertEquals(3, $this->booking->getCurrentCycleAttemptsCount());

        $this->assertFalse($this->booking->fresh()->isPaymentAttemptLimitExceeded());

        // Mark 3rd attempt expired
        $att3->update([
            'status' => PaymentStatus::Expired,
            'expires_at' => now()->subMinute(),
        ]);

        // Now limit is exceeded (3 expired attempts, 0 paid)
        $this->assertTrue($this->booking->fresh()->isPaymentAttemptLimitExceeded());
    }

    public function test_system_blocks_further_invoices_when_3_attempts_exceeded(): void
    {
        // Create 3 expired attempts
        for ($i = 1; $i <= 3; $i++) {
            PaymentAttempt::create([
                'booking_id' => $this->booking->id,
                'cycle_number' => 1,
                'payment_reference' => "REF-EXP-00{$i}",
                'channel' => 'qris',
                'amount' => 35000,
                'status' => PaymentStatus::Expired,
                'expires_at' => now()->subMinutes(10),
            ]);
        }

        $this->assertTrue($this->booking->fresh()->isPaymentAttemptLimitExceeded());

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Batas maksimal 3 percobaan pembayaran QRIS telah tercapai');

        $this->paymentGateway->createCloseoutInvoice($this->booking->fresh());
    }

    public function test_admin_cannot_reopen_payment_before_30_minute_cooldown(): void
    {
        // 3 expired attempts made only 10 minutes ago
        for ($i = 1; $i <= 3; $i++) {
            PaymentAttempt::create([
                'booking_id' => $this->booking->id,
                'cycle_number' => 1,
                'payment_reference' => "REF-EXP-00{$i}",
                'channel' => 'qris',
                'amount' => 35000,
                'status' => PaymentStatus::Expired,
                'expires_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ]);
        }

        $this->assertFalse($this->booking->fresh()->canAdminReopenPayment());

        // Attempting to reopen via Admin HTTP route
        $response = $this->actingAs($this->adminJkt)->post(route('admin.bookings.reopen_payment', $this->booking->booking_code), [
            'reason' => 'Customer meminta generate ulang QRIS.',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, $this->booking->fresh()->current_payment_cycle);
    }

    public function test_admin_can_reopen_payment_after_30_minute_cooldown_with_reason(): void
    {
        // 3 expired attempts made 35 minutes ago
        for ($i = 1; $i <= 3; $i++) {
            PaymentAttempt::create([
                'booking_id' => $this->booking->id,
                'cycle_number' => 1,
                'payment_reference' => "REF-EXP-00{$i}",
                'channel' => 'qris',
                'amount' => 35000,
                'status' => PaymentStatus::Expired,
                'expires_at' => now()->subMinutes(35),
                'updated_at' => now()->subMinutes(35),
            ]);
        }

        $this->assertTrue($this->booking->fresh()->canAdminReopenPayment());

        // Admin submits reopen
        $response = $this->actingAs($this->adminJkt)->post(route('admin.bookings.reopen_payment', $this->booking->booking_code), [
            'reason' => 'Customer mengalami kendala koneksi di stasiun, diberikan kesempatan transaksi siklus baru.',
        ]);

        $response->assertRedirect(route('admin.bookings.show', $this->booking->booking_code));
        $response->assertSessionHas('success');

        $fresh = $this->booking->fresh();
        $this->assertEquals(2, $fresh->current_payment_cycle);
        $this->assertNotNull($fresh->payment_cycle_reopened_at);
        $this->assertEquals('Customer mengalami kendala koneksi di stasiun, diberikan kesempatan transaksi siklus baru.', $fresh->payment_reopen_reason);
        $this->assertEquals($this->adminJkt->id, $fresh->payment_reopened_by);

        // Audit Log must be created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin_reopened_payment_cycle',
            'user_id' => $this->adminJkt->id,
            'auditable_id' => $this->booking->id,
        ]);

        // Customer now has 3 fresh attempts in cycle 2
        $this->assertEquals(0, $fresh->getCurrentCycleAttemptsCount());
        $newAttempt = $this->paymentGateway->createCloseoutInvoice($fresh);
        $this->assertEquals(2, $newAttempt->cycle_number);
        $this->assertEquals(1, $fresh->getCurrentCycleAttemptsCount());
    }

    public function test_cross_region_admin_cannot_access_or_reopen_booking(): void
    {
        // 3 expired attempts
        for ($i = 1; $i <= 3; $i++) {
            PaymentAttempt::create([
                'booking_id' => $this->booking->id,
                'cycle_number' => 1,
                'payment_reference' => "REF-EXP-00{$i}",
                'channel' => 'qris',
                'amount' => 35000,
                'status' => PaymentStatus::Expired,
                'expires_at' => now()->subMinutes(40),
                'updated_at' => now()->subMinutes(40),
            ]);
        }

        // Admin Bandung cannot view Jakarta booking
        $viewResponse = $this->actingAs($this->adminBdg)->get(route('admin.bookings.show', $this->booking->booking_code));
        $viewResponse->assertStatus(403);

        // Admin Bandung cannot reopen Jakarta booking
        $reopenResponse = $this->actingAs($this->adminBdg)->post(route('admin.bookings.reopen_payment', $this->booking->booking_code), [
            'reason' => 'Mencoba reopen lintas wilayah.',
        ]);
        $reopenResponse->assertStatus(403);

        // Superadmin CAN view and reopen
        $superResponse = $this->actingAs($this->superadmin)->get(route('admin.bookings.show', $this->booking->booking_code));
        $superResponse->assertStatus(200);

        $superReopen = $this->actingAs($this->superadmin)->post(route('admin.bookings.reopen_payment', $this->booking->booking_code), [
            'reason' => 'Superadmin override reopen payment.',
        ]);
        $superReopen->assertRedirect();
        $this->assertEquals(2, $this->booking->fresh()->current_payment_cycle);
    }
}
