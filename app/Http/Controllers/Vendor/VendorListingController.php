<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Listing;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorListingController extends Controller
{
    /**
     * Display listings belonging to the vendor.
     */
    public function index(): View
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();
        abort_unless($vendorProfile, 403, 'Profil Mitra Vendor tidak ditemukan.');

        $listings = $vendorProfile->listings()->with(['category', 'facilities'])->latest()->get();

        return view('pages.vendor.listings.index', [
            'vendor' => $vendorProfile,
            'listings' => $listings,
        ]);
    }

    /**
     * Show edit form for listing.
     */
    public function edit(Listing $listing): View
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();

        if ($user->role->value === 'vendor' && $listing->vendor_profile_id !== $vendorProfile?->id) {
            abort(403, 'Anda tidak memiliki hak akses mengubah listing ini.');
        }

        return view('pages.vendor.listings.edit', [
            'listing' => $listing,
            'vendor' => $vendorProfile,
        ]);
    }

    /**
     * Update listing pricing, capacity, and operational rules.
     */
    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();

        if ($user->role->value === 'vendor' && $listing->vendor_profile_id !== $vendorProfile?->id) {
            abort(403, 'Anda tidak memiliki hak akses mengubah listing ini.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'price_per_hour' => ['required', 'numeric', 'min:1000', 'max:500000'],
            'price_per_day' => ['required', 'numeric', 'min:5000', 'max:2000000'],
            'capacity_total' => ['required', 'integer', 'min:1', 'max:500'],
            'operational_open' => ['nullable', 'string', 'max:10'],
            'operational_close' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:1000'],
            'rules' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldCapacityTotal = $listing->capacity_total;
        $newCapacityTotal = (int) $validated['capacity_total'];

        // Adjust capacity available proportionally
        $difference = $newCapacityTotal - $oldCapacityTotal;
        $newCapacityAvailable = max(0, $listing->capacity_available + $difference);

        $listing->update([
            'title' => $validated['title'],
            'price_per_hour' => $validated['price_per_hour'],
            'price_per_day' => $validated['price_per_day'],
            'capacity_total' => $newCapacityTotal,
            'capacity_available' => $newCapacityAvailable,
            'operational_hours' => ($validated['operational_open'] ?? '06:00').' - '.($validated['operational_close'] ?? '23:00'),
            'description' => $validated['description'],
            'rules' => $validated['rules'],
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'vendor_listing_updated',
            'auditable_type' => Listing::class,
            'auditable_id' => $listing->id,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('vendor.listings.index')
            ->with('success', "Listing '{$listing->title}' berhasil diperbarui.");
    }

    /**
     * Quick toggle open/close status of vendor store.
     */
    public function toggleStoreStatus(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $vendorProfile = $user->vendorProfile ?? VendorProfile::first();
        abort_unless($vendorProfile, 403, 'Profil Mitra Vendor tidak ditemukan.');

        $vendorProfile->update([
            'is_open' => ! $vendorProfile->is_open,
        ]);

        $statusText = $vendorProfile->is_open ? 'BUKA (Menerima Penitipan)' : 'TUTUP SEMENTARA';

        return back()->with('info', "Status toko berhasil diubah menjadi: {$statusText}.");
    }
}
