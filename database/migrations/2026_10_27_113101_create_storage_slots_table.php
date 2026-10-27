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
        Schema::create('storage_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->string('slot_number', 50);
            $table->string('size', 30)->default('medium');
            $table->decimal('max_weight_kg', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_occupied')->default(false);
            $table->text('notes')->nullable();
            $table->unique(['listing_id', 'slot_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_slots');
    }
};
