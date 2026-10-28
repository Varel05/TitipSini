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
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->unsignedInteger('cycle_number')->default(1)->after('booking_id');
            $table->timestamp('expires_at')->nullable()->after('provider_response');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('current_payment_cycle')->default(1)->after('status');
            $table->timestamp('payment_cycle_reopened_at')->nullable()->after('current_payment_cycle');
            $table->text('payment_reopen_reason')->nullable()->after('payment_cycle_reopened_at');
            $table->foreignId('payment_reopened_by')->nullable()->after('payment_reopen_reason')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->dropColumn(['cycle_number', 'expires_at']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['payment_reopened_by']);
            $table->dropColumn([
                'current_payment_cycle',
                'payment_cycle_reopened_at',
                'payment_reopen_reason',
                'payment_reopened_by',
            ]);
        });
    }
};
