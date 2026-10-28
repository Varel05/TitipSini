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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $table->string('category', 50)->index();
            $table->string('status', 30)->default('open')->index();
            $table->boolean('is_escalated')->default(false)->index();
            $table->timestamp('escalated_at')->nullable();
            $table->string('title', 200);
            $table->text('description');
            $table->json('evidence_paths')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->decimal('compensation_amount', 12, 2)->default(0);
            $table->decimal('vendor_penalty_amount', 12, 2)->default(0);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('is_open');
            $table->timestamp('suspended_at')->nullable()->after('is_suspended');
            $table->text('suspension_reason')->nullable()->after('suspended_at');
            $table->foreignId('suspended_by')->nullable()->after('suspension_reason')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');

        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropColumn(['is_suspended', 'suspended_at', 'suspension_reason', 'suspended_by']);
        });
    }
};
