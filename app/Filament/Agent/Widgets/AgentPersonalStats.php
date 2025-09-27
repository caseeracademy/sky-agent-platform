<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Application;
use App\Models\Commission;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentPersonalStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $agentId = auth()->id();
        
        // Total students
        $totalStudents = Student::where('agent_id', $agentId)->count();
        
        // Total applications
        $totalApplications = Application::where('agent_id', $agentId)->count();
        
        // Applications this month
        $thisMonthApplications = Application::where('agent_id', $agentId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Success rate (approved applications)
        $approvedApplications = Application::where('agent_id', $agentId)
            ->where('status', 'approved')
            ->count();
        
        $successRate = $totalApplications > 0 ? ($approvedApplications / $totalApplications) * 100 : 0;
        
        // Total earnings
        $totalEarnings = Commission::where('agent_id', $agentId)->sum('amount');

        return [
            Stat::make('My Students', $totalStudents)
                ->description('Total students managed')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
                
            Stat::make('Applications', $totalApplications)
                ->description('Total submitted')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
                
            Stat::make('Success Rate', round($successRate) . '%')
                ->description('Applications approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 70 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger')),
                
            Stat::make('Total Earnings', '$' . number_format($totalEarnings, 2))
                ->description('Commission earned')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
