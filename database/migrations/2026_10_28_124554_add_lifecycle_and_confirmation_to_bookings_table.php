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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('returned_at')->nullable()->after('actual_check_out_at');
            $table->timestamp('auto_complete_at')->nullable()->after('returned_at');
            $table->timestamp('customer_confirmed_at')->nullable()->after('auto_complete_at');
            $table->text('rejection_reason')->nullable()->after('cancellation_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'returned_at',
                'auto_complete_at',
                'customer_confirmed_at',
                'rejection_reason',
            ]);
        });
    }
};
