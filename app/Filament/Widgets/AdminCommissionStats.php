<?php

namespace App\Filament\Widgets;

use App\Models\Commission;
use App\Models\Payout;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminCommissionStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Total commissions earned
        $totalCommissions = Commission::sum('amount');
        
        // Commissions this month
        $thisMonthCommissions = Commission::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        // Pending payments
        $pendingPayments = Payout::where('status', 'pending')
            ->sum('amount');
        
        // Active agents count
        $activeAgents = User::whereIn('role', ['agent_owner', 'agent_staff'])
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Total Commissions', '$' . number_format($totalCommissions, 2))
                ->description('All time earnings')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('This Month', '$' . number_format($thisMonthCommissions, 2))
                ->description('Current month earnings')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
                
            Stat::make('Pending Payments', '$' . number_format($pendingPayments, 2))
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingPayments > 1000 ? 'warning' : 'info'),
                
            Stat::make('Active Agents', $activeAgents)
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
