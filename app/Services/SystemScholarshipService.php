<?php

namespace App\Services;

use App\Models\Degree;
use App\Models\ScholarshipPoint;
use App\Models\University;
use Illuminate\Support\Facades\Log;

class SystemScholarshipService
{
    /**
     * Get system scholarship data for all university+degree combinations.
     */
    public function getSystemScholarshipData(): array
    {
        $data = [];

        try {
            // Get ALL scholarship points (both active and redeemed) grouped by university and degree
            // Both contribute to system total - system benefits from all approved scholarship applications
            $groupedPoints = ScholarshipPoint::whereIn('status', ['active', 'redeemed'])
                ->get()
                ->groupBy(function ($point) {
                    return $point->university_id.'-'.$point->degree_id;
                });

            foreach ($groupedPoints as $key => $pointsGroup) {
                $firstPoint = $pointsGroup->first();

                // Get fresh university and degree data
                $university = University::find($firstPoint->university_id);
                $degree = Degree::find($firstPoint->degree_id);

                if (! $university || ! $degree) {
                    continue;
                }

                // Get scholarship requirements for this university+degree
                $requirements = $university->getScholarshipRequirementForDegree($degree->name);
                if (! $requirements) {
                    continue;
                }

                $universityThreshold = $requirements['system_threshold'] ?? 4;
                $agentThreshold = $requirements['agent_threshold'] ?? 5;

                // Calculate system scholarship logic
                $totalStudents = $pointsGroup->count();
                $systemScholarshipData = $this->calculateSystemScholarship(
                    $totalStudents,
                    $universityThreshold,
                    $agentThreshold,
                    $university,
                    $degree
                );

                if ($systemScholarshipData) {
                    $data[] = $systemScholarshipData;
                }
            }

        } catch (\Exception $e) {
            Log::error('Error getting system scholarship data', [
                'error' => $e->getMessage(),
            ]);
        }

        return $data;
    }

