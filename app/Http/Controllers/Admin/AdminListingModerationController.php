<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminListingModerationController extends Controller
{
    /**
     * Display listings moderation queue.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $baseQuery = Listing::query()->forAdminRegion($user);

        $listings = (clone $baseQuery)
            ->when($statusFilter && $statusFilter !== 'all', function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('vendorProfile', fn ($v) => $v->where('business_name', 'like', "%{$search}%"));
                });
            })
            ->with(['vendorProfile', 'category', 'facilities', 'operationalRegion'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => (clone $baseQuery)->count(),
            'submitted' => (clone $baseQuery)->where('status', ListingStatus::Submitted)->count(),
            'published' => (clone $baseQuery)->where('status', ListingStatus::Published)->count(),
            'draft' => (clone $baseQuery)->where('status', ListingStatus::Draft)->count(),
            'rejected' => (clone $baseQuery)->where('status', ListingStatus::Rejected)->count(),
        ];

        return view('pages.admin.listings.index', [
            'listings' => $listings,
            'counts' => $counts,
            'currentStatus' => $statusFilter ?? 'all',
            'search' => $search,
            'assignedRegion' => $user->assignedRegion,
        ]);
    }

    /**
     * Show listing detail for moderation.
     */
    public function show(Listing $listing): View
    {
        $this->authorizeRegionalAccess($listing);

        $listing->load(['vendorProfile.user', 'category', 'facilities', 'images', 'slots', 'operationalRegion']);

        return view('pages.admin.listings.show', [
            'listing' => $listing,
        ]);
    }

    /**
     * Approve and publish listing to catalog.
     */
    public function publish(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeRegionalAccess($listing);

        $listing->update([
            'status' => ListingStatus::Published,
            'published_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_listing_published',
            'auditable_type' => Listing::class,
            'auditable_id' => $listing->id,
            'new_values' => [
                'status' => ListingStatus::Published->value,
                'published_by' => auth()->user()->name,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.listings.show', $listing->id)
            ->with('success', "Listing '{$listing->title}' berhasil disetujui & tayang publik di katalog TitipSini.");
    }

    /**
     * Reject or unpublish listing.
     */
    public function reject(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeRegionalAccess($listing);

        $listing->update([
            'status' => ListingStatus::Rejected,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'admin_listing_rejected',
            'auditable_type' => Listing::class,
            'auditable_id' => $listing->id,
            'new_values' => [
                'status' => ListingStatus::Rejected->value,
                'rejected_by' => auth()->user()->name,
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.listings.show', $listing->id)
            ->with('info', "Listing '{$listing->title}' ditolak / dinonaktifkan dari katalog publik.");
    }

    /**
     * Authorize that the authenticated admin is allowed to access/moderate this listing.
     */
    protected function authorizeRegionalAccess(Listing $listing): void
    {
        $user = auth()->user();

        if ($user->isSuperadmin()) {
            return;
        }

        $region = $user->assignedRegion;
        if (! $region) {
            abort(403, 'Akses ditolak: Akun admin Anda belum ditugaskan ke wilayah operasional aktif.');
        }

        $isCovered = ($listing->operational_region_id === $region->id)
            || (! empty($region->cities) && in_array($listing->city, $region->cities, true));

        if (! $isCovered) {
            abort(403, 'Akses ditolak: Listing ini berada di luar cakupan wilayah operasional yang Anda kelola.');
        }
    }
}
