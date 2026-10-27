<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\ListingStatus;
use App\Enums\SettlementStatus;
use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorSettlement;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display centralized executive management & monitoring dashboard.
     */
    public function index(): View
    {
        // Platform user counts
        $totalCustomers = User::where('role', UserRole::Customer)->count();
        $totalVendors = VendorProfile::count();

        // Operational Queues
        $pendingKycCount = VendorProfile::where('verification_status', VendorVerificationStatus::Pending)->count();
        $pendingListingCount = Listing::where('status', ListingStatus::Submitted)->count();
        $activeBookingsCount = Booking::whereIn('status', [
            BookingStatus::Confirmed,
            BookingStatus::CheckedIn,
            BookingStatus::RetrievalRequested,
        ])->count();
        $completedBookingsCount = Booking::where('status', BookingStatus::Completed)->count();

        // Financial Totals (ADR-002)
        $totalGmv = (float) Booking::where('status', BookingStatus::Completed)->sum('total_amount');
        $totalPlatformRevenue = (float) Booking::where('status', BookingStatus::Completed)->sum('platform_fee');
        $totalVendorDisbursed = (float) VendorSettlement::where('status', SettlementStatus::Settled)->sum('amount');
        $pendingVendorPayout = (float) VendorSettlement::where('status', SettlementStatus::Pending)->sum('amount');

        // Recent Audit Activity
        $recentAuditLogs = AuditLog::with('user')->latest()->take(6)->get();

        // Urgent KYC review queue
        $urgentKycVendors = VendorProfile::where('verification_status', VendorVerificationStatus::Pending)
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('pages.admin.dashboard', [
            'totalCustomers' => $totalCustomers,
            'totalVendors' => $totalVendors,
            'pendingKycCount' => $pendingKycCount,
            'pendingListingCount' => $pendingListingCount,
            'activeBookingsCount' => $activeBookingsCount,
            'completedBookingsCount' => $completedBookingsCount,
            'totalGmv' => $totalGmv,
            'totalPlatformRevenue' => $totalPlatformRevenue,
            'totalVendorDisbursed' => $totalVendorDisbursed,
            'pendingVendorPayout' => $pendingVendorPayout,
            'recentAuditLogs' => $recentAuditLogs,
            'urgentKycVendors' => $urgentKycVendors,
        ]);
    }
}