    /**
     * Calculate system scholarship for a specific university+degree combination.
     */
    private function calculateSystemScholarship(
        int $totalStudents,
        int $universityThreshold,
        int $agentThreshold,
        University $university,
        Degree $degree
    ): ?array {
        try {
            // Calculate how many students needed for system to earn 1 scholarship
            // Formula: agents_needed * agent_threshold = students_per_system_scholarship
            $gcd = $this->gcd($universityThreshold, $agentThreshold);
            $agentsNeeded = $universityThreshold / $gcd;
            $studentsPerSystemScholarship = $agentsNeeded * $agentThreshold;

            // Calculate system progress
            $systemScholarshipsEarned = intval($totalStudents / $studentsPerSystemScholarship);
            $currentCycleProgress = $totalStudents % $studentsPerSystemScholarship;
            $progressPercentage = $studentsPerSystemScholarship > 0
                ? round(($currentCycleProgress / $studentsPerSystemScholarship) * 100)
                : 0;

            // Calculate next milestone
            $studentsNeededForNext = $studentsPerSystemScholarship - $currentCycleProgress;

            // Get contributing agents data
            $contributingAgents = $this->getContributingAgents($university->id, $degree->id);

            return [
                'id' => 'system_'.$university->id.'_'.$degree->id,
                'university' => $university,
                'degree' => $degree,
                'total_students' => $totalStudents,
                'university_threshold' => $universityThreshold,
                'agent_threshold' => $agentThreshold,
                'students_per_system_scholarship' => $studentsPerSystemScholarship,
                'system_scholarships_earned' => $systemScholarshipsEarned,
                'current_cycle_progress' => $currentCycleProgress,
                'progress_percentage' => $progressPercentage,
                'students_needed_for_next' => $studentsNeededForNext,
                'contributing_agents' => $contributingAgents,
                'status' => $systemScholarshipsEarned > 0 ? 'earned' : 'in_progress',
                'progress_text' => "{$currentCycleProgress}/{$studentsPerSystemScholarship}",
                'status_text' => $systemScholarshipsEarned > 0
                    ? "Earned {$systemScholarshipsEarned} scholarship".($systemScholarshipsEarned > 1 ? 's' : '')
                    : "Need {$studentsNeededForNext} more students",
                'color' => $systemScholarshipsEarned > 0 ? 'success' : 'info',
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating system scholarship', [
                'university_id' => $university->id,
                'degree_id' => $degree->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get contributing agents for a university+degree combination.
     */
    private function getContributingAgents(int $universityId, int $degreeId): array
    {
        try {
            // Get ALL scholarship points (both active and redeemed)
            // All approved scholarship applications contribute to system total
            $agents = ScholarshipPoint::where('university_id', $universityId)
                ->where('degree_id', $degreeId)
                ->whereIn('status', ['active', 'redeemed'])
                ->with(['agent'])
                ->get()
                ->groupBy('agent_id')
                ->flatMap(function ($points, $agentId) use ($universityId, $degreeId) {
                    $agent = $points->first()->agent;

                    // Get agent threshold for this university+degree
                    $university = University::find($universityId);
                    $degree = Degree::find($degreeId);
                    $requirements = $university->getScholarshipRequirementForDegree($degree->name);
                    $agentThreshold = $requirements['agent_threshold'] ?? 5;

                    $totalPoints = $points->count(); // Total contribution to system
                    $activePoints = $points->where('status', 'active')->count();

                    // Calculate scholarships earned
                    $scholarshipsEarned = floor($totalPoints / $agentThreshold);
                    $currentCyclePoints = $totalPoints % $agentThreshold;

                    $agentCycles = [];

                    // Create entries for each completed scholarship
                    for ($i = 1; $i <= $scholarshipsEarned; $i++) {
                        $agentCycles[] = [
                            'agent' => $agent,
                            'cycle_number' => $i,
                            'points_count' => $agentThreshold,
                            'total_points' => $totalPoints,
                            'scholarships_earned' => $scholarshipsEarned,
                            'threshold' => $agentThreshold,
                            'progress_percentage' => 100,
                            'has_completed' => true,
                            'progress_text' => "{$agentThreshold}/{$agentThreshold}",
                            'status' => 'completed',
                            'is_current_cycle' => false,
                        ];
                    }

                    // Add current/next cycle if there are active points or partial progress
                    if ($activePoints > 0 || $currentCyclePoints > 0) {
                        $displayPoints = $activePoints > 0 ? $activePoints : $currentCyclePoints;
                        $progressPercentage = $agentThreshold > 0 ? round(($displayPoints / $agentThreshold) * 100) : 0;

                        $agentCycles[] = [
                            'agent' => $agent,
                            'cycle_number' => $scholarshipsEarned + 1,
                            'points_count' => $displayPoints,
                            'total_points' => $totalPoints,
                            'scholarships_earned' => $scholarshipsEarned,
                            'threshold' => $agentThreshold,
                            'progress_percentage' => $progressPercentage,
                            'has_completed' => false,
                            'progress_text' => "{$displayPoints}/{$agentThreshold}",
                            'status' => 'in_progress',
                            'is_current_cycle' => true,
                        ];
                    }

                    return $agentCycles;
                })
                ->values()
                ->toArray();

            return $agents;

        } catch (\Exception $e) {
            Log::error('Error getting contributing agents', [
                'university_id' => $universityId,
                'degree_id' => $degreeId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Calculate Greatest Common Divisor.
     */
    private function gcd(int $a, int $b): int
    {
        while ($b != 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }

        return $a;
    }

    /**
     * Get system scholarship details by ID.
     */
    public function getSystemScholarshipById(string $id): ?array
    {
        $allData = $this->getSystemScholarshipData();

        foreach ($allData as $scholarship) {
            if ($scholarship['id'] === $id) {
                return $scholarship;
            }
        }

        return null;
    }
}
