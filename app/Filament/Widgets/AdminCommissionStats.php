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
        // Total agent commissions earned
        $totalAgentCommissions = Commission::sum('amount');

        // Total system commissions (from approved applications)
        $totalSystemCommissions = \App\Models\Application::where('status', 'approved')
            ->join('programs', 'applications.program_id', '=', 'programs.id')
            ->sum('programs.system_commission');

        // Commissions this month
        $thisMonthCommissions = Commission::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // System commissions this month
        $thisMonthSystemCommissions = \App\Models\Application::where('status', 'approved')
            ->whereMonth('applications.created_at', now()->month)
            ->whereYear('applications.created_at', now()->year)
            ->join('programs', 'applications.program_id', '=', 'programs.id')
            ->sum('programs.system_commission');

        // Pending payments
        $pendingPayments = Payout::where('status', 'pending')
            ->sum('amount');

        // Active agents count
        $activeAgents = User::whereIn('role', ['agent_owner', 'agent_staff'])
            ->where('is_active', true)
            ->count();

        return [
            Stat::make('Total Agent Commissions', '$'.number_format($totalAgentCommissions, 2))
                ->description('All time agent earnings')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total System Commission', '$'.number_format($totalSystemCommissions, 2))
                ->description('All time system earnings')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            Stat::make('Agent Commissions (This Month)', '$'.number_format($thisMonthCommissions, 2))
                ->description('Current month agent earnings')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('System Commission (This Month)', '$'.number_format($thisMonthSystemCommissions, 2))
                ->description('Current month system earnings')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Pending Payments', '$'.number_format($pendingPayments, 2))
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
