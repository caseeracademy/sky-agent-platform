<?php

namespace App\Services;

use App\Models\Program;
use App\Models\University;
use Barryvdh\DomPDF\Facade\Pdf;

class ProgramPdfExportService
{
    /**
     * Generate a PDF report for a single program.
     */
    public function generateProgramPdf(Program $program): \Barryvdh\DomPDF\PDF
    {
        // Load relationships
        $program->load(['university', 'degree', 'applications.student', 'applications.agent']);

        // Calculate statistics
        $stats = $this->calculateProgramStatistics($program);

        $html = view('pdf.program-report', [
            'program' => $program,
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
     * Generate a PDF report for all programs in a university.
     */
    public function generateUniversityProgramsPdf(University $university): \Barryvdh\DomPDF\PDF
    {
        // Load relationships
        $university->load(['programs.degree', 'programs.applications.student', 'programs.applications.agent']);

        // Calculate statistics
        $stats = $this->calculateUniversityProgramsStatistics($university);

        $html = view('pdf.university-programs-report', [
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
     * Generate a PDF report for all programs (system-wide).
     */
    public function generateAllProgramsPdf(): \Barryvdh\DomPDF\PDF
    {
        // Load all programs with relationships
        $programs = Program::with(['university', 'degree', 'applications.student', 'applications.agent'])
            ->orderBy('university_id')
            ->orderBy('name')
            ->get();

        // Group by university
        $universities = $programs->groupBy('university_id');
        $universitiesWithData = collect();

        foreach ($universities as $universityId => $universityPrograms) {
            $university = $universityPrograms->first()->university;
            $university->programs = $universityPrograms;
            $universitiesWithData->push($university);
        }

        // Calculate statistics
        $stats = $this->calculateAllProgramsStatistics($programs);

        $html = view('pdf.all-programs-report', [
            'universities' => $universitiesWithData,
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
     * Calculate statistics for a single program.
     */
    private function calculateProgramStatistics(Program $program): array
    {
        $applications = $program->applications;

        return [
            'total_applications' => $applications->count(),
            'approved_applications' => $applications->where('status', 'approved')->count(),
            'pending_applications' => $applications->whereIn('status', ['pending', 'submitted', 'under_review'])->count(),
            'rejected_applications' => $applications->where('status', 'rejected')->count(),
            'success_rate' => $applications->count() > 0
                ? round(($applications->where('status', 'approved')->count() / $applications->count()) * 100, 1)
                : 0,
            'total_commission_earned' => $applications->where('status', 'approved')->sum('commission_amount'),
            'average_application_value' => $applications->count() > 0 ? $applications->avg('commission_amount') : 0,
        ];
    }

    /**
     * Calculate statistics for all programs in a university.
     */
    private function calculateUniversityProgramsStatistics(University $university): array
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
            'total_tuition_value' => $programs->where('is_active', true)->sum('tuition_fee'),
            'total_commission_value' => $programs->sum('agent_commission'),
            'degree_types' => $programs->pluck('degree.name')->unique()->count(),
        ];
    }

    /**
     * Calculate statistics for all programs system-wide.
     */
    private function calculateAllProgramsStatistics($programs): array
    {
        $applications = $programs->pluck('applications')->flatten();
        $universities = $programs->pluck('university')->unique();

        return [
            'total_programs' => $programs->count(),
            'active_programs' => $programs->where('is_active', true)->count(),
            'total_universities' => $universities->count(),
            'total_applications' => $applications->count(),
            'approved_applications' => $applications->where('status', 'approved')->count(),
            'pending_applications' => $applications->whereIn('status', ['pending', 'submitted', 'under_review'])->count(),
            'success_rate' => $applications->count() > 0
                ? round(($applications->where('status', 'approved')->count() / $applications->count()) * 100, 1)
                : 0,
            'total_tuition_value' => $programs->where('is_active', true)->sum('tuition_fee'),
            'total_commission_value' => $programs->sum('agent_commission'),
            'degree_types' => $programs->pluck('degree.name')->unique()->count(),
        ];
    }
}
