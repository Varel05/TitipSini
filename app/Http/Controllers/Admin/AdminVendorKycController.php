<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VendorVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVendorKycController extends Controller
{
    /**
     * Display list of vendors for KYC review and status inspection.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $vendors = VendorProfile::query()
            ->when($statusFilter && $statusFilter !== 'all', function ($q) use ($statusFilter) {
                $q->where('verification_status', $statusFilter);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('business_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->with(['user', 'listings'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => VendorProfile::count(),
            'pending' => VendorProfile::where('verification_status', VendorVerificationStatus::Pending)->count(),
            'verified' => VendorProfile::where('verification_status', VendorVerificationStatus::Verified)->count(),
            'rejected' => VendorProfile::where('verification_status', VendorVerificationStatus::Rejected)->count(),
            'unverified' => VendorProfile::where('verification_status', VendorVerificationStatus::Unverified)->count(),
        ];

        return view('pages.admin.vendors.index', [
            'vendors' => $vendors,
            'counts' => $counts,
            'currentStatus' => $statusFilter ?? 'all',
            'search' => $search,
        ]);
    }

    /**
     * Show vendor KYC verification dossier.
     */
    public function show(VendorProfile $vendor): View
    {
        $vendor->load(['user', 'verifier', 'listings.category']);

        return view('pages.admin.vendors.show', [
            'vendor' => $vendor,
        ]);
    }

    /**
     * Approve vendor KYC dossier.
     */
    public function approve(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $vendor->update([
            'verification_status' => VendorVerificationStatus::Verified,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'rejection_reason' => null,
            'is_open' => true,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_vendor_kyc_approved',
            'auditable_type' => VendorProfile::class,
            'auditable_id' => $vendor->id,
            'new_values' => [
                'status' => VendorVerificationStatus::Verified->value,
                'verified_by' => auth()->user()->name,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.vendors.show', $vendor->id)
            ->with('success', "Mitra '{$vendor->business_name}' berhasil diverifikasi KYC! Toko kini aktif dan dapat menerima pesanan.");
    }

    /**
     * Reject vendor KYC dossier with reason.
     */
    public function reject(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $reason = $request->input('rejection_reason');

        $vendor->update([
            'verification_status' => VendorVerificationStatus::Rejected,
            'rejection_reason' => $reason,
            'verified_at' => null,
            'verified_by' => auth()->id(),
            'is_open' => false,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_vendor_kyc_rejected',
            'auditable_type' => VendorProfile::class,
            'auditable_id' => $vendor->id,
            'new_values' => [
                'status' => VendorVerificationStatus::Rejected->value,
                'reason' => $reason,
                'rejected_by' => auth()->user()->name,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.vendors.show', $vendor->id)
            ->with('info', "Verifikasi mitra '{$vendor->business_name}' telah ditolak dengan alasan yang tercatat.");
    }
}
