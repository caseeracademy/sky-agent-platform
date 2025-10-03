<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Degree;
use App\Models\ScholarshipCommission;
use App\Models\ScholarshipPoint;
use App\Models\University;
use Illuminate\Support\Facades\Log;

class SimpleScholarshipService
{
    /**
     * Process an approved application and create scholarship point.
     */
    public function processApprovedApplication(Application $application): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'point_created' => false,
            'scholarship_earned' => false,
            'debug' => [],
        ];

        try {
            // Ensure application is scholarship type and approved
            if ($application->commission_type !== 'scholarship' || $application->status !== 'approved') {
                $result['message'] = 'Application is not scholarship type or not approved';

                return $result;
            }

            // Load relationships
            $application->load(['student', 'program.university', 'program.degree']);
            $student = $application->student;
            $program = $application->program;
            $university = $program->university;
            $degree = $program->degree;

            if (! $student || ! $program || ! $university || ! $degree) {
                $result['message'] = 'Missing required relationships';

                return $result;
            }

            // Check if point already exists
            $existingPoint = ScholarshipPoint::where('application_id', $application->id)->first();
            if ($existingPoint) {
                $result['message'] = 'Scholarship point already exists for this application';
                $result['debug'][] = "Existing point ID: {$existingPoint->id}";

                return $result;
            }

            // Get university requirements
            $requirements = $university->getScholarshipRequirementForDegree($degree->name);
            $agentThreshold = $this->getAgentThreshold($requirements);

            if (! $agentThreshold) {
                $result['message'] = 'No valid scholarship requirements for this degree';
                $result['debug'][] = 'Requirements: '.json_encode($requirements);

                return $result;
            }

            $result['debug'][] = "Threshold for {$degree->name}: {$agentThreshold} students";

            // Create scholarship point
            $point = ScholarshipPoint::create([
                'agent_id' => $student->agent_id,
                'university_id' => $university->id,
                'degree_id' => $degree->id,
                'program_id' => $program->id,
                'application_id' => $application->id,
                'student_id' => $student->id,
                'application_year' => ScholarshipPoint::getCurrentApplicationYear(),
                'status' => 'active',
                'earned_at' => now(),
            ]);

            $result['point_created'] = true;
            $result['debug'][] = "Created scholarship point ID: {$point->id}";

            // Check if agent now qualifies for a scholarship
            $totalPoints = ScholarshipPoint::where('agent_id', $student->agent_id)
                ->where('university_id', $university->id)
                ->where('degree_id', $degree->id)
                ->where('status', 'active')
                ->count();

            $result['debug'][] = "Total active points for agent: {$totalPoints}/{$agentThreshold}";

            if ($totalPoints >= $agentThreshold) {
                // Agent qualifies for a scholarship
                $commission = ScholarshipCommission::create([
                    'agent_id' => $student->agent_id,
                    'university_id' => $university->id,
                    'degree_id' => $degree->id,
                    'qualifying_points_count' => $agentThreshold,
                    'status' => 'earned',
                    'earned_at' => now(),
                    'application_year' => ScholarshipPoint::getCurrentApplicationYear(),
                ]);

                // Mark points as redeemed
                ScholarshipPoint::where('agent_id', $student->agent_id)
                    ->where('university_id', $university->id)
                    ->where('degree_id', $degree->id)
                    ->where('status', 'active')
                    ->limit($agentThreshold)
                    ->update(['status' => 'redeemed']);

                $result['scholarship_earned'] = true;
                $result['debug'][] = "Created scholarship commission: {$commission->commission_number}";
            }

            $result['success'] = true;
            $result['message'] = $result['scholarship_earned']
                ? 'Scholarship point created and scholarship earned!'
                : 'Scholarship point created, progress updated';

        } catch (\Exception $e) {
            $result['message'] = 'Error processing application: '.$e->getMessage();
            $result['debug'][] = $e->getTraceAsString();
        }

        return $result;
    }

    /**
     * Get agent progress for dashboard display.
     */
    public function getAgentProgress(int $agentId): array
    {
        $progress = [];

        try {
            // First, fix any missing scholarships
            $this->fixMissingScholarships($agentId);

            // Get all active points grouped by university + degree
            $points = ScholarshipPoint::where('agent_id', $agentId)
                ->where('status', 'active')
                ->with(['university', 'degree'])
                ->get()
                ->groupBy(function ($point) {
                    return $point->university_id.'-'.$point->degree_id;
                });

            foreach ($points as $key => $groupedPoints) {
                $firstPoint = $groupedPoints->first();
                $university = $firstPoint->university;
                $degree = $firstPoint->degree;

                // Get requirements
                $requirements = $university->getScholarshipRequirementForDegree($degree->name);
                $threshold = $this->getAgentThreshold($requirements);

                // Count current points
                $currentPoints = $groupedPoints->count();

                // Count earned scholarships
                $earnedScholarships = ScholarshipCommission::where('agent_id', $agentId)
                    ->where('university_id', $university->id)
                    ->where('degree_id', $degree->id)
                    ->where('status', 'earned')
                    ->count();

                // Calculate progress (prevent division by zero)
                $progressPercentage = $threshold > 0 ? min(100, round(($currentPoints / $threshold) * 100, 1)) : 0;
                $isComplete = $threshold > 0 && $currentPoints >= $threshold;

                $progress[] = [
                    'university_id' => $university->id,
                    'university_name' => $university->name,
                    'degree_id' => $degree->id,
                    'degree_name' => $degree->name,
                    'current_points' => $currentPoints,
                    'threshold' => $threshold,
                    'progress_percentage' => $progressPercentage,
                    'progress_text' => "{$currentPoints}/{$threshold}",
                    'is_complete' => $isComplete,
                    'earned_scholarships' => $earnedScholarships,
                    'status_text' => $isComplete
                        ? 'Ready to earn scholarship!'
                        : "{$currentPoints} out of {$threshold} students approved",
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error getting agent progress', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
        }

        return $progress;
    }

    /**
     * Get total earned scholarships for an agent.
     */
    public function getTotalEarnedScholarships(int $agentId): int
    {
        return ScholarshipCommission::where('agent_id', $agentId)
            ->where('status', 'earned')
            ->count();
    }

    /**
     * Get total active points for an agent.
     */
    public function getTotalActivePoints(int $agentId): int
    {
        return ScholarshipPoint::where('agent_id', $agentId)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Fix missing scholarships for an agent.
     */
    public function fixMissingScholarships(int $agentId): array
    {
        $fixed = [];

        try {
            // Get all university-degree combinations for this agent
            $combinations = ScholarshipPoint::where('agent_id', $agentId)
                ->select('university_id', 'degree_id')
                ->distinct()
                ->get();

            foreach ($combinations as $combo) {
                $university = University::find($combo->university_id);
                $degree = Degree::find($combo->degree_id);

                if (! $university || ! $degree) {
                    continue;
                }

                // Get requirements
                $requirements = $university->getScholarshipRequirementForDegree($degree->name);
                $threshold = $this->getAgentThreshold($requirements);

                if (! $threshold) {
                    continue;
                }

                // Count total points (active + redeemed)
                $totalPoints = ScholarshipPoint::where('agent_id', $agentId)
                    ->where('university_id', $combo->university_id)
                    ->where('degree_id', $combo->degree_id)
                    ->count();

                // Count existing scholarships (both earned and used)
                // Used scholarships were already given, so they count toward total
                $existingScholarships = ScholarshipCommission::where('agent_id', $agentId)
                    ->where('university_id', $combo->university_id)
                    ->where('degree_id', $combo->degree_id)
                    ->whereIn('status', ['earned', 'used'])
                    ->count();

                // Calculate missing scholarships (prevent division by zero)
                $shouldHave = $threshold > 0 ? floor($totalPoints / $threshold) : 0;
                $missing = $shouldHave - $existingScholarships;

                if ($missing > 0) {
                    // Create missing scholarships
                    for ($i = 0; $i < $missing; $i++) {
                        $commission = ScholarshipCommission::create([
                            'agent_id' => $agentId,
                            'university_id' => $combo->university_id,
                            'degree_id' => $combo->degree_id,
                            'qualifying_points_count' => $threshold,
                            'status' => 'earned',
                            'earned_at' => now(),
                            'application_year' => ScholarshipPoint::getCurrentApplicationYear(),
                            'notes' => 'Auto-created by repair system',
                        ]);

                        $fixed[] = [
                            'commission_number' => $commission->commission_number,
                            'university' => $university->name,
                            'degree' => $degree->name,
                        ];

                        Log::info('Fixed missing scholarship', [
                            'agent_id' => $agentId,
                            'commission_number' => $commission->commission_number,
                            'university' => $university->name,
                            'degree' => $degree->name,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error fixing missing scholarships', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
        }

        return $fixed;
    }

    /**
     * Get scholarship progress data for the scholarships page.
     */
    public function getScholarshipPageData(int $agentId): array
    {
        $data = [
            'completed_scholarships' => [],
            'progress_scholarships' => [],
        ];

        try {
            // Get completed scholarships
            $data['completed_scholarships'] = ScholarshipCommission::where('agent_id', $agentId)
                ->with(['university', 'degree'])
                ->orderBy('earned_at', 'desc')
                ->get()
                ->toArray();

            // Get progress scholarships using a completely different approach to avoid caching issues
            $activePoints = ScholarshipPoint::where('agent_id', $agentId)
                ->where('status', 'active')
                ->get();

            $groupedPoints = $activePoints->groupBy(function ($point) {
                return $point->university_id.'-'.$point->degree_id;
            });

            foreach ($groupedPoints as $key => $pointsGroup) {
                $firstPoint = $pointsGroup->first();

                // Always use fresh database queries to avoid any caching
                $university = University::where('id', $firstPoint->university_id)->first();
                $degree = \App\Models\Degree::where('id', $firstPoint->degree_id)->first();

                if (! $university || ! $degree) {
                    continue;
                }

                // Get requirements
                $requirements = $university->getScholarshipRequirementForDegree($degree->name);
                $threshold = $this->getAgentThreshold($requirements);

                if (! $threshold) {
                    continue;
                }

                // Count active points for this combination (use the grouped points)
                $activePointsCount = $pointsGroup->count();

                if ($activePointsCount > 0) {
                    $progressPercentage = $threshold > 0 ? min(100, round(($activePointsCount / $threshold) * 100)) : 0;

                    $data['progress_scholarships'][] = [
                        'id' => 'progress_'.$firstPoint->university_id.'_'.$firstPoint->degree_id,
                        'university' => $university,
                        'degree' => $degree,
                        'current_points' => $activePointsCount,
                        'threshold' => $threshold,
                        'progress_percentage' => $progressPercentage,
                        'status' => 'in_progress',
                        'progress_text' => "{$activePointsCount}/{$threshold}",
                        'status_text' => $activePointsCount >= $threshold
                            ? 'Ready to earn scholarship!'
                            : "{$activePointsCount} out of {$threshold} students approved",
                        'color' => $activePointsCount >= $threshold ? 'warning' : 'info',
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error getting scholarship page data', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
            ]);
        }

        return $data;
    }

    /**
     * Get agent threshold from requirements array.
     */
    private function getAgentThreshold(?array $requirements): ?int
    {
        if (! $requirements) {
            return null;
        }

        $threshold = $requirements['agent_threshold'] ?? $requirements['min_students'] ?? null;

        if ($threshold && is_numeric($threshold)) {
            return max(1, (int) $threshold);
        }

        return null;
    }
}
