<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payout;
use App\Models\Commission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    // DEPOSIT: When a commission is earned
    public function credit(Commission $commission): void
    {
        DB::transaction(function () use ($commission) {
            $wallet = $commission->agent->wallet()->lockForUpdate()->firstOrCreate(
                ['agent_id' => $commission->agent->id],
                ['available_balance' => 0, 'pending_balance' => 0]
            );
            $wallet->available_balance += $commission->amount;
            $wallet->save();
            Log::info("Wallet credited for Agent ID {$commission->agent->id} with {$commission->amount}. New available: {$wallet->available_balance}");
        });
    }

    // WITHDRAWAL: When a payout is requested
    public function requestPayout(User $agent, float $amount): Payout
    {
        return DB::transaction(function () use ($agent, $amount) {
            $wallet = $agent->wallet()->lockForUpdate()->first();

            if ($amount > $wallet->available_balance) {
                throw new \Exception('Insufficient funds.');
            }

            $wallet->available_balance -= $amount;
            $wallet->pending_balance += $amount;
            $wallet->save();

            return $agent->payouts()->create(['amount' => $amount, 'status' => 'pending']);
        });
    }

    // ADMIN ACTION: When a payout is approved
    public function approvePayout(Payout $payout): void
    {
        DB::transaction(function () use ($payout) {
            $wallet = $payout->agent->wallet()->lockForUpdate()->first();
            $wallet->pending_balance -= $payout->amount;
            $wallet->save();
            $payout->update(['status' => 'paid']);
        });
    }

    // ADMIN ACTION: When a payout is rejected
    public function rejectPayout(Payout $payout): void
    {
        DB::transaction(function () use ($payout) {
            $wallet = $payout->agent->wallet()->lockForUpdate()->first();
            $wallet->pending_balance -= $payout->amount;
            $wallet->available_balance += $payout->amount; // Refund the money
            $wallet->save();
            $payout->update(['status' => 'rejected']);
        });
    }
}
