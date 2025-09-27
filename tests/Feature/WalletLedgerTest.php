<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Commission;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_ledger_flow(): void
    {
        $walletService = app(WalletService::class);

        $agent = User::factory()->create(['role' => 'agent_owner']);
        $wallet = Wallet::create([
            'agent_id' => $agent->id,
            'available_balance' => 0,
            'pending_balance' => 0,
        ]);

        $commissionOne = Commission::factory()->create(['agent_id' => $agent->id, 'amount' => 50]);
        $commissionTwo = Commission::factory()->create(['agent_id' => $agent->id, 'amount' => 75]);

        $walletService->credit($commissionOne);
        $walletService->credit($commissionTwo);

        $wallet->refresh();
        $this->assertEquals(125, (float) $wallet->available_balance);
        $this->assertEquals(0, (float) $wallet->pending_balance);

        $payout = $walletService->requestPayout($agent, 100);
        $wallet->refresh();
        $this->assertEquals(25, (float) $wallet->available_balance);
        $this->assertEquals(100, (float) $wallet->pending_balance);
        $this->assertEquals('pending', $payout->status);

        $walletService->approvePayout($payout->fresh());
        $wallet->refresh();
        $this->assertEquals(25, (float) $wallet->available_balance);
        $this->assertEquals(0, (float) $wallet->pending_balance);
        $this->assertEquals('paid', $payout->fresh()->status);

        // Test paid commissions calculation
        $totalPaidCommissions = $agent->payouts()
            ->where('status', 'paid')
            ->sum('amount');
        $this->assertEquals(100, (float) $totalPaidCommissions);
    }
}
