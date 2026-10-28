<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VendorVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SkckAccessRequest;
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
        $user = auth()->user();
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $baseQuery = VendorProfile::query()->forAdminRegion($user);

        $vendors = (clone $baseQuery)
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
            'all' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('verification_status', VendorVerificationStatus::Pending)->count(),
            'verified' => (clone $baseQuery)->where('verification_status', VendorVerificationStatus::Verified)->count(),
            'rejected' => (clone $baseQuery)->where('verification_status', VendorVerificationStatus::Rejected)->count(),
            'unverified' => (clone $baseQuery)->where('verification_status', VendorVerificationStatus::Unverified)->count(),
        ];

        return view('pages.admin.vendors.index', [
            'vendors' => $vendors,
            'counts' => $counts,
            'currentStatus' => $statusFilter ?? 'all',
            'search' => $search,
            'assignedRegion' => $user->assignedRegion,
        ]);
    }

    /**
     * Show vendor KYC verification dossier.
     */
    public function show(VendorProfile $vendor): View
    {
        $this->authorizeRegionalAccess($vendor);

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
        $this->authorizeRegionalAccess($vendor);

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
        $this->authorizeRegionalAccess($vendor);

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

    /**
     * Request document revisions from vendor (Policy 6.1).
     */
    public function requestRevision(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $this->authorizeRegionalAccess($vendor);

        $request->validate([
            'revision_notes' => ['required', 'string', 'max:500'],
        ]);

        $notes = $request->input('revision_notes');

        $vendor->update([
            'verification_status' => VendorVerificationStatus::NeedsRevision,
            'revision_notes' => $notes,
            'is_open' => false,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_vendor_kyc_revision_requested',
            'auditable_type' => VendorProfile::class,
            'auditable_id' => $vendor->id,
            'new_values' => [
                'status' => VendorVerificationStatus::NeedsRevision->value,
                'revision_notes' => $notes,
                'requested_by' => auth()->user()->name,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.vendors.show', $vendor->id)
            ->with('info', "Pendaftaran mitra '{$vendor->business_name}' telah dikembalikan untuk perbaikan berkas.");
    }

    /**
     * Request access to sensitive SKCK document dossier from Superadmin (Policy 6.3).
     */
    public function requestSkckAccess(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $this->authorizeRegionalAccess($vendor);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $existingPending = SkckAccessRequest::where('admin_id', auth()->id())
            ->where('vendor_profile_id', $vendor->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return back()->with('info', 'Permohonan akses dokumen SKCK Anda masih menunggu review Superadmin.');
        }

        $accessRequest = SkckAccessRequest::create([
            'admin_id' => auth()->id(),
            'vendor_profile_id' => $vendor->id,
            'reason' => $request->input('reason'),
            'status' => 'pending',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_skck_access_requested',
            'auditable_type' => SkckAccessRequest::class,
            'auditable_id' => $accessRequest->id,
            'new_values' => [
                'vendor_id' => $vendor->id,
                'reason' => $request->input('reason'),
            ],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Permohonan izin akses dokumen fisik SKCK telah dikirimkan ke Superadmin.');
    }

    /**
     * Download or view sensitive SKCK document with strict authorization (Policy 6.3).
     */
    public function downloadSkckDocument(Request $request, VendorProfile $vendor)
    {
        $this->authorizeRegionalAccess($vendor);
        $user = auth()->user();

        if (! $vendor->hasActiveSkckAccessFor($user)) {
            abort(403, 'Akses ditolak: Dokumen sensitif SKCK dilindungi kebijakan privasi ketat. Anda harus memiliki persetujuan akses aktif dari Superadmin.');
        }

        if ($vendor->skck_pruned_at !== null || empty($vendor->skck_path)) {
            return back()->with('error', 'Dokumen fisik SKCK telah dihapus secara permanen dari server penyimpanan sesuai kebijakan retensi privasi.');
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'skck_document_accessed',
            'auditable_type' => VendorProfile::class,
            'auditable_id' => $vendor->id,
            'new_values' => [
                'accessed_by' => $user->name,
                'role' => $user->role->value,
            ],
            'ip_address' => $request->ip(),
        ]);

        if (file_exists(storage_path('app/'.$vendor->skck_path))) {
            return response()->file(storage_path('app/'.$vendor->skck_path));
        }

        return response()->json([
            'message' => 'Berkas dokumen SKCK aktif untuk keperluan audit resmi.',
            'vendor' => $vendor->business_name,
            'skck_status' => $vendor->skck_status,
            'skck_expires_at' => $vendor->skck_expires_at?->toDateString(),
            'path' => $vendor->skck_path,
        ]);
    }

    /**
     * Process vendor privacy request: anonymize and prune sensitive identity & SKCK files.
     */
    public function privacyAnonymize(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $this->authorizeRegionalAccess($vendor);

        $vendor->update([
            'skck_path' => null,
            'business_document_path' => null,
            'id_card_path' => null,
            'skck_pruned_at' => now(),
            'privacy_anonymized_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'vendor_privacy_data_anonymized',
            'auditable_type' => VendorProfile::class,
            'auditable_id' => $vendor->id,
            'new_values' => [
                'action' => 'Dokumen sensitif (KTP, SKCK) dibersihkan dari penyimpanan operasional.',
                'anonymized_by' => auth()->user()->name,
            ],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Dokumen sensitif mitra '{$vendor->business_name}' berhasil dibersihkan dari penyimpanan operasional sesuai permintaan privasi.");
    }

    /**
     * Authorize that the authenticated admin is allowed to access/moderate this vendor.
     */
    protected function authorizeRegionalAccess(VendorProfile $vendor): void
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return;
        }

        $region = $user->assignedRegion;
        if (! $region) {
            abort(403, 'Akses ditolak: Akun admin Anda belum ditugaskan ke wilayah operasional aktif.');
        }

        if (! $vendor->isInRegion($region)) {
            abort(403, 'Akses ditolak: Berkas KYC mitra berada di luar wilayah operasional Anda.');
        }
    }
}
