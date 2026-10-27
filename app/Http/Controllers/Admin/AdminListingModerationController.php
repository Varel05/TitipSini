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
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $listings = Listing::query()
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
            ->with(['vendorProfile', 'category', 'facilities'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Listing::count(),
            'submitted' => Listing::where('status', ListingStatus::Submitted)->count(),
            'published' => Listing::where('status', ListingStatus::Published)->count(),
            'draft' => Listing::where('status', ListingStatus::Draft)->count(),
            'rejected' => Listing::where('status', ListingStatus::Rejected)->count(),
        ];

        return view('pages.admin.listings.index', [
            'listings' => $listings,
            'counts' => $counts,
            'currentStatus' => $statusFilter ?? 'all',
            'search' => $search,
        ]);
    }

    /**
     * Show listing detail for moderation.
     */
    public function show(Listing $listing): View
    {
        $listing->load(['vendorProfile.user', 'category', 'facilities', 'images', 'slots']);

        return view('pages.admin.listings.show', [
            'listing' => $listing,
        ]);
    }

    /**
     * Approve and publish listing to catalog.
     */
    public function publish(Request $request, Listing $listing): RedirectResponse
    {
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
}
