<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ScholarshipCommission;
use App\Models\ScholarshipPoint;
use App\Models\University;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScholarshipPointService
{
    /**
     * Create a scholarship point when application is approved.
     */
    public function createPointForApplication(Application $application): ?ScholarshipPoint
    {
        // Load necessary relationships
        $application->load(['student.agent', 'program.university', 'program.degree']);

        $agent = $application->student->agent;
        $university = $application->program->university;
        $degree = $application->program->degree;
        $program = $application->program;
        $student = $application->student;

        if (! $agent || ! $university || ! $degree) {
            Log::warning('Cannot create scholarship point: missing required relationships', [
                'application_id' => $application->id,
                'agent_id' => $agent?->id,
                'university_id' => $university?->id,
                'degree_id' => $degree?->id,
            ]);

            return null;
        }

        // Check if university has scholarship requirements for this degree
        if (! $university->hasAnyScholarshipRequirements()) {
            Log::info('University has no scholarship requirements', [
                'university_id' => $university->id,
                'application_id' => $application->id,
            ]);

            return null;
        }

        $requirements = $university->getScholarshipRequirementForDegree($degree->name);
        if (! $requirements) {
            Log::info('No scholarship requirements for degree type', [
                'university_id' => $university->id,
                'degree_type' => $degree->name,
                'application_id' => $application->id,
            ]);

            return null;
        }

        // Check if point already exists for this application
        $existingPoint = ScholarshipPoint::where('application_id', $application->id)->first();
        if ($existingPoint) {
            Log::warning('Scholarship point already exists for application', [
                'application_id' => $application->id,
                'point_id' => $existingPoint->id,
            ]);

            return $existingPoint;
        }

        // Create the point
        $point = ScholarshipPoint::create([
            'agent_id' => $agent->id,
            'university_id' => $university->id,
            'degree_id' => $degree->id,
            'program_id' => $program->id,
            'application_id' => $application->id,
            'student_id' => $student->id,
        ]);

        Log::info('Scholarship point created', [
            'point_id' => $point->id,
            'application_id' => $application->id,
            'agent_id' => $agent->id,
            'university_id' => $university->id,
            'degree_id' => $degree->id,
        ]);

        // Update admin inventory
        app(AdminScholarshipService::class)->updateInventoryForPoint($point);

        // Check if agent now qualifies for a scholarship
        $this->checkAndCreateScholarshipCommission($agent->id, $university->id, $degree->id);

        return $point;
    }

    /**
     * Check if agent qualifies for scholarship and create commission.
     */
    public function checkAndCreateScholarshipCommission(int $agentId, int $universityId, int $degreeId): ?ScholarshipCommission
    {
        return DB::transaction(function () use ($agentId, $universityId, $degreeId) {
            // Get university and degree
            $university = University::find($universityId);
            $degree = \App\Models\Degree::find($degreeId);

            if (! $university || ! $degree) {
                Log::error('University or degree not found', [
                    'university_id' => $universityId,
                    'degree_id' => $degreeId,
                ]);

                return null;
            }

            $requirements = $university->getScholarshipRequirementForDegree($degree->name);

            if (! $requirements) {
                Log::info('No scholarship requirements for this degree', [
                    'university_id' => $universityId,
                    'degree_name' => $degree->name,
                ]);

                return null;
            }

            $requiredPoints = $requirements['min_students'] ?? 5;

            // Count active points for this combination
            $activePoints = ScholarshipPoint::active()
                ->where('agent_id', $agentId)
                ->where('university_id', $universityId)
                ->where('degree_id', $degreeId)
                ->get();

            $activePointsCount = $activePoints->count();

            if ($activePointsCount < $requiredPoints) {
                Log::info('Agent does not have enough points yet', [
                    'agent_id' => $agentId,
                    'university_id' => $universityId,
                    'degree_id' => $degreeId,
                    'active_points' => $activePointsCount,
                    'required_points' => $requiredPoints,
                ]);

                return null;
            }

            // Calculate how many scholarships the agent should have
            $eligibleScholarships = floor($activePointsCount / $requiredPoints);

            // Check how many scholarships the agent already has
            $existingCommissions = ScholarshipCommission::where('agent_id', $agentId)
                ->where('university_id', $universityId)
                ->where('degree_id', $degreeId)
                ->where('status', 'earned')
                ->count();

            $commissionsToCreate = $eligibleScholarships - $existingCommissions;

            if ($commissionsToCreate <= 0) {
                Log::info('Agent already has enough scholarships', [
                    'agent_id' => $agentId,
                    'university_id' => $universityId,
                    'degree_id' => $degreeId,
                    'eligible_scholarships' => $eligibleScholarships,
                    'existing_commissions' => $existingCommissions,
                ]);

                return null;
            }

            // Create the scholarship commission
            $commission = ScholarshipCommission::create([
                'agent_id' => $agentId,
                'university_id' => $universityId,
                'degree_id' => $degreeId,
                'qualifying_points_count' => $requiredPoints,
                'notes' => "Earned for achieving {$requiredPoints} approved applications",
            ]);

            // Mark the oldest required points as redeemed
            $pointsToRedeem = $activePoints->take($requiredPoints);

            foreach ($pointsToRedeem as $point) {
                $point->markAsRedeemed();
            }

            Log::info('Scholarship commission created', [
                'commission_id' => $commission->id,
                'agent_id' => $agentId,
                'university_id' => $universityId,
                'degree_id' => $degreeId,
                'points_redeemed' => $pointsToRedeem->count(),
            ]);

            // Update admin inventory
            app(AdminScholarshipService::class)->updateInventoryForCommission($commission);

            return $commission;
        });
    }

    /**
     * Get agent's scholarship progress summary.
     */
    public function getAgentProgressSummary(int $agentId): array
    {
        $currentYear = ScholarshipPoint::getCurrentApplicationYear();

        // Get all active points grouped by university+degree
        $activePoints = ScholarshipPoint::with(['university', 'degree'])
            ->active()
            ->where('agent_id', $agentId)
            ->currentCycle()
            ->get()
            ->groupBy(function ($point) {
                return $point->university_id.'-'.$point->degree_id;
            });

        // Get available commissions
        $availableCommissions = ScholarshipCommission::with(['university', 'degree'])
            ->available()
            ->where('agent_id', $agentId)
            ->get();

        $progress = [];
        $totalOpportunities = 0;
        $eligibleCount = 0;
        $closestToEligibility = null;

        foreach ($activePoints as $key => $points) {
            $firstPoint = $points->first();
            $university = $firstPoint->university;
            $degree = $firstPoint->degree;
            $count = $points->count();

            // Get requirements
            $requirements = $university->getScholarshipRequirementForDegree($degree->name);
            $required = $requirements['min_students'] ?? 5;

            $progressData = [
                'university_id' => $university->id,
                'university_name' => $university->name,
                'degree_id' => $degree->id,
                'degree_type' => $degree->name,
                'current_points' => $count,
                'required_points' => $required,
                'progress_percentage' => min(100, ($count / $required) * 100),
                'is_eligible' => $count >= $required,
                'remaining' => max(0, $required - $count),
            ];

            $progress[] = $progressData;
            $totalOpportunities++;

            if ($progressData['is_eligible']) {
                $eligibleCount++;
            } elseif (! $closestToEligibility || $progressData['remaining'] < $closestToEligibility['remaining']) {
                $closestToEligibility = $progressData;
            }
        }

        return [
            'progress' => $progress,
            'available_scholarships' => $availableCommissions->count(),
            'total_opportunities' => $totalOpportunities,
            'eligible_count' => $eligibleCount,
            'closest_to_eligibility' => $closestToEligibility,
            'commissions' => $availableCommissions,
        ];
    }

    /**
     * Get points that will expire soon.
     */
    public function getExpiringPoints(int $agentId, int $daysThreshold = 30): \Illuminate\Database\Eloquent\Collection
    {
        $thresholdDate = now()->addDays($daysThreshold);

        return ScholarshipPoint::with(['university', 'degree', 'program'])
            ->active()
            ->where('agent_id', $agentId)
            ->where('expires_at', '<=', $thresholdDate)
            ->orderBy('expires_at')
            ->get();
    }

    /**
     * Expire points that have passed their expiry date.
     */
    public function expireOldPoints(): int
    {
        $expiredCount = ScholarshipPoint::active()
            ->where('expires_at', '<', now())
            ->update([
                'status' => 'expired',
            ]);

        Log::info('Expired old scholarship points', [
            'count' => $expiredCount,
        ]);

        return $expiredCount;
    }

    /**
     * Reset all points for new application cycle.
     */
    public function resetForNewCycle(): array
    {
        $stats = [
            'expired_points' => 0,
            'expired_commissions' => 0,
        ];

        // Expire all active points from previous cycles
        $stats['expired_points'] = ScholarshipPoint::active()
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        // Expire unused commissions from previous cycles
        $stats['expired_commissions'] = ScholarshipCommission::available()
            ->where('application_year', '<', ScholarshipPoint::getCurrentApplicationYear())
            ->update(['status' => 'expired']);

        Log::info('Reset scholarship system for new cycle', $stats);

        return $stats;
    }
}
