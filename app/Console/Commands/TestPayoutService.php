<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PayoutService;
use Illuminate\Console\Command;

class TestPayoutService extends Command
{
    protected $signature = 'app:test-payout {agentEmail} {amount}';
    protected $description = 'Manually tests the PayoutService logic for a specific agent and amount.';

    public function handle(PayoutService $payoutService): int
    {
        $agentEmail = $this->argument('agentEmail');
        $amount = (float) $this->argument('amount');

        $agent = User::where('email', $agentEmail)->first();

        if (! $agent) {
            $this->error("Agent with email {$agentEmail} not found.");
            return self::FAILURE;
        }

        $this->info("Found Agent: {$agent->name}");

        $availableBalance = $agent->commissions()->where('status', 'earned')->sum('amount');
        $this->info("Initial Available Balance: \${$availableBalance}");

        try {
            $this->info("Attempting to create a payout for \${$amount}...");
            $payoutService->createPayout($agent, $amount);
            
            $this->info('------------------------------------');
            $this->info('✅ Payout Service Executed Successfully!');
            $this->info('------------------------------------');
            
            $newAvailableBalance = $agent->commissions()->where('status', 'earned')->sum('amount');
            $newPendingBalance = $agent->commissions()->where('status', 'requested')->sum('amount');
            
            $this->info("Final Available Balance: \${$newAvailableBalance}");
            $this->info("Final Pending Balance: \${$newPendingBalance}");
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
