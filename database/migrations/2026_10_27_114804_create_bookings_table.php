<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 32)->unique()->index();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('storage_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('created')->index();
            $table->string('duration_type', 20)->default('day');
            $table->unsignedInteger('duration_units')->default(1);
            $table->unsignedInteger('total_items')->default(1);
            $table->dateTime('check_in_plan_at');
            $table->dateTime('check_out_plan_at');
            $table->dateTime('actual_check_in_at')->nullable();
            $table->dateTime('actual_check_out_at')->nullable();
            $table->decimal('storage_fee', 12, 2);
            $table->decimal('platform_fee', 12, 2);
            $table->decimal('gateway_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('vendor_earnings', 12, 2);
            $table->json('pricing_snapshot');
            $table->string('security_seal_code', 50)->nullable()->index();
            $table->string('check_in_photo_path', 255)->nullable();
            $table->string('check_out_photo_path', 255)->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
