<?php

namespace App\Filament\Agent\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class MyWalletStats extends BaseWidget
{
    protected function getStats(): array
    {
        $agent = auth()->user();
        $wallet = $agent->wallet;

        // Current wallet balances
        $available = (float) ($wallet->available_balance ?? 0);
        $pending = (float) ($wallet->pending_balance ?? 0);

        // Calculate total paid commissions (lifetime earnings)
        $paidCommissions = $agent->payouts()
            ->where('status', 'paid')
            ->sum('amount');

        // Calculate potential earnings from active applications
        $potential = $agent->applications()
            ->whereIn('status', ['pending', 'under_review', 'additional_documents_required'])
            ->with('program')
            ->get()
            ->sum(fn ($application) => $application->program?->agent_commission ?? 0);

        return [
            Stat::make('Available Balance', Number::currency($available, 'USD'))
                ->description('Ready for withdrawal request')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Pending Payouts', Number::currency($pending, 'USD'))
                ->description('Awaiting admin approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Paid Commissions', Number::currency($paidCommissions, 'USD'))
                ->description('Total lifetime earnings')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('primary'),

            Stat::make('Potential Earnings', Number::currency($potential, 'USD'))
                ->description('From active applications')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('info'),
        ];
    }
}
