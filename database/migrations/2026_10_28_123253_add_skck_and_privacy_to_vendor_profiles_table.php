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
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->string('skck_path')->nullable()->after('business_document_path');
            $table->date('skck_issued_at')->nullable()->after('skck_path');
            $table->date('skck_expires_at')->nullable()->after('skck_issued_at');
            $table->date('skck_grace_period_ends_at')->nullable()->after('skck_expires_at');
            $table->timestamp('skck_verified_at')->nullable()->after('skck_grace_period_ends_at');
            $table->string('skck_status', 30)->default('valid')->after('skck_verified_at');
            $table->timestamp('skck_pruned_at')->nullable()->after('skck_status');
            $table->text('revision_notes')->nullable()->after('rejection_reason');
            $table->timestamp('privacy_anonymized_at')->nullable()->after('skck_pruned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'skck_path',
                'skck_issued_at',
                'skck_expires_at',
                'skck_grace_period_ends_at',
                'skck_verified_at',
                'skck_status',
                'skck_pruned_at',
                'revision_notes',
                'privacy_anonymized_at',
            ]);
        });
    }
};
