<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use Filament\Widgets\ChartWidget;

class AdminConversionFunnel extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Application Conversion Funnel';
    }

    protected function getData(): array
    {
        // Get conversion funnel data
        $totalApplications = Application::count();
        $submitted = Application::whereIn('status', ['submitted', 'under_review', 'additional_documents_required', 'approved', 'enrolled'])->count();
        $underReview = Application::whereIn('status', ['under_review', 'additional_documents_required', 'approved', 'enrolled'])->count();
        $approved = Application::whereIn('status', ['approved', 'enrolled'])->count();
        $enrolled = Application::where('status', 'enrolled')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Applications',
                    'data' => [$totalApplications, $submitted, $underReview, $approved, $enrolled],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',  // Blue
                        'rgba(16, 185, 129, 0.8)',  // Green
                        'rgba(245, 158, 11, 0.8)',  // Yellow
                        'rgba(34, 197, 94, 0.8)',   // Success Green
                        'rgba(168, 85, 247, 0.8)',  // Purple
                    ],
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(168, 85, 247, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Total Applications (' . $totalApplications . ')',
                'Submitted (' . $submitted . ')',
                'Under Review (' . $underReview . ')',
                'Approved (' . $approved . ')',
                'Enrolled (' . $enrolled . ')',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const total = ' . Application::count() . ';
                            const percentage = total > 0 ? Math.round((context.raw / total) * 100) : 0;
                            return context.label + ": " + context.raw + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
