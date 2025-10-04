<?php

namespace App\Services;

use App\Models\University;
use Barryvdh\DomPDF\Facade\Pdf;

class UniversityPdfExportService
{
    /**
     * Generate a PDF report for a university.
     */
    public function generatePdf(University $university): \Barryvdh\DomPDF\PDF
    {
        // Load relationships
        $university->load(['programs.degree', 'programs.applications.student', 'programs.applications.agent']);

        // Calculate statistics
        $stats = $this->calculateStatistics($university);

        $html = view('pdf.university-report', [
            'university' => $university,
            'stats' => $stats,
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial',
            ]);
    }

    /**
     * Calculate statistics for the university.
     */
    private function calculateStatistics(University $university): array
    {
        $programs = $university->programs;
        $applications = $programs->pluck('applications')->flatten();

        return [
            'total_programs' => $programs->count(),
            'active_programs' => $programs->where('is_active', true)->count(),
            'total_applications' => $applications->count(),
            'approved_applications' => $applications->where('status', 'approved')->count(),
            'pending_applications' => $applications->whereIn('status', ['pending', 'submitted', 'under_review'])->count(),
            'success_rate' => $applications->count() > 0
                ? round(($applications->where('status', 'approved')->count() / $applications->count()) * 100, 1)
                : 0,
            'average_tuition' => $programs->where('is_active', true)->avg('tuition_fee'),
            'degree_types' => $programs->pluck('degree.name')->unique()->count(),
        ];
    }
}
