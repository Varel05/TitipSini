<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ListingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SettlementStatus;
use App\Enums\VendorVerificationStatus;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Listing;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $vendorUser;

    private VendorProfile $vendorProfile;

    private User $customerUser;

    private Listing $listing;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendorUser = User::factory()->vendor()->create([
            'name' => 'Mitra Gambir',
            'email' => 'vendor.gambir@example.com',
        ]);

        $this->vendorProfile = VendorProfile::factory()->create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'Loker Sentral Gambir',
            'verification_status' => VendorVerificationStatus::Verified,
            'is_open' => true,
            'bank_name' => 'BCA',
            'bank_account_number' => '8881234567',
            'bank_account_holder' => 'Mitra Sentral Gambir',
        ]);

        $this->customerUser = User::factory()->customer()->create([
            'name' => 'Pelanggan Uji',
            'email' => 'customer.test@example.com',
        ]);

        $category = Category::create([
            'name' => 'Koper Wisata',
            'slug' => 'koper-wisata',
            'is_active' => true,
        ]);

        $this->listing = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $category->id,
            'title' => 'Loker Dekat Pintu Stasiun',
            'slug' => 'loker-dekat-pintu-stasiun',
            'description' => 'Loker berpendingin dan bersegil aman',
            'address' => 'Jl. Medan Merdeka Timur No. 1',
            'city' => 'Jakarta Pusat',
            'price_per_hour' => 10000,
            'price_per_day' => 40000,
            'capacity_total' => 10,
            'capacity_available' => 6,
            'status' => ListingStatus::Published,
        ]);

        $bookingService = app(BookingService::class);
        $this->booking = $bookingService->createBooking($this->customerUser, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 2,
            'total_items' => 1,
            'check_in_plan_at' => now()->format('Y-m-d H:i'),
        ]);
    }

    public function test_unauthenticated_user_cannot_access_vendor_portal(): void
    {
        $response = $this->get(route('vendor.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_vendor_portal(): void
    {
        $response = $this->actingAs($this->customerUser)->get(route('vendor.dashboard'));
        $response->assertStatus(403);
    }

    public function test_vendor_can_view_dashboard_with_kpi_metrics(): void
    {
        $response = $this->actingAs($this->vendorUser)->get(route('vendor.dashboard'));

        $response->assertOk();
        $response->assertSeeText('Loker Sentral Gambir');
        $response->assertSeeText('Booking Aktif');
        $response->assertSeeText('Barang Tersimpan');
    }

    public function test_vendor_can_view_operational_bookings_queue(): void
    {
        $response = $this->actingAs($this->vendorUser)->get(route('vendor.bookings.index'));

        $response->assertOk();
        $response->assertSee($this->booking->booking_code);
        $response->assertSeeText('Pelanggan Uji');
    }

    public function test_vendor_can_checkin_booking_with_security_seal(): void
    {
        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.bookings.checkin', $this->booking->booking_code), [
                'security_seal_code' => 'SEAL-VK-7788',
                'check_in_notes' => 'Kondisi koper rapi dan utuh',
            ]);

        $response->assertRedirect(route('vendor.bookings.show', $this->booking->booking_code));

        $freshBooking = $this->booking->fresh();
        $this->assertSame(BookingStatus::CheckedIn, $freshBooking->status);
        $this->assertSame('SEAL-VK-7788', $freshBooking->security_seal_code);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'vendor_booking_check_in',
            'auditable_id' => $freshBooking->id,
        ]);
    }

    public function test_vendor_cannot_handover_unpaid_booking(): void
    {
        $bookingService = app(BookingService::class);
        $bookingService->checkIn($this->booking, 'SEAL-TEST-1122');
        $bookingService->requestRetrieval($this->booking);

        // Attempting handover without payment confirmation
        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.bookings.handover', $this->booking->booking_code));

        $response->assertSessionHas('error');
        // Status should still be RetrievalRequested, not Completed!
        $this->assertSame(BookingStatus::RetrievalRequested, $this->booking->fresh()->status);
    }

    public function test_vendor_can_handover_paid_booking_and_trigger_settlement(): void
    {
        $bookingService = app(BookingService::class);
        $bookingService->checkIn($this->booking, 'SEAL-TEST-1122');
        $bookingService->requestRetrieval($this->booking);

        // Record paid payment attempt
        PaymentAttempt::create([
            'booking_id' => $this->booking->id,
            'payment_reference' => 'XEN-TEST-PAID-001',
            'channel' => 'qris',
            'amount' => $this->booking->total_amount,
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.bookings.handover', $this->booking->booking_code));

        $response->assertRedirect(route('vendor.bookings.show', $this->booking->booking_code));
        $response->assertSessionHas('success');

        $freshBooking = $this->booking->fresh();
        $this->assertSame(BookingStatus::Completed, $freshBooking->status);

        // Capacity should be restored back
        $this->assertSame(6, $this->listing->fresh()->capacity_available);

        // Settlement should be recorded & settled
        $this->assertDatabaseHas('vendor_settlements', [
            'booking_id' => $freshBooking->id,
            'status' => SettlementStatus::Settled->value,
        ]);
    }

    public function test_vendor_can_update_listing_pricing_and_capacity(): void
    {
        $response = $this->actingAs($this->vendorUser)
            ->put(route('vendor.listings.update', $this->listing->id), [
                'title' => 'Loker Sentral Gambir VIP',
                'price_per_hour' => 15000,
                'price_per_day' => 50000,
                'capacity_total' => 20,
                'operational_open' => '07:00',
                'operational_close' => '22:00',
                'description' => 'Fasilitas loker premium',
                'rules' => 'Dilarang menyimpan makanan basah',
            ]);

        $response->assertRedirect(route('vendor.listings.index'));

        $this->assertDatabaseHas('listings', [
            'id' => $this->listing->id,
            'title' => 'Loker Sentral Gambir VIP',
            'price_per_hour' => 15000,
            'price_per_day' => 50000,
            'capacity_total' => 20,
        ]);
    }

    public function test_vendor_can_toggle_store_open_status(): void
    {
        $this->assertTrue($this->vendorProfile->fresh()->is_open);

        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.listings.toggle_open'));

        $response->assertRedirect();
        $this->assertFalse($this->vendorProfile->fresh()->is_open);
    }

    public function test_vendor_can_update_bank_account(): void
    {
        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.settlements.update_bank'), [
                'bank_name' => 'MANDIRI',
                'bank_account_number' => '1370009988776',
                'bank_account_holder' => 'Mitra Mandiri Sentral',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vendor_profiles', [
            'id' => $this->vendorProfile->id,
            'bank_name' => 'MANDIRI',
            'bank_account_number' => '1370009988776',
            'bank_account_holder' => 'Mitra Mandiri Sentral',
        ]);
    }
}
