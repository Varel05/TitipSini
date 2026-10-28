<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SkckAccessRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminSkckAccessController extends Controller
{
    /**
     * Display list of SKCK document access requests submitted by operational admins.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');

        $requests = SkckAccessRequest::query()
            ->when($statusFilter !== 'all', fn ($q) => $q->where('status', $statusFilter))
            ->with(['admin', 'vendorProfile.user', 'reviewer'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => SkckAccessRequest::count(),
            'pending' => SkckAccessRequest::where('status', 'pending')->count(),
            'approved' => SkckAccessRequest::where('status', 'approved')->count(),
            'rejected' => SkckAccessRequest::where('status', 'rejected')->count(),
        ];

        return view('pages.admin.skck-requests.index', [
            'requests' => $requests,
            'counts' => $counts,
            'currentStatus' => $statusFilter,
        ]);
    }

    /**
     * Approve admin's SKCK access request with temporary time-bound authorization.
     */
    public function approve(Request $request, SkckAccessRequest $accessRequest): RedirectResponse
    {
        $accessRequest->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'access_token' => Str::random(40),
            'expires_at' => now()->addHours(2), // 2-hour temporary viewing window
            'rejection_reason' => null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'superadmin_skck_access_approved',
            'auditable_type' => SkckAccessRequest::class,
            'auditable_id' => $accessRequest->id,
            'new_values' => [
                'admin_id' => $accessRequest->admin_id,
                'vendor_id' => $accessRequest->vendor_profile_id,
                'expires_at' => $accessRequest->expires_at->toDateTimeString(),
            ],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Permohonan akses dokumen SKCK oleh Admin '{$accessRequest->admin->name}' telah disetujui selama 2 jam.");
    }

    /**
     * Reject admin's SKCK access request.
     */
    public function reject(Request $request, SkckAccessRequest $accessRequest): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $reason = $request->input('rejection_reason');

        $accessRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
            'access_token' => null,
            'expires_at' => null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'superadmin_skck_access_rejected',
            'auditable_type' => SkckAccessRequest::class,
            'auditable_id' => $accessRequest->id,
            'new_values' => [
                'admin_id' => $accessRequest->admin_id,
                'vendor_id' => $accessRequest->vendor_profile_id,
                'reason' => $reason,
            ],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('info', 'Permohonan akses dokumen SKCK ditolak.');
    }
}
