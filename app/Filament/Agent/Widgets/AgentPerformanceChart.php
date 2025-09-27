<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Application;
use App\Models\Commission;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AgentPerformanceChart extends ChartWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'My Performance Over Time';
    }

    protected function getData(): array
    {
        $agentId = auth()->id();
        
        // Get last 6 months of data
        $months = [];
        $applications = [];
        $approvals = [];
        $commissions = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            $months[] = $monthName;
            
            // Applications submitted this month
            $monthApplications = Application::where('agent_id', $agentId)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $applications[] = $monthApplications;
            
            // Approvals this month
            $monthApprovals = Application::where('agent_id', $agentId)
                ->where('status', 'approved')
                ->whereMonth('decision_at', $date->month)
                ->whereYear('decision_at', $date->year)
                ->count();
            $approvals[] = $monthApprovals;
            
            // Commission earned this month
            $monthCommissions = Commission::where('agent_id', $agentId)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
            $commissions[] = $monthCommissions;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Applications Submitted',
                    'data' => $applications,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Approvals',
                    'data' => $approvals,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Commission ($)',
                    'data' => $commissions,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Applications',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Commission ($)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}
