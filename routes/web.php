<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDisputeController;
use App\Http\Controllers\Admin\AdminFinanceController;
use App\Http\Controllers\Admin\AdminListingModerationController;
use App\Http\Controllers\Admin\AdminSkckAccessController;
use App\Http\Controllers\Admin\AdminVendorKycController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CustomerDisputeController;
use App\Http\Controllers\Vendor\VendorBookingController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorListingController;
use App\Http\Controllers\Vendor\VendorSettlementController;
use App\Http\Controllers\Webhook\XenditWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Catalog & Information Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/katalog', [CatalogController::class, 'explore'])->name('catalog.index');
Route::get('/katalog/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::redirect('/catalog', '/katalog');
Route::redirect('/bookings', '/pesanan');
Route::redirect('/vendor/dashboard', '/vendor');
Route::redirect('/admin/dashboard', '/admin');

/*
|--------------------------------------------------------------------------
| Booking & Transaction Lifecycle Routes (Zero Upfront Flow)
|--------------------------------------------------------------------------
*/
Route::get('/booking/{listing:slug}', [BookingController::class, 'create'])->name('booking.create');
Route::post('/booking/{listing:slug}', [BookingController::class, 'store'])->name('booking.store');

Route::get('/pesanan', [BookingController::class, 'index'])->name('booking.index');
Route::get('/pesanan/{booking:booking_code}', [BookingController::class, 'show'])->name('booking.show');
Route::get('/pesanan/{booking:booking_code}/bayar', [BookingController::class, 'pay'])->name('booking.pay');
Route::get('/pesanan/{booking:booking_code}/checkout-simulation', [BookingController::class, 'checkoutSimulation'])->name('booking.checkout_simulation');
Route::post('/pesanan/{booking:booking_code}/ambil', [BookingController::class, 'requestRetrieval'])->name('booking.retrieval');
Route::post('/pesanan/{booking:booking_code}/simulasi-check-in', [BookingController::class, 'simulateCheckIn'])->name('booking.simulate_checkin');
Route::post('/pesanan/{booking:booking_code}/simulasi-bayar', [BookingController::class, 'simulatePaymentSuccess'])->name('booking.simulate_pay');
Route::post('/pesanan/{booking:booking_code}/batal', [BookingController::class, 'cancel'])->name('booking.cancel');
Route::post('/pesanan/{booking:booking_code}/konfirmasi-terima', [BookingController::class, 'confirmReturn'])->name('booking.confirm_return');

// Customer Dispute & Complaint Reporting (Policy 11.1)
Route::get('/pesanan/{booking:booking_code}/komplain', [CustomerDisputeController::class, 'create'])->name('booking.dispute.create');
Route::post('/pesanan/{booking:booking_code}/komplain', [CustomerDisputeController::class, 'store'])->name('booking.dispute.store');

/*
|--------------------------------------------------------------------------
| Payment Gateway Webhook Handlers
|--------------------------------------------------------------------------
*/
Route::post('/api/webhooks/xendit', [XenditWebhookController::class, 'handle'])->name('webhook.xendit');
Route::post('/webhooks/xendit', [XenditWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Authentication Routes & Quick Switcher
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/demo-login/{role}', [AuthController::class, 'quickLogin'])->name('auth.quick_login');

/*
|--------------------------------------------------------------------------
| Vendor Operational Portal Routes
|--------------------------------------------------------------------------
*/
Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'role:vendor'])->group(function () {
    Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');

    // Operational Bookings Queue & Handover
    Route::get('/bookings', [VendorBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking:booking_code}', [VendorBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking:booking_code}/confirm', [VendorBookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking:booking_code}/reject', [VendorBookingController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{booking:booking_code}/checkin', [VendorBookingController::class, 'checkIn'])->name('bookings.checkin');
    Route::post('/bookings/{booking:booking_code}/handover', [VendorBookingController::class, 'handover'])->name('bookings.handover');
    Route::post('/bookings/{booking:booking_code}/mark-returned', [VendorBookingController::class, 'markReturned'])->name('bookings.mark_returned');

    // Listings Management
    Route::get('/listings', [VendorListingController::class, 'index'])->name('listings.index');
    Route::get('/listings/{listing}/edit', [VendorListingController::class, 'edit'])->name('listings.edit');
    Route::put('/listings/{listing}', [VendorListingController::class, 'update'])->name('listings.update');
    Route::post('/listings/toggle-open', [VendorListingController::class, 'toggleStoreStatus'])->name('listings.toggle_open');

    // Settlements & Payouts (80% net share)
    Route::get('/settlements', [VendorSettlementController::class, 'index'])->name('settlements.index');
    Route::post('/settlements/bank', [VendorSettlementController::class, 'updateBank'])->name('settlements.update_bank');
});

/*
|--------------------------------------------------------------------------
| Admin & Superadmin Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Vendor KYC Management (Policy 6.1 - 6.3)
    Route::get('/vendors', [AdminVendorKycController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/{vendor}', [AdminVendorKycController::class, 'show'])->name('vendors.show');
    Route::post('/vendors/{vendor}/approve', [AdminVendorKycController::class, 'approve'])->name('vendors.approve');
    Route::post('/vendors/{vendor}/reject', [AdminVendorKycController::class, 'reject'])->name('vendors.reject');
    Route::post('/vendors/{vendor}/request-revision', [AdminVendorKycController::class, 'requestRevision'])->name('vendors.request_revision');
    Route::post('/vendors/{vendor}/skck-request', [AdminVendorKycController::class, 'requestSkckAccess'])->name('vendors.request_skck');
    Route::get('/vendors/{vendor}/skck-document', [AdminVendorKycController::class, 'downloadSkckDocument'])->name('vendors.download_skck');
    Route::post('/vendors/{vendor}/privacy-anonymize', [AdminVendorKycController::class, 'privacyAnonymize'])->name('vendors.privacy_anonymize');

    // Listing Moderation
    Route::get('/listings', [AdminListingModerationController::class, 'index'])->name('listings.index');
    Route::get('/listings/{listing}', [AdminListingModerationController::class, 'show'])->name('listings.show');
    Route::post('/listings/{listing}/publish', [AdminListingModerationController::class, 'publish'])->name('listings.publish');
    Route::post('/listings/{listing}/reject', [AdminListingModerationController::class, 'reject'])->name('listings.reject');

    // Booking Management & Payment Cycle Reopen (Policy 10.2)
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking:booking_code}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking:booking_code}/reopen-payment', [AdminBookingController::class, 'reopenPayment'])->name('bookings.reopen_payment');

    // Dispute Management & Escalation (Policy 11)
    Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
    Route::post('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');
    Route::post('/disputes/{dispute}/reject', [AdminDisputeController::class, 'reject'])->name('disputes.reject');
    Route::post('/disputes/{dispute}/escalate', [AdminDisputeController::class, 'escalate'])->name('disputes.escalate');
    Route::post('/disputes/{dispute}/arbitrate', [AdminDisputeController::class, 'arbitrate'])->name('disputes.arbitrate');

    // Superadmin Exclusive Routes (Financial Reconciliation, Audit Trail, SKCK Approvals per Policies 6.3, 13.2, 13.3)
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance/settlements/{settlement}/retry', [AdminFinanceController::class, 'retryPayout'])->name('finance.retry_payout');
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit_logs.index');
        Route::get('/skck-requests', [AdminSkckAccessController::class, 'index'])->name('skck_requests.index');
        Route::post('/skck-requests/{accessRequest}/approve', [AdminSkckAccessController::class, 'approve'])->name('skck_requests.approve');
        Route::post('/skck-requests/{accessRequest}/reject', [AdminSkckAccessController::class, 'reject'])->name('skck_requests.reject');
    });
});

/*
|--------------------------------------------------------------------------
| Policy & Information Pages
|--------------------------------------------------------------------------
*/
Route::view('/tentang-kami', 'pages.about')->name('about');
Route::view('/bantuan', 'pages.faq')->name('faq');
Route::view('/syarat-ketentuan', 'pages.terms')->name('terms');
