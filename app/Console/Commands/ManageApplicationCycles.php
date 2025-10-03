<?php

namespace App\Console\Commands;

use App\Models\ApplicationCycle;
use App\Services\ScholarshipPointService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManageApplicationCycles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cycles:manage 
                            {--dry-run : Show what would be done without making changes}
                            {--force : Force cycle transitions even if not scheduled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application cycles: activate, close, and expire scholarship points';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔄 Managing Application Cycles...');

        if ($dryRun) {
            $this->warn('🧪 DRY RUN MODE - No changes will be made');
        }

        // Update cycle statuses
        $this->info('📅 Checking cycle statuses...');

        if ($dryRun) {
            $this->showCycleStatusPreview();
        } else {
            $statusUpdates = ApplicationCycle::updateStatuses();

            if ($statusUpdates['activated'] > 0) {
                $this->info("✅ Activated {$statusUpdates['activated']} cycle(s)");
            }

            if ($statusUpdates['closed'] > 0) {
                $this->info("🔒 Closed {$statusUpdates['closed']} cycle(s)");
            }

            if ($statusUpdates['activated'] === 0 && $statusUpdates['closed'] === 0) {
                $this->info('ℹ️  No cycle status changes needed');
            }
        }

        // Expire old points
        $this->info('⏰ Checking for expired scholarship points...');

        $scholarshipPointService = app(ScholarshipPointService::class);

        if ($dryRun) {
            $this->showExpiryPreview($scholarshipPointService);
        } else {
            $expiredCount = $scholarshipPointService->expireOldPoints();

            if ($expiredCount > 0) {
                $this->info("⚰️  Expired {$expiredCount} old scholarship point(s)");
                Log::info("Expired {$expiredCount} scholarship points via command");
            } else {
                $this->info('ℹ️  No points to expire');
            }
        }

        // Check for cycle reset (July 1)
        if ($this->shouldResetCycles() || $force) {
            $this->info('🔄 Performing cycle reset...');

            if ($dryRun) {
                $this->showResetPreview();
            } else {
                $resetStats = $scholarshipPointService->resetForNewCycle();
                $this->info('🗑️  Reset complete:');
                $this->info("   - Expired points: {$resetStats['expired_points']}");
                $this->info("   - Expired commissions: {$resetStats['expired_commissions']}");
            }
        }

        // Show current status
        $this->showCurrentStatus();

        $this->info('✅ Application cycle management complete!');

        return Command::SUCCESS;
    }

    /**
     * Check if we should reset cycles (July 1).
     */
    private function shouldResetCycles(): bool
    {
        $now = now();

        return $now->month === 7 && $now->day === 1;
    }

    /**
     * Show what cycle status changes would be made.
     */
    private function showCycleStatusPreview(): void
    {
        $now = now();

        $toActivate = ApplicationCycle::where('status', 'upcoming')
            ->where('start_date', '<=', $now->toDateString())
            ->where('end_date', '>=', $now->toDateString())
            ->get();

        $toClose = ApplicationCycle::where('status', 'active')
            ->where('end_date', '<', $now->toDateString())
            ->get();

        if ($toActivate->count() > 0) {
            $this->info("Would activate {$toActivate->count()} cycle(s):");
            foreach ($toActivate as $cycle) {
                $this->line("  - {$cycle->year} ({$cycle->start_date} to {$cycle->end_date})");
            }
        }

        if ($toClose->count() > 0) {
            $this->info("Would close {$toClose->count()} cycle(s):");
            foreach ($toClose as $cycle) {
                $this->line("  - {$cycle->year} ({$cycle->start_date} to {$cycle->end_date})");
            }
        }

        if ($toActivate->count() === 0 && $toClose->count() === 0) {
            $this->info('No cycle status changes needed');
        }
    }

    /**
     * Show what points would be expired.
     */
    private function showExpiryPreview(ScholarshipPointService $service): void
    {
        $expiredCount = \App\Models\ScholarshipPoint::active()
            ->where('expires_at', '<', now())
            ->count();

        if ($expiredCount > 0) {
            $this->info("Would expire {$expiredCount} scholarship point(s)");
        } else {
            $this->info('No points to expire');
        }
    }

    /**
     * Show what would be reset.
     */
    private function showResetPreview(): void
    {
        $pointsToExpire = \App\Models\ScholarshipPoint::active()
            ->where('expires_at', '<', now())
            ->count();

        $commissionsToExpire = \App\Models\ScholarshipCommission::available()
            ->where('application_year', '<', \App\Models\ScholarshipPoint::getCurrentApplicationYear())
            ->count();

        $this->info('Would perform cycle reset:');
        $this->line("  - Points to expire: {$pointsToExpire}");
        $this->line("  - Commissions to expire: {$commissionsToExpire}");
    }

    /**
     * Show current system status.
     */
    private function showCurrentStatus(): void
    {
        $this->info('📊 Current Status:');

        $currentCycle = ApplicationCycle::getCurrentCycle();
        if ($currentCycle) {
            $daysRemaining = $currentCycle->getDaysRemaining();
            $progress = $currentCycle->getProgressPercentage();

            $this->line("  🎯 Active Cycle: {$currentCycle->year}");
            $this->line("  📅 Period: {$currentCycle->start_date->format('M j')} - {$currentCycle->end_date->format('M j, Y')}");
            $this->line("  ⏱️  Days Remaining: {$daysRemaining}");
            $this->line("  📈 Progress: {$progress}%");
        } else {
            $this->line('  ⚠️  No active cycle');
        }

        $activePoints = \App\Models\ScholarshipPoint::active()->count();
        $availableCommissions = \App\Models\ScholarshipCommission::available()->count();

        $this->line("  🎯 Active Points: {$activePoints}");
        $this->line("  🏆 Available Scholarships: {$availableCommissions}");
    }
}
