<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use App\Models\VendorSettlement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorSettlementController extends Controller
{
    /**
     * Display vendor payout and settlement history.
     */
    public function index(): View
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();
        abort_unless($vendorProfile, 403, 'Profil Mitra Vendor tidak ditemukan.');

        $settlements = VendorSettlement::where('vendor_profile_id', $vendorProfile->id)
            ->with(['booking.customer', 'booking.listing'])
            ->latest()
            ->paginate(15);

        $totalSettled = (float) VendorSettlement::where('vendor_profile_id', $vendorProfile->id)
            ->where('status', SettlementStatus::Settled)
            ->sum('amount');

        $totalPending = (float) VendorSettlement::where('vendor_profile_id', $vendorProfile->id)
            ->where('status', SettlementStatus::Pending)
            ->sum('amount');

        return view('pages.vendor.settlements.index', [
            'vendor' => $vendorProfile,
            'settlements' => $settlements,
            'totalSettled' => $totalSettled,
            'totalPending' => $totalPending,
        ]);
    }

    /**
     * Update bank account information for automated payouts.
     */
    public function updateBank(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();
        abort_unless($vendorProfile, 403, 'Profil Mitra Vendor tidak ditemukan.');

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:50'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:100'],
        ]);

        $vendorProfile->update($validated);

        return back()->with('success', 'Rekening bank pencairan dana berhasil diperbarui.');
    }
}
