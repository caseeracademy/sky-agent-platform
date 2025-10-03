<?php

namespace App\Filament\Agent\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ScholarshipProgress extends Widget
{
    protected string $view = 'filament.agent.widgets.scholarship-progress';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getScholarshipProgress(): Collection
    {
        $agentId = auth()->id();

        // Use the simple service for reliable progress data
        $simpleService = app(\App\Services\SimpleScholarshipService::class);
        $progressData = $simpleService->getAgentProgress($agentId);

        // Add extra fields needed by the widget
        foreach ($progressData as &$item) {
            // Calculate days until expiry for active points
            $activePoints = \App\Models\ScholarshipPoint::where('agent_id', $agentId)
                ->where('university_id', $item['university_id'])
                ->where('degree_id', $item['degree_id'])
                ->where('status', 'active')
                ->get();

            $nextExpiry = $activePoints->min('expires_at');
            $item['days_until_expiry'] = $nextExpiry ? max(0, now()->diffInDays($nextExpiry, false)) : 0;
            $item['is_available'] = $item['earned_scholarships'] > 0;
            $item['available_scholarships'] = $item['earned_scholarships']; // Alias for compatibility
        }

        return collect($progressData)->sortByDesc(function ($item) {
            return [$item['earned_scholarships'], $item['progress_percentage']];
        })->values();
    }

    protected function getProgressStatus(int $activePoints, int $threshold, int $availableCount): string
    {
        if ($availableCount > 0) {
            return $availableCount === 1 ? '1 Scholarship Ready!' : "{$availableCount} Scholarships Ready!";
        }

        if ($activePoints >= $threshold) {
            return 'Ready to Earn Scholarship!';
        }

        if ($activePoints > 0) {
            return "{$activePoints} out of {$threshold} students approved";
        }

        return 'Start applying students';
    }

    protected function getProgressColor(int $activePoints, int $threshold, int $availableCount): string
    {
        if ($availableCount > 0) {
            return 'success'; // Green for available scholarships
        }

        if ($activePoints >= $threshold) {
            return 'warning'; // Orange for ready to earn
        }

        if ($activePoints > 0) {
            return 'info'; // Blue for in progress
        }

        return 'gray'; // Gray for no progress
    }

    public function getTotalAvailableScholarships(): int
    {
        // Count earned scholarships for this agent
        return \App\Models\ScholarshipCommission::where('agent_id', auth()->id())
            ->where('status', 'earned')
            ->count();
    }

    public function getTotalActivePoints(): int
    {
        // Count active scholarship points for this agent
        return \App\Models\ScholarshipPoint::where('agent_id', auth()->id())
            ->where('status', 'active')
            ->count();
    }
}
