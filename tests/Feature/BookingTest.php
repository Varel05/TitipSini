<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ListingStatus;
use App\Enums\PaymentStatus;
use App\Enums\SettlementStatus;
use App\Enums\VendorVerificationStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Listing $listing;

    private User $customer;

    private BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = new BookingService;

        $vendorUser = User::factory()->vendor()->create();
        $vendorProfile = VendorProfile::factory()->create([
            'user_id' => $vendorUser->id,
            'verification_status' => VendorVerificationStatus::Verified,
            'is_open' => true,
        ]);

        $category = Category::create([
            'name' => 'Koper Wisata',
            'slug' => 'koper-wisata',
            'is_active' => true,
        ]);

        $this->listing = Listing::create([
            'vendor_profile_id' => $vendorProfile->id,
            'category_id' => $category->id,
            'title' => 'Loker Stasiun Gambir',
            'slug' => 'loker-stasiun-gambir',
            'description' => 'Dekat pintu stasiun',
            'address' => 'Gambir, Jakarta Pusat',
            'city' => 'Jakarta Pusat',
            'price_per_hour' => 10000,
            'price_per_day' => 35000,
            'capacity_total' => 10,
            'capacity_available' => 5,
            'status' => ListingStatus::Published,
        ]);

        $this->customer = User::factory()->customer()->create([
            'name' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'phone' => '08123456789',
        ]);
    }

    public function test_pricing_quote_calculates_correct_platform_commission(): void
    {
        // 2 days * 35,000 * 2 items = 140,000 storage fee
        // Platform fee 20% = 28,000
        // Vendor earnings 80% = 112,000
        $quote = $this->bookingService->calculateQuote($this->listing, 'day', 2, 2);

        $this->assertSame(140000.0, $quote['storage_fee']);
        $this->assertSame(28000.0, $quote['platform_fee']);
        $this->assertSame(112000.0, $quote['vendor_earnings']);
        $this->assertSame(140000.0, $quote['total_amount']);
    }

    public function test_customer_can_create_booking_zero_upfront(): void
    {
        $response = $this->actingAs($this->customer)->post(route('booking.store', $this->listing->slug), [
            'customer_name' => 'Siti Rahma',
            'customer_phone' => '08123456789',
            'customer_email' => 'siti@example.com',
            'duration_type' => 'day',
            'duration_units' => 2,
            'total_items' => 1,
            'check_in_plan_at' => now()->addDay()->format('Y-m-d H:i'),
            'item_name' => 'Koper 24 Inch Hitam',
            'customer_notes' => 'Tiba siang hari',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'customer_id' => $this->customer->id,
            'listing_id' => $this->listing->id,
            'total_items' => 1,
            'status' => BookingStatus::Created->value,
            'storage_fee' => 70000.00,
            'platform_fee' => 14000.00,
            'vendor_earnings' => 56000.00,
        ]);

        // Capacity should be decremented from 5 to 4
        $this->assertSame(4, $this->listing->fresh()->capacity_available);
    }

    public function test_booking_fails_when_capacity_is_insufficient(): void
    {
        $this->expectException(ValidationException::class);

        // Attempting to book 6 items when only 5 are available
        $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 6,
            'check_in_plan_at' => now()->format('Y-m-d H:i'),
        ]);
    }

    public function test_booking_lifecycle_transitions(): void
    {
        // 1. Create booking (Initial Created state per Policy 8.1)
        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->format('Y-m-d H:i'),
        ]);

        $this->assertSame(BookingStatus::Created, $booking->status);
        $this->assertSame(4, $this->listing->fresh()->capacity_available);

        // Vendor confirms booking
        $booking = $this->bookingService->confirmBooking($booking);
        $this->assertSame(BookingStatus::Confirmed, $booking->status);

        // 2. Physical Check-in & Seal attachment
        $booking = $this->bookingService->checkIn($booking, 'SEAL-TEST-9988');
        $this->assertSame(BookingStatus::CheckedIn, $booking->status);
        $this->assertSame('SEAL-TEST-9988', $booking->security_seal_code);
        $this->assertNotNull($booking->actual_check_in_at);

        // 3. Request Retrieval
        $booking = $this->bookingService->requestRetrieval($booking);
        $this->assertSame(BookingStatus::RetrievalRequested, $booking->status);

        // 4. Simulate Xendit Payment Confirmation & Complete Retrieval
        $payment = PaymentAttempt::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'XEN-TEST-PAY-01',
            'channel' => 'qris',
            'amount' => $booking->total_amount,
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $booking = $this->bookingService->completeRetrieval($booking, $payment);
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertNotNull($booking->actual_check_out_at);

        // Capacity should be restored back to 5
        $this->assertSame(5, $this->listing->fresh()->capacity_available);

        // Vendor settlement payout should be created
        $this->assertDatabaseHas('vendor_settlements', [
            'booking_id' => $booking->id,
            'vendor_profile_id' => $booking->vendor_profile_id,
            'amount' => $booking->vendor_earnings,
            'status' => SettlementStatus::Pending->value,
        ]);
    }

    public function test_cancelling_booking_restores_capacity(): void
    {
        $booking = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 2,
            'check_in_plan_at' => now()->format('Y-m-d H:i'),
        ]);

        // Capacity reduced by 2 (5 -> 3)
        $this->assertSame(3, $this->listing->fresh()->capacity_available);

        $this->bookingService->cancelBooking($booking, 'Perubahan rencana perjalanan');

        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
        // Capacity restored back to 5
        $this->assertSame(5, $this->listing->fresh()->capacity_available);
    }

    public function test_guest_cannot_view_booking_history_and_is_redirected_to_login(): void
    {
        // Unauthenticated guest attempts to access /pesanan
        $response = $this->get(route('booking.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_can_view_only_their_own_bookings(): void
    {
        $otherCustomer = User::factory()->customer()->create([
            'name' => 'Budi Lain',
            'email' => 'budi.lain@example.com',
        ]);

        // Booking 1 for Customer A ($this->customer)
        $bookingCustomer = $this->bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 1,
            'total_items' => 1,
            'check_in_plan_at' => now()->addDay(),
        ]);

        // Booking 2 for Customer B ($otherCustomer)
        $bookingOther = $this->bookingService->createBooking($otherCustomer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 2,
            'total_items' => 1,
            'check_in_plan_at' => now()->addDays(2),
        ]);

        // Acting as Customer A
        $response = $this->actingAs($this->customer)->get(route('booking.index'));

        $response->assertStatus(200);
        $response->assertSeeText($bookingCustomer->booking_code);
        $response->assertDontSeeText($bookingOther->booking_code);
    }
}
