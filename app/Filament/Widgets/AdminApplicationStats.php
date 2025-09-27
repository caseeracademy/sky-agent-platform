<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class AdminApplicationStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Count applications by status
        $pendingCount = Application::where('status', 'pending')->count();
        $inReviewCount = Application::where('status', 'under_review')->count();
        $awaitingDocsCount = Application::where('status', 'additional_documents_required')->count();
        
        // Count approved applications this month
        $approvedThisMonth = Application::where('status', 'approved')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        return [
            Stat::make('Pending Applications', Number::format($pendingCount))
                ->description('New applications waiting for review')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('In Review', Number::format($inReviewCount))
                ->description('Applications currently being processed')
                ->descriptionIcon('heroicon-o-eye')
                ->color('info'),

            Stat::make('Awaiting Documents', Number::format($awaitingDocsCount))
                ->description('Sent back to agents for more info')
                ->descriptionIcon('heroicon-o-document-plus')
                ->color('danger'),

            Stat::make('Approved This Month', Number::format($approvedThisMonth))
                ->description('Successfully approved in ' . now()->format('F'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
