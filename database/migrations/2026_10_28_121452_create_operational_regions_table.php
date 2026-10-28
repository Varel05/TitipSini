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
        Schema::create('operational_regions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->foreignId('admin_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->json('provinces')->nullable();
            $table->json('cities');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_under_supervision')->default(false);
            $table->text('supervision_notes')->nullable();
            $table->timestamps();
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('operational_region_id')->nullable()->after('category_id')->constrained('operational_regions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('operational_region_id');
        });

        Schema::dropIfExists('operational_regions');
    }
};
