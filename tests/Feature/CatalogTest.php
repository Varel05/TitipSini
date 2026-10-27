<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\VendorVerificationStatus;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Listing;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private VendorProfile $vendorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $vendorUser = User::factory()->vendor()->create();
        $this->vendorProfile = VendorProfile::factory()->create([
            'user_id' => $vendorUser->id,
            'verification_status' => VendorVerificationStatus::Verified,
            'is_open' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Koper Wisata',
            'slug' => 'koper-wisata',
            'description' => 'Penitipan koper kabin dan tas',
            'is_active' => true,
        ]);
    }

    public function test_homepage_renders_successfully(): void
    {
        $listing = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Loker Wisata Stasiun',
            'slug' => 'loker-wisata-stasiun',
            'description' => 'Tempat titip aman dekat peron',
            'address' => 'Jl. Stasiun No. 1',
            'city' => 'Jakarta Pusat',
            'price_per_day' => 30000,
            'capacity_total' => 10,
            'capacity_available' => 8,
            'status' => ListingStatus::Published,
            'is_featured' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('TitipSini')
            ->assertSee('Loker Wisata Stasiun')
            ->assertSee('Jakarta Pusat');
    }

    public function test_catalog_explore_page_filters_by_city_and_category(): void
    {
        $listingJakarta = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Penitipan Monas Jakarta',
            'slug' => 'penitipan-monas-jakarta',
            'description' => 'Dekat Monas',
            'address' => 'Jl. Medan Merdeka',
            'city' => 'Jakarta Pusat',
            'price_per_day' => 25000,
            'capacity_total' => 10,
            'capacity_available' => 10,
            'status' => ListingStatus::Published,
        ]);

        $listingBandung = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Penitipan Stasiun Bandung',
            'slug' => 'penitipan-stasiun-bandung',
            'description' => 'Dekat Stasiun Hall',
            'address' => 'Jl. Kebon Kawung',
            'city' => 'Bandung',
            'price_per_day' => 20000,
            'capacity_total' => 10,
            'capacity_available' => 10,
            'status' => ListingStatus::Published,
        ]);

        // Filter by city: Bandung
        $response = $this->get(route('catalog.index', ['city' => 'Bandung']));
        $response->assertOk()
            ->assertSee('Penitipan Stasiun Bandung')
            ->assertDontSee('Penitipan Monas Jakarta');

        // Filter by category: koper-wisata
        $responseCat = $this->get(route('catalog.index', ['category' => 'koper-wisata']));
        $responseCat->assertOk()
            ->assertSee('Penitipan Monas Jakarta')
            ->assertSee('Penitipan Stasiun Bandung');
    }

    public function test_listing_detail_page_renders_with_facilities(): void
    {
        $facility = Facility::create([
            'name' => 'CCTV 24 Jam Nonstop',
            'slug' => 'cctv-24-jam',
        ]);

        $listing = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Vault Dokumen Sudirman',
            'slug' => 'vault-dokumen-sudirman',
            'description' => 'Tempat aman untuk dokumen rahasia',
            'address' => 'Jl. Jend. Sudirman',
            'city' => 'Jakarta Selatan',
            'price_per_hour' => 10000,
            'price_per_day' => 50000,
            'capacity_total' => 15,
            'capacity_available' => 12,
            'status' => ListingStatus::Published,
        ]);

        $listing->facilities()->attach($facility);

        $response = $this->get(route('catalog.show', $listing->slug));

        $response->assertOk()
            ->assertSee('Vault Dokumen Sudirman')
            ->assertSee('CCTV 24 Jam Nonstop')
            ->assertSee('50.000');
    }

    public function test_draft_listing_returns_404_publicly(): void
    {
        $draftListing = Listing::create([
            'vendor_profile_id' => $this->vendorProfile->id,
            'category_id' => $this->category->id,
            'title' => 'Draft Tempat Rahasia',
            'slug' => 'draft-tempat-rahasia',
            'description' => 'Masih dalam persiapan',
            'address' => 'Jl. Rahasia',
            'city' => 'Jakarta',
            'price_per_day' => 10000,
            'capacity_total' => 5,
            'capacity_available' => 5,
            'status' => ListingStatus::Draft,
        ]);

        $response = $this->get(route('catalog.show', $draftListing->slug));
        $response->assertNotFound();
    }
}
