<?php

namespace App\Filament\Widgets;

use App\Models\Commission;
use Filament\Widgets\ChartWidget;

class FinancialAnalytics extends ChartWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Financial Analytics - Commission Trends';
    }

    protected function getData(): array
    {
        // Get last 12 months of commission data
        $months = [];
        $totalCommissions = [];
        $paidCommissions = [];
        $pendingCommissions = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            $months[] = $monthName;
            
            // Total commissions created this month
            $monthTotal = Commission::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
            $totalCommissions[] = $monthTotal;
            
            // Paid commissions this month
            $monthPaid = Commission::where('status', 'paid')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
            $paidCommissions[] = $monthPaid;
            
            // Pending commissions this month
            $monthPending = Commission::where('status', 'pending')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
            $pendingCommissions[] = $monthPending;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Commissions Generated',
                    'data' => $totalCommissions,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Paid Commissions',
                    'data' => $paidCommissions,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Pending Commissions',
                    'data' => $pendingCommissions,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
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
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toLocaleString(); }',
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Commission Amount ($)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
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
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": $" + context.raw.toLocaleString();
                        }',
                    ],
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
