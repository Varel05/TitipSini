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
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorSettlement;
use App\Services\BookingService;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XenditPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Listing $listing;

    private User $customer;

    private Booking $booking;

    private PaymentGatewayInterface $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.xendit.webhook_token' => 'test_webhook_token_secret',
            'services.xendit.secret_key' => 'xnd_development_dummy_key',
            'services.xendit.is_production' => false,
        ]);

        $this->paymentGateway = app(PaymentGatewayInterface::class);

        $vendorUser = User::factory()->vendor()->create();
        $vendorProfile = VendorProfile::factory()->create([
            'user_id' => $vendorUser->id,
            'verification_status' => VendorVerificationStatus::Verified,
            'is_open' => true,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Mitra Gambir Bersatu',
        ]);

        $category = Category::create([
            'name' => 'Loker Wisata',
            'slug' => 'loker-wisata',
            'is_active' => true,
        ]);

        $this->listing = Listing::create([
            'vendor_profile_id' => $vendorProfile->id,
            'category_id' => $category->id,
            'title' => 'Loker Stasiun Gambir',
            'slug' => 'loker-stasiun-gambir',
            'description' => 'Aman dan strategis',
            'address' => 'Jl. Medan Merdeka Timur',
            'city' => 'Jakarta Pusat',
            'price_per_hour' => 10000,
            'price_per_day' => 40000,
            'capacity_total' => 10,
            'capacity_available' => 5,
            'status' => ListingStatus::Published,
        ]);

        $this->customer = User::factory()->customer()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081298765432',
        ]);

        // Create initial booking
        $bookingService = app(BookingService::class);
        $this->booking = $bookingService->createBooking($this->customer, $this->listing, [
            'duration_type' => 'day',
            'duration_units' => 2,
            'total_items' => 1,
            'check_in_plan_at' => now()->format('Y-m-d H:i'),
        ]);

        // Check in
        $bookingService->checkIn($this->booking, 'SEAL-XYZ-1234');
    }

    public function test_customer_request_retrieval_creates_closeout_invoice(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('booking.retrieval', $this->booking->booking_code));

        $response->assertRedirect(route('booking.show', $this->booking->booking_code));
        $this->assertSame(BookingStatus::RetrievalRequested, $this->booking->fresh()->status);

        $this->assertDatabaseHas('payment_attempts', [
            'booking_id' => $this->booking->id,
            'status' => PaymentStatus::Pending->value,
            'amount' => 80000.00, // 2 days * 40,000
        ]);
    }

    public function test_webhook_rejects_unauthorized_token(): void
    {
        $response = $this->postJson(route('webhook.xendit'), [
            'status' => 'PAID',
            'external_id' => 'TITIPSINI-INV-999',
        ], [
            'x-callback-token' => 'invalid_wrong_token',
        ]);

        $response->assertStatus(403);
    }

    public function test_webhook_successfully_processes_paid_invoice_and_disburses(): void
    {
        // 1. Request retrieval to generate invoice
        $paymentAttempt = $this->paymentGateway->createCloseoutInvoice($this->booking);

        $this->assertSame(PaymentStatus::Pending, $paymentAttempt->status);
        $this->assertSame(4, $this->listing->fresh()->capacity_available);

        // 2. Simulate webhook call from Xendit
        $response = $this->postJson(route('webhook.xendit'), [
            'status' => 'PAID',
            'external_id' => $paymentAttempt->payment_reference,
            'amount' => 80000,
            'payment_method' => 'QRIS',
        ], [
            'x-callback-token' => 'test_webhook_token_secret',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'booking_code' => $this->booking->booking_code,
        ]);

        // 3. Verify booking is completed and capacity is restored
        $freshBooking = $this->booking->fresh();
        $this->assertSame(BookingStatus::Completed, $freshBooking->status);
        $this->assertSame(5, $this->listing->fresh()->capacity_available);

        // 4. Verify payment attempt is paid
        $this->assertSame(PaymentStatus::Paid, $paymentAttempt->fresh()->status);
        $this->assertNotNull($paymentAttempt->fresh()->paid_at);

        // 5. Verify vendor settlement is created and marked settled
        $this->assertDatabaseHas('vendor_settlements', [
            'booking_id' => $this->booking->id,
            'amount' => 64000.00, // 80% of 80,000
            'status' => SettlementStatus::Settled->value,
        ]);

        // 6. Verify audit logs recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'xendit_webhook_payment_paid',
            'auditable_id' => $this->booking->id,
        ]);
    }

    public function test_webhook_processing_is_idempotent(): void
    {
        $paymentAttempt = $this->paymentGateway->createCloseoutInvoice($this->booking);

        $payload = [
            'status' => 'PAID',
            'external_id' => $paymentAttempt->payment_reference,
            'amount' => 80000,
            'payment_method' => 'QRIS',
        ];
        $headers = ['x-callback-token' => 'test_webhook_token_secret'];

        // Send first webhook
        $this->postJson(route('webhook.xendit'), $payload, $headers)->assertOk();

        $initialSettlementCount = VendorSettlement::where('booking_id', $this->booking->id)->count();
        $this->assertSame(1, $initialSettlementCount);

        // Send duplicate webhook
        $secondResponse = $this->postJson(route('webhook.xendit'), $payload, $headers);
        $secondResponse->assertOk();

        // Must still have exactly 1 settlement and capacity intact
        $this->assertSame(1, VendorSettlement::where('booking_id', $this->booking->id)->count());
        $this->assertSame(5, $this->listing->fresh()->capacity_available);
    }

    public function test_checkout_simulation_screen_renders_successfully(): void
    {
        $this->paymentGateway->createCloseoutInvoice($this->booking);

        $response = $this->actingAs($this->customer)
            ->get(route('booking.checkout_simulation', $this->booking->booking_code));

        $response->assertOk();
        $response->assertSeeText('TitipSini Pay');
        $response->assertSee('xenPlatform Gateway');
        $response->assertSee('80.000');
    }
}
