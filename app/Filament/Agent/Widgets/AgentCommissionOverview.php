<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Commission;
use App\Models\Payout;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentCommissionOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $agentId = auth()->id();
        
        // Total earnings
        $totalEarnings = Commission::where('agent_id', $agentId)->sum('amount');
        
        // This month earnings
        $thisMonthEarnings = Commission::where('agent_id', $agentId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        // Pending payouts (from payouts table, not commissions)
        $pendingCommissions = Payout::where('agent_id', $agentId)
            ->where('status', 'pending')
            ->sum('amount');
        
        // Paid payouts (from payouts table, not commissions)
        $paidCommissions = Payout::where('agent_id', $agentId)
            ->where('status', 'paid')
            ->sum('amount');

        return [
            Stat::make('Total Earnings', '$' . number_format($totalEarnings, 2))
                ->description('All time commission')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('This Month', '$' . number_format($thisMonthEarnings, 2))
                ->description('Current month earnings')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
                
            Stat::make('Pending Payment', '$' . number_format($pendingCommissions, 2))
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Paid Out', '$' . number_format($paidCommissions, 2))
                ->description('Successfully paid')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
