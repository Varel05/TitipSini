<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\PaymentAttempt;
use App\Models\VendorSettlement;
use App\Services\Payment\VendorPayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFinanceController extends Controller
{
    public function __construct(
        protected VendorPayoutService $vendorPayoutService,
    ) {}

    /**
     * Display centralized financial reconciliation dashboard (replaces finance role).
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'settlements');

        // Financial KPIs
        $totalGrossCollected = (float) PaymentAttempt::where('status', PaymentStatus::Paid)->sum('amount');
        $totalPlatformCommission = (float) Booking::where('status', 'completed')->sum('platform_fee');
        $totalVendorDisbursed = (float) VendorSettlement::where('status', SettlementStatus::Settled)->sum('amount');
        $totalPendingSettlement = (float) VendorSettlement::where('status', SettlementStatus::Pending)->sum('amount');

        // Payments Table
        $payments = PaymentAttempt::with(['booking.customer', 'booking.listing.vendorProfile'])
            ->latest()
            ->paginate(15, ['*'], 'payments_page');

        // Settlements Table
        $settlements = VendorSettlement::with(['vendorProfile.user', 'booking.customer'])
            ->latest()
            ->paginate(15, ['*'], 'settlements_page');

        return view('pages.admin.finance.index', [
            'tab' => $tab,
            'totalGrossCollected' => $totalGrossCollected,
            'totalPlatformCommission' => $totalPlatformCommission,
            'totalVendorDisbursed' => $totalVendorDisbursed,
            'totalPendingSettlement' => $totalPendingSettlement,
            'payments' => $payments,
            'settlements' => $settlements,
        ]);
    }

    /**
     * Manually trigger/retry vendor settlement disbursement.
     */
    public function retryPayout(Request $request, VendorSettlement $settlement): RedirectResponse
    {
        $this->vendorPayoutService->processPayout($settlement);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_retry_vendor_payout',
            'auditable_type' => VendorSettlement::class,
            'auditable_id' => $settlement->id,
            'new_values' => [
                'triggered_by' => auth()->user()->name,
                'status' => $settlement->fresh()->status->value,
            ],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Pencairan dana settlement #{$settlement->id} senilai Rp ".number_format($settlement->amount, 0, ',', '.').' berhasil diproses ulang.');
    }
}
