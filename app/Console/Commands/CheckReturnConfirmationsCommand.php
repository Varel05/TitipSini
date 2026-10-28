<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Console\Command;

class CheckReturnConfirmationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'titipsini:check-return-confirmations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-complete bookings after 48-hour return confirmation window lapses without complaints (Policy 8.2)';

    /**
     * Execute the console command.
     */
    public function handle(BookingService $bookingService): int
    {
        $this->info('Checking returned bookings awaiting customer confirmation...');

        $now = now();
        $expiredConfirmations = Booking::where('status', BookingStatus::ReturnedByVendor)
            ->whereNotNull('auto_complete_at')
            ->where('auto_complete_at', '<=', $now)
            ->get();

        $completedCount = 0;

        foreach ($expiredConfirmations as $booking) {
            $bookingService->autoComplete48Hours($booking);

            AuditLog::create([
                'user_id' => null, // Automated background system event
                'action' => 'booking_auto_completed_after_48h',
                'auditable_type' => Booking::class,
                'auditable_id' => $booking->id,
                'new_values' => [
                    'booking_code' => $booking->booking_code,
                    'returned_at' => $booking->returned_at?->toIso8601String(),
                    'auto_completed_at' => $now->toIso8601String(),
                    'reason' => '48-hour customer return confirmation window elapsed without disputes (Policy 8.2).',
                ],
                'ip_address' => '127.0.0.1',
            ]);

            $completedCount++;
            $this->info("Booking [{$booking->booking_code}] diselesaikan otomatis (48 jam terlewati tanpa sengketa).");
        }

        $this->info("Completed {$completedCount} returned bookings.");

        return Command::SUCCESS;
    }
}
