<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\VendorVerificationStatus;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_casts_role_to_enum_correctly(): void
    {
        $customer = User::factory()->customer()->create();
        $vendor = User::factory()->vendor()->create();
        $admin = User::factory()->admin()->create();
        $superadmin = User::factory()->superadmin()->create();

        $this->assertInstanceOf(UserRole::class, $customer->role);
        $this->assertSame(UserRole::Customer, $customer->role);
        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());

        $this->assertSame(UserRole::Vendor, $vendor->role);
        $this->assertTrue($vendor->isVendor());

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isAdministrative());

        $this->assertSame(UserRole::Superadmin, $superadmin->role);
        $this->assertTrue($superadmin->isSuperadmin());
        $this->assertTrue($superadmin->isAdministrative());
    }

    public function test_user_has_customer_profile_relationship(): void
    {
        $user = User::factory()->customer()->create();
        $profile = CustomerProfile::factory()->create([
            'user_id' => $user->id,
            'emergency_contact_name' => 'Keluarga Budi',
        ]);

        $this->assertNotNull($user->customerProfile);
        $this->assertSame('Keluarga Budi', $user->customerProfile->emergency_contact_name);
        $this->assertSame($user->id, $profile->user->id);
    }

    public function test_vendor_profile_relationship_and_verification_status(): void
    {
        $admin = User::factory()->admin()->create();
        $vendorUser = User::factory()->vendor()->create();

        $profile = VendorProfile::factory()->verified($admin)->create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Gudang Aman TitipSini',
        ]);

        $this->assertNotNull($vendorUser->vendorProfile);
        $this->assertSame('Gudang Aman TitipSini', $vendorUser->vendorProfile->business_name);
        $this->assertSame(VendorVerificationStatus::Verified, $profile->verification_status);
        $this->assertTrue($profile->isVerified());
        $this->assertTrue($profile->canAcceptBookings());
        $this->assertSame($admin->id, $profile->verifier->id);
    }

    public function test_unverified_vendor_cannot_accept_bookings(): void
    {
        $vendorUser = User::factory()->vendor()->create();
        $profile = VendorProfile::factory()->pending()->create([
            'user_id' => $vendorUser->id,
        ]);

        $this->assertFalse($profile->isVerified());
        $this->assertFalse($profile->canAcceptBookings());
    }

    public function test_prospective_vendor_can_register_successfully(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Varel Deva Dewangga',
            'email' => 'varel.vendor@example.com',
            'phone' => '083896693316',
            'password' => 'Password123#',
            'password_confirmation' => 'Password123#',
            'role' => 'vendor',
            'business_name' => 'Loker Penitipan Dewangga',
            'address' => 'Jl. Malioboro No. 45',
        ]);

        $response->assertRedirect(route('vendor.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'varel.vendor@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Vendor, $user->role);

        $vendorProfile = $user->vendorProfile;
        $this->assertNotNull($vendorProfile);
        $this->assertSame('Loker Penitipan Dewangga', $vendorProfile->business_name);
        $this->assertSame('083896693316', $vendorProfile->phone);
        $this->assertSame(VendorVerificationStatus::Unverified, $vendorProfile->verification_status);
        $this->assertFalse($vendorProfile->is_open);
    }

    public function test_customer_can_register_successfully(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Budi Customer',
            'email' => 'budi.customer@example.com',
            'phone' => '081234567890',
            'password' => 'Password123#',
            'password_confirmation' => 'Password123#',
            'role' => 'customer',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'budi.customer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertNotNull($user->customerProfile);
    }
}
