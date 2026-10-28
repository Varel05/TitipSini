<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\VendorProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckSkckExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'titipsini:check-skck-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enforce SKCK expiry policies, 30-day grace period restrictions, and 7-day document retention pruning (Policy 6.2 & 6.3)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SKCK expiration and retention lifecycle audit...');

        $now = now();
        $blockedCount = 0;
        $graceCount = 0;
        $prunedCount = 0;

        $vendors = VendorProfile::whereNotNull('skck_expires_at')->get();

        foreach ($vendors as $vendor) {
            $graceEnd = $vendor->skck_grace_period_ends_at
                ?? $vendor->skck_expires_at->copy()->addDays(30);

            // 1. Check if 30-day grace period has expired -> Block new bookings
            if ($now->greaterThan($graceEnd)) {
                if ($vendor->skck_status !== 'expired_blocked' || $vendor->is_open) {
                    $vendor->update([
                        'skck_status' => 'expired_blocked',
                        'is_open' => false,
                    ]);

                    AuditLog::create([
                        'user_id' => null, // Automated system event
                        'action' => 'skck_grace_period_expired_blocked',
                        'auditable_type' => VendorProfile::class,
                        'auditable_id' => $vendor->id,
                        'new_values' => [
                            'vendor' => $vendor->business_name,
                            'skck_expires_at' => $vendor->skck_expires_at->toDateString(),
                            'grace_period_ends_at' => $graceEnd->toDateString(),
                            'reason' => 'Masa tenggang 30 hari SKCK berakhir tanpa perpanjangan terverifikasi.',
                        ],
                        'ip_address' => '127.0.0.1',
                    ]);

                    $blockedCount++;
                    $this->warn("Vendor [{$vendor->business_name}] diblokir dari booking baru karena masa tenggang SKCK telah habis.");
                }
            } elseif ($now->greaterThan($vendor->skck_expires_at)) {
                // 2. Currently within 30-day grace period
                if ($vendor->skck_status !== 'in_grace_period') {
                    $vendor->update(['skck_status' => 'in_grace_period']);
                    $graceCount++;
                    $this->info("Vendor [{$vendor->business_name}] memasuki masa tenggang 30 hari SKCK (berakhir: {$graceEnd->toDateString()}).");
                }
            } elseif ($vendor->skck_expires_at->diffInDays($now) <= 30) {
                // 3. Expiring soon (within 30 days)
                if ($vendor->skck_status !== 'expiring_soon') {
                    $vendor->update(['skck_status' => 'expiring_soon']);
                }
            }
        }

        // 4. Document retention pruning (Policy 6.3: Auto-prune raw SKCK files after 7 days post-verification)
        $prunableVendors = VendorProfile::where('verification_status', 'verified')
            ->whereNotNull('verified_at')
            ->where('verified_at', '<=', $now->copy()->subDays(7))
            ->whereNull('skck_pruned_at')
            ->whereNotNull('skck_path')
            ->get();

        foreach ($prunableVendors as $pVendor) {
            if ($pVendor->skck_path && Storage::exists($pVendor->skck_path)) {
                Storage::delete($pVendor->skck_path);
            }

            $pVendor->update([
                'skck_path' => null,
                'skck_pruned_at' => $now,
            ]);

            AuditLog::create([
                'user_id' => null,
                'action' => 'skck_auto_pruned_retention',
                'auditable_type' => VendorProfile::class,
                'auditable_id' => $pVendor->id,
                'new_values' => [
                    'vendor' => $pVendor->business_name,
                    'verified_at' => $pVendor->verified_at->toDateTimeString(),
                    'pruned_at' => $now->toDateTimeString(),
                    'retention_policy' => 'Maksimal 7 hari pasca-verifikasi dokumen fisik SKCK dibersihkan.',
                ],
                'ip_address' => '127.0.0.1',
            ]);

            $prunedCount++;
            $this->info("Dokumen fisik SKCK vendor [{$pVendor->business_name}] telah diprune otomatis sesuai kebijakan retensi 7 hari.");
        }

        $this->info("SKCK Audit Complete: {$blockedCount} blocked, {$graceCount} in grace period, {$prunedCount} documents pruned.");

        return Command::SUCCESS;
    }
}
