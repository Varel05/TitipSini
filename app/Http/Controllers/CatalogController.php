<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display the public homepage with hero search, categories, and featured listings.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['listings' => fn ($query) => $query->where('status', ListingStatus::Published)])
            ->get();

        $featuredListings = Listing::query()
            ->published()
            ->featured()
            ->with(['category', 'vendorProfile', 'facilities'])
            ->latest('published_at')
            ->take(6)
            ->get();

        $popularCities = Listing::query()
            ->published()
            ->select('city')
            ->distinct()
            ->pluck('city');

        return view('pages.home', [
            'categories' => $categories,
            'featuredListings' => $featuredListings,
            'popularCities' => $popularCities,
        ]);
    }

    /**
     * Display the searchable and filterable listings catalog.
     */
    public function explore(Request $request): View
    {
        $query = Listing::query()
            ->published()
            ->with(['category', 'vendorProfile', 'facilities']);

        if ($search = $request->input('q')) {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($categorySlug = $request->input('category')) {
            $query->whereHas('category', function ($subQuery) use ($categorySlug): void {
                $subQuery->where('slug', $categorySlug);
            });
        }

        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        if ($facilitySlug = $request->input('facility')) {
            $query->whereHas('facilities', function ($subQuery) use ($facilitySlug): void {
                $subQuery->where('slug', $facilitySlug);
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc' => $query->orderBy('price_per_day', 'asc'),
            'price_desc' => $query->orderBy('price_per_day', 'desc'),
            'capacity' => $query->orderBy('capacity_available', 'desc'),
            default => $query->latest('published_at'),
        };

        $listings = $query->paginate(9)->withQueryString();

        $categories = Category::where('is_active', true)->get();
        $facilities = Facility::all();
        $cities = Listing::query()->published()->select('city')->distinct()->pluck('city');

        return view('pages.catalog', [
            'listings' => $listings,
            'categories' => $categories,
            'facilities' => $facilities,
            'cities' => $cities,
            'currentFilters' => [
                'q' => $search,
                'category' => $categorySlug,
                'city' => $city,
                'facility' => $facilitySlug,
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Display a single listing detail with full operational info and price calculator.
     */
    public function show(string $slug): View
    {
        $listing = Listing::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'vendorProfile.user', 'facilities', 'images', 'slots'])
            ->firstOrFail();

        $similarListings = Listing::query()
            ->published()
            ->where('id', '!=', $listing->id)
            ->where('city', $listing->city)
            ->take(3)
            ->get();

        return view('pages.listing-detail', [
            'listing' => $listing,
            'similarListings' => $similarListings,
        ]);
    }
}
