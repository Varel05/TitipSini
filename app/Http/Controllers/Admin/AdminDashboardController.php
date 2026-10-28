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
        $user = auth()->user();
        $isSuperadmin = $user->isSuperadmin();
        $assignedRegion = $user->assignedRegion;

        // Platform customer count (Superadmin sees all, Admin sees customers with bookings in their region)
        $totalCustomers = $isSuperadmin
            ? User::where('role', UserRole::Customer)->count()
            : User::where('role', UserRole::Customer)
                ->whereHas('bookings', fn ($b) => $b->forAdminRegion($user))
                ->count();

        // Vendor profiles scoped by operational region
        $vendorQuery = VendorProfile::query()->forAdminRegion($user);
        $totalVendors = (clone $vendorQuery)->count();

        // Operational Queues scoped by region
        $pendingKycCount = (clone $vendorQuery)->where('verification_status', VendorVerificationStatus::Pending)->count();
        $pendingListingCount = Listing::query()->forAdminRegion($user)->where('status', ListingStatus::Submitted)->count();
        $activeBookingsCount = Booking::query()->forAdminRegion($user)->whereIn('status', [
            BookingStatus::Confirmed,
            BookingStatus::CheckedIn,
            BookingStatus::RetrievalRequested,
        ])->count();
        $completedBookingsCount = Booking::query()->forAdminRegion($user)->where('status', BookingStatus::Completed)->count();

        // Financial Totals scoped by region (ADR-002)
        $completedBookings = Booking::query()->forAdminRegion($user)->where('status', BookingStatus::Completed);
        $totalGmv = (float) (clone $completedBookings)->sum('total_amount');
        $totalPlatformRevenue = (float) (clone $completedBookings)->sum('platform_fee');

        $totalVendorDisbursed = (float) VendorSettlement::whereHas('booking', fn ($b) => $b->forAdminRegion($user))
            ->where('status', SettlementStatus::Settled)
            ->sum('amount');
        $pendingVendorPayout = (float) VendorSettlement::whereHas('booking', fn ($b) => $b->forAdminRegion($user))
            ->where('status', SettlementStatus::Pending)
            ->sum('amount');

        // Audit Trail (Policy 13.2: Exclusively accessible to Superadmin)
        $recentAuditLogs = $isSuperadmin
            ? AuditLog::with('user')->latest()->take(6)->get()
            : collect();

        // Urgent KYC review queue for current region
        $urgentKycVendors = (clone $vendorQuery)
            ->where('verification_status', VendorVerificationStatus::Pending)
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
            'assignedRegion' => $assignedRegion,
            'isSuperadmin' => $isSuperadmin,
        ]);
    }
}
