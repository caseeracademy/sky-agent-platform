<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdminApplicationsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Total applications
        $totalApplications = Application::count();
        
        // Applications this month
        $thisMonthApplications = Application::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Pending review applications
        $pendingReview = Application::whereIn('status', ['submitted', 'under_review', 'additional_documents_required'])
            ->count();
        
        // Applications approved this month
        $approvedThisMonth = Application::where('status', 'approved')
            ->whereMonth('decision_at', now()->month)
            ->whereYear('decision_at', now()->year)
            ->count();
        
        // Calculate trends
        $lastMonthApplications = Application::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        $applicationTrend = $lastMonthApplications > 0 
            ? (($thisMonthApplications - $lastMonthApplications) / $lastMonthApplications) * 100 
            : 0;

        return [
            Stat::make('Total Applications', $totalApplications)
                ->description('All time applications')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
                
            Stat::make('This Month', $thisMonthApplications)
                ->description(sprintf('%s%d%% from last month', $applicationTrend >= 0 ? '+' : '', round($applicationTrend)))
                ->descriptionIcon($applicationTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($applicationTrend >= 0 ? 'success' : 'danger'),
                
            Stat::make('Pending Review', $pendingReview)
                ->description('Awaiting admin action')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingReview > 10 ? 'warning' : 'info'),
                
            Stat::make('Approved This Month', $approvedThisMonth)
                ->description('Successfully processed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
