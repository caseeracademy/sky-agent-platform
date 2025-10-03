<?php

namespace App\Filament\Agent\Widgets;

use App\Models\ScholarshipCommission;
use App\Services\ScholarshipPointService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgentScholarshipProgress extends BaseWidget
{
    protected function getStats(): array
    {
        $agent = auth()->user();
        $scholarshipPointService = app(ScholarshipPointService::class);

        // Get scholarship progress summary using new service
        $summary = $scholarshipPointService->getAgentProgressSummary($agent->id);

        // Get earned scholarships count (new system)
        $earnedScholarships = ScholarshipCommission::where('agent_id', $agent->id)
            ->whereIn('status', ['earned'])
            ->count();

        // Get used scholarships count
        $usedScholarships = ScholarshipCommission::where('agent_id', $agent->id)
            ->where('status', 'used')
            ->count();

        // Get closest opportunity
        $closestOpportunity = $summary['closest_to_eligibility'];
        $nextMilestone = $closestOpportunity
            ? "{$closestOpportunity['remaining']} more students needed"
            : 'No active progress';

        return [
            Stat::make('Available Scholarships', $summary['available_scholarships'])
                ->description('Ready to use')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Used Scholarships', $usedScholarships)
                ->description('Previously redeemed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make('Active Progress', $summary['total_opportunities'])
                ->description($summary['eligible_count'].' ready to earn')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Next Milestone', $nextMilestone)
                ->description($closestOpportunity ? $closestOpportunity['university_name'].' '.$closestOpportunity['degree_type'] : 'Keep applying!')
                ->descriptionIcon('heroicon-m-flag')
                ->color('primary'),
        ];
    }

    protected function getColumns(): int
    {
        return 2;
    }
}
