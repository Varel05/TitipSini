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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingLifecyclePrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $vendorUser;

    protected VendorProfile $vendorProfile;

    protected Listing $listing;

    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = app(BookingService::class);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $region = OperationalRegion::create([
            'name' => 'Wilayah Jakarta Pusat',
            'code' => 'REG-JP',
            'admin_id' => $admin->id,
            'cities' => ['Jakarta Pusat'],
            'is_active' => true,
        ]);

        $this->vendorUser = User::factory()->create([
            'role' => UserRole::Vendor,
            'phone' => '081299988877',
        ]);

        $this->vendorProfile = VendorProfile::factory()->create([
            'user_id' => $this->vendorUser->id,
            'business_name' => 'SafeStore Gambir',
            'city' => 'Jakarta Pusat',
            'phone' => '081299988877',
            'verification_status' => VendorVerificationStatus::Verified,
            'is_open' => true,
        ]);

        $category = Category::create([
            'name' => 'Penitipan Traveling',
            'slug' => 'penitipan-traveling',
            'is_active' => true,
        ]);

        $this->listing = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'operational_region_id' => $region->id,
            'category_id' => $category->id,
            'title' => 'Loker Gambir Aman',
            'slug' => 'loker-gambir-aman',
            'description' => 'Dekat pintu stasiun',
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
            'name' => 'Budi Customer',
            'phone' => '085711223344',
            'email' => 'budi@example.com',
        ]);
    }

    public function test_contact_phone_is_masked_when_booking_is_pending_confirmation(): void
    {
        // 1. Create booking in initial 'Created' state (Policy 8.1 Poin 3)
        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 2,
            'total_items' => 1,
            'check_in_plan_at' => now()->addDay(),
            'status' => BookingStatus::Created,
        ]);

        $this->assertEquals(BookingStatus::Created, $booking->status);
        $this->assertFalse($booking->canViewContacts());

        // 2. Vendor view: customer phone must be masked
        $response = $this->actingAs($this->vendorUser)->get(route('vendor.bookings.show', $booking->booking_code));
        $response->assertStatus(200);
        $response->assertSeeText('Nomor kontak disembunyikan');
        $response->assertDontSeeText('085711223344');

        // 3. Customer view: vendor phone must be masked
        $custResponse = $this->actingAs($this->customer)->get(route('booking.show', $booking->booking_code));
        $custResponse->assertStatus(200);
        $custResponse->assertSeeText('Nomor kontak tersedia setelah pesanan dikonfirmasi mitra');
        $custResponse->assertDontSeeText('081299988877');
    }

    public function test_vendor_can_confirm_booking_and_contacts_become_visible(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->addDay(),
            'status' => BookingStatus::Created,
        ]);

        // Vendor confirms booking (Policy 8.1 Poin 4 & 5)
        $response = $this->actingAs($this->vendorUser)->post(route('vendor.bookings.confirm', $booking->booking_code));
        $response->assertRedirect(route('vendor.bookings.show', $booking->booking_code));

        $booking->refresh();
        $this->assertEquals(BookingStatus::Confirmed, $booking->status);
        $this->assertTrue($booking->canViewContacts());

        // Both parties can now see contacts (Policy 13.1)
        $vendorPage = $this->actingAs($this->vendorUser)->get(route('vendor.bookings.show', $booking->booking_code));
        $vendorPage->assertSeeText('085711223344');

        $customerPage = $this->actingAs($this->customer)->get(route('booking.show', $booking->booking_code));
        $customerPage->assertSeeText('081299988877');
    }

    public function test_vendor_can_reject_booking_with_reason(): void
    {
        $initialCapacity = $this->listing->fresh()->capacity_available;

        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 2,
            'check_in_plan_at' => now()->addDay(),
            'status' => BookingStatus::Created,
        ]);

        $this->assertEquals($initialCapacity - 2, $this->listing->fresh()->capacity_available);

        // Vendor rejects booking (Policy 8.1 & 8.3)
        $response = $this->actingAs($this->vendorUser)->post(route('vendor.bookings.reject', $booking->booking_code), [
            'rejection_reason' => 'Loker sedang dalam masa perbaikan mekanis.',
        ]);
        $response->assertRedirect();

        $booking->refresh();
        $this->assertEquals(BookingStatus::Cancelled, $booking->status);
        $this->assertEquals('Loker sedang dalam masa perbaikan mekanis.', $booking->rejection_reason);

        // Capacity restored
        $this->assertEquals($initialCapacity, $this->listing->fresh()->capacity_available);
    }

    public function test_vendor_can_mark_goods_returned_after_payment(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->subDay(),
            'status' => BookingStatus::RetrievalRequested,
        ]);

        PaymentAttempt::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAY-TEST-001',
            'channel' => 'QRIS',
            'amount' => 35000,
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        // Vendor marks goods returned (Policy 8.2)
        $response = $this->actingAs($this->vendorUser)->post(route('vendor.bookings.mark_returned', $booking->booking_code));
        $response->assertRedirect();

        $booking->refresh();
        $this->assertEquals(BookingStatus::ReturnedByVendor, $booking->status);
        $this->assertNotNull($booking->returned_at);
        $this->assertNotNull($booking->auto_complete_at);
        $this->assertTrue($booking->auto_complete_at->isFuture());
    }

    public function test_customer_can_confirm_goods_return_and_complete_booking(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->subDay(),
            'status' => BookingStatus::ReturnedByVendor,
        ]);

        $booking->update([
            'returned_at' => now()->subHours(2),
            'auto_complete_at' => now()->addHours(46),
        ]);

        // Customer confirms receipt (Policy 8.2)
        $response = $this->actingAs($this->customer)->post(route('booking.confirm_return', $booking->booking_code));
        $response->assertRedirect(route('booking.show', $booking->booking_code));

        $booking->refresh();
        $this->assertEquals(BookingStatus::Completed, $booking->status);
        $this->assertNotNull($booking->customer_confirmed_at);
        $this->assertNotNull($booking->vendorSettlement);
    }

    public function test_contact_phone_is_masked_again_after_booking_is_completed(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->subDay(),
            'status' => BookingStatus::Completed,
        ]);

        // Policy 13.1: Contacts are masked again once booking is completed or cancelled
        $this->assertFalse($booking->canViewContacts());
    }

    public function test_command_auto_completes_booking_after_48_hours(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->subDays(3),
            'status' => BookingStatus::ReturnedByVendor,
        ]);

        // Simulated return 49 hours ago (expired 48-hour confirmation window)
        $booking->update([
            'returned_at' => now()->subHours(49),
            'auto_complete_at' => now()->subHour(),
        ]);

        $this->artisan('titipsini:check-return-confirmations')->assertSuccessful();

        $booking->refresh();
        $this->assertEquals(BookingStatus::Completed, $booking->status);
        $this->assertNotNull($booking->vendorSettlement);
    }
}
