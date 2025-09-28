<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Commission;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AdminAdvancedStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Conversion rate calculation
        $totalApplications = Application::count();
        $approvedApplications = Application::where('status', 'approved')->count();
        $conversionRate = $totalApplications > 0 ? round(($approvedApplications / $totalApplications) * 100, 1) : 0;

        // Average processing time (Database agnostic)
        $avgProcessingTime = Application::whereNotNull('decision_at')
            ->whereNotNull('submitted_at')
            ->get()
            ->map(function ($application) {
                return \Carbon\Carbon::parse($application->decision_at)
                    ->diffInDays(\Carbon\Carbon::parse($application->submitted_at));
            })
            ->average();
        $avgProcessingTime = $avgProcessingTime ? round($avgProcessingTime, 1) : 0;

        // Revenue growth this month vs last month
        $thisMonthRevenue = Commission::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        $lastMonthRevenue = Commission::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');

        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
            : 0;

        // Top performing university this month
        $topUniversity = Application::join('programs', 'applications.program_id', '=', 'programs.id')
            ->join('universities', 'programs.university_id', '=', 'universities.id')
            ->where('applications.status', 'approved')
            ->whereMonth('applications.created_at', now()->month)
            ->whereYear('applications.created_at', now()->year)
            ->groupBy('universities.id', 'universities.name')
            ->selectRaw('universities.name, COUNT(*) as approvals')
            ->orderByDesc('approvals')
            ->first();

        return [
            Stat::make('Conversion Rate', $conversionRate . '%')
                ->description('Applications approved vs total')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($conversionRate >= 70 ? 'success' : ($conversionRate >= 50 ? 'warning' : 'danger'))
                ->chart([65, 68, 72, 69, 75, 73, $conversionRate]),

            Stat::make('Avg Processing Time', $avgProcessingTime . ' days')
                ->description('From submission to decision')
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgProcessingTime <= 7 ? 'success' : ($avgProcessingTime <= 14 ? 'warning' : 'danger')),

            Stat::make('Revenue Growth', ($revenueGrowth >= 0 ? '+' : '') . $revenueGrowth . '%')
                ->description('Month over month commission growth')
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthRevenue ?: 1000,
                    $thisMonthRevenue ?: 1200,
                ]),

            Stat::make('Top University', $topUniversity?->name ?: 'No data')
                ->description(($topUniversity?->approvals ?? 0) . ' approvals this month')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('info'),
        ];
    }
}
