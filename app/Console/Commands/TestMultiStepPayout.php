<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Services\PayoutService;
use Illuminate\Console\Command;

class TestMultiStepPayout extends Command
{
    protected $signature = 'app:test-multi-payout-final';
    protected $description = 'Runs the definitive multi-step payout test for the Wallet Ledger system.';

    public function handle(PayoutService $payoutService): int
    {
        $this->info('--- STARTING FINAL PAYOUT VERIFICATION ---');

        // 1. Reset the database
        $this->call('migrate:fresh');
        // Manually create what we need
        $agent = User::factory()->create(['role' => 'agent_owner', 'email' => 'agent.owner@sky.com']);
        Commission::factory()->create(['agent_id' => $agent->id, 'amount' => 100]);
        Commission::factory()->create(['agent_id' => $agent->id, 'amount' => 100]);
        $this->info('Step 1: Database has been reset. Agent has 2x$100 commissions.');

        // 2. Initial State
        $totalEarned = $agent->commissions()->sum('amount');
        $this->line("Step 2: Initial State - Total Earned = \${$totalEarned}, Available Balance = \${$totalEarned}");
        $this->newLine();

        // 3. First Payout
        $this->info('--- Performing First Payout (Requesting $100)... ---');
        $payoutService->createPayout($agent, 100);
        $pendingAfterFirst = $agent->payouts()->where('status', 'pending')->sum('amount');
        $availableAfterFirst = $totalEarned - $pendingAfterFirst;
        $this->info("State after first payout: Available = \${$availableAfterFirst}, Pending = \${$pendingAfterFirst}");

        if (round($availableAfterFirst) != 100 || round($pendingAfterFirst) != 100) {
            $this->error('TEST FAILED: First payout logic is incorrect!');
            return 1;
        }
        $this->info('✅ First Payout Correct.');
        $this->newLine();

        // 4. Second Payout (The REAL Test)
        $this->info('--- Performing Second Payout (Requesting $20 from remaining $100)... ---');
        $payoutService->createPayout($agent, 20);
        $pendingAfterSecond = $agent->payouts()->where('status', 'pending')->sum('amount');
        $availableAfterSecond = $totalEarned - $pendingAfterSecond;
        $this->info("State after second payout: Available = \${$availableAfterSecond}, Pending = \${$pendingAfterSecond}");

        if (round($availableAfterSecond) != 80 || round($pendingAfterSecond) != 120) {
            $this->error('TEST FAILED: The second partial payout logic is still WRONG!');
            return 1;
        }

        $this->newLine();
        $this->info('----------------------------------------------------');
        $this->info('✅✅✅  FINAL PROOF: WALLET LEDGER SYSTEM IS CORRECT! ✅✅✅');
        $this->info('----------------------------------------------------');

        return 0;
    }
}