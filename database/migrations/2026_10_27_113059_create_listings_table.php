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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->string('tagline', 255)->nullable();
            $table->text('description');
            $table->text('address');
            $table->string('city', 100)->index();
            $table->string('district', 100)->nullable();
            $table->string('postal_code', 15)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('price_per_hour', 12, 2)->nullable();
            $table->decimal('price_per_day', 12, 2);
            $table->decimal('price_per_week', 12, 2)->nullable();
            $table->decimal('price_per_month', 12, 2)->nullable();
            $table->unsignedInteger('capacity_total')->default(10);
            $table->unsignedInteger('capacity_available')->default(10);
            $table->string('status', 30)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->string('featured_image', 255)->nullable();
            $table->text('rules')->nullable();
            $table->json('operational_hours')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
