<?php

namespace Database\Factories;

use App\Enums\VendorVerificationStatus;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VendorProfile>
 */
class VendorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->vendor(),
            'business_name' => fake()->company().' Storage',
            'business_type' => fake()->randomElement(['individu', 'umkm', 'pt']),
            'description' => fake()->paragraph(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'province' => 'DKI Jakarta',
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(-6.3, -6.1),
            'longitude' => fake()->longitude(106.7, 106.9),
            'verification_status' => VendorVerificationStatus::Unverified,
            'is_open' => true,
        ];
    }

    /**
     * State for verified vendor.
     */
    public function verified(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VendorVerificationStatus::Verified,
            'verified_at' => now(),
            'verified_by' => $admin?->id ?? User::factory()->admin(),
        ]);
    }

    /**
     * State for pending verification vendor.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VendorVerificationStatus::Pending,
        ]);
    }

    /**
     * State for rejected vendor.
     */
    public function rejected(string $reason = 'Dokumen tidak jelas'): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VendorVerificationStatus::Rejected,
            'rejection_reason' => $reason,
        ]);
    }
}
