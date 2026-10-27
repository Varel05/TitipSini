<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\BookingStatus;
use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\VendorProfile;
use App\Models\VendorSettlement;
use Illuminate\View\View;

class VendorDashboardController extends Controller
{
    /**
     * Show vendor operational dashboard with live stats and quick actions.
     */
    public function index(): View
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();

        abort_unless($vendorProfile, 403, 'Profil Mitra Vendor tidak ditemukan.');

        // Live KPI Metrics
        $activeBookingsCount = Booking::where('vendor_profile_id', $vendorProfile->id)
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::CheckedIn, BookingStatus::RetrievalRequested])
            ->count();

        $storedItemsCount = Booking::where('vendor_profile_id', $vendorProfile->id)
            ->where('status', BookingStatus::CheckedIn)
            ->sum('total_items');

        $pendingRetrievalCount = Booking::where('vendor_profile_id', $vendorProfile->id)
            ->where('status', BookingStatus::RetrievalRequested)
            ->count();

        $totalCompletedCount = Booking::where('vendor_profile_id', $vendorProfile->id)
            ->where('status', BookingStatus::Completed)
            ->count();

        $totalEarnings = (float) VendorSettlement::where('vendor_profile_id', $vendorProfile->id)
            ->where('status', SettlementStatus::Settled)
            ->sum('amount');

        $pendingPayout = (float) VendorSettlement::where('vendor_profile_id', $vendorProfile->id)
            ->where('status', SettlementStatus::Pending)
            ->sum('amount');

        // Recent bookings needing vendor attention
        $priorityBookings = Booking::where('vendor_profile_id', $vendorProfile->id)
            ->whereIn('status', [BookingStatus::RetrievalRequested, BookingStatus::Confirmed, BookingStatus::CheckedIn])
            ->with(['customer', 'listing', 'paymentAttempts'])
            ->latest()
            ->take(6)
            ->get();

        // Vendor listings
        $listings = $vendorProfile->listings()->with('category')->get();

        return view('pages.vendor.dashboard', [
            'vendor' => $vendorProfile,
            'activeBookingsCount' => $activeBookingsCount,
            'storedItemsCount' => $storedItemsCount,
            'pendingRetrievalCount' => $pendingRetrievalCount,
            'totalCompletedCount' => $totalCompletedCount,
            'totalEarnings' => $totalEarnings,
            'pendingPayout' => $pendingPayout,
            'priorityBookings' => $priorityBookings,
            'listings' => $listings,
        ]);
    }
}
