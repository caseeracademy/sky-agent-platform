<?php

namespace App\Services;

use App\Models\AdminScholarshipInventory;
use App\Models\Degree;
use App\Models\ScholarshipCommission;
use App\Models\ScholarshipPoint;
use App\Models\University;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AdminScholarshipService
{
    /**
     * Update admin inventory when a scholarship point is created.
     */
    public function updateInventoryForPoint(ScholarshipPoint $point): void
    {
        $inventory = AdminScholarshipInventory::getOrCreateFor(
            $point->university_id,
            $point->degree_id,
            $point->application_year
        );

        $inventory->recalculate();

        Log::info('Admin inventory updated for scholarship point', [
            'point_id' => $point->id,
            'inventory_id' => $inventory->id,
            'available_scholarships' => $inventory->available_scholarships,
        ]);
    }

    /**
     * Update admin inventory when a scholarship commission is created.
     */
    public function updateInventoryForCommission(ScholarshipCommission $commission): void
    {
        $inventory = AdminScholarshipInventory::getOrCreateFor(
            $commission->university_id,
            $commission->degree_id,
            $commission->application_year
        );

        $inventory->recalculate();

        Log::info('Admin inventory updated for scholarship commission', [
            'commission_id' => $commission->id,
            'inventory_id' => $inventory->id,
            'available_scholarships' => $inventory->available_scholarships,
        ]);
    }

    /**
     * Get admin inventory summary for dashboard.
     */
    public function getInventorySummary(?int $year = null): array
    {
        $year = $year ?? ScholarshipPoint::getCurrentApplicationYear();

        $inventories = AdminScholarshipInventory::with(['university', 'degree'])
            ->where('application_year', $year)
            ->active()
            ->get();

        $summary = [
            'total_universities' => $inventories->groupBy('university_id')->count(),
            'total_degree_types' => $inventories->groupBy('degree_id')->count(),
            'total_combinations' => $inventories->count(),
            'total_scholarships_from_universities' => $inventories->sum('total_scholarships_from_university'),
            'total_scholarships_to_agents' => $inventories->sum('scholarships_given_to_agents'),
            'total_margin_scholarships' => $inventories->sum('margin_scholarships'),
            'total_unclaimed_scholarships' => $inventories->sum('unclaimed_scholarships'),
            'total_available_scholarships' => $inventories->sum('available_scholarships'),
            'inventories' => $inventories,
        ];

        return $summary;
    }

    /**
     * Get inventory breakdown by university.
     */
    public function getInventoryByUniversity(?int $year = null): Collection
    {
        $year = $year ?? ScholarshipPoint::getCurrentApplicationYear();

        return AdminScholarshipInventory::with(['university', 'degree'])
            ->where('application_year', $year)
            ->active()
            ->get()
            ->groupBy('university_id')
            ->map(function ($inventories, $universityId) {
                $university = $inventories->first()->university;

                return [
                    'university' => $university,
                    'total_available_scholarships' => $inventories->sum('available_scholarships'),
                    'total_margin_scholarships' => $inventories->sum('margin_scholarships'),
                    'total_unclaimed_scholarships' => $inventories->sum('unclaimed_scholarships'),
                    'degree_breakdowns' => $inventories->keyBy('degree_id')->map(function ($inventory) {
                        return [
                            'degree' => $inventory->degree,
                            'breakdown' => $inventory->getBreakdownSummary(),
                            'calculation_details' => $inventory->calculation_details,
                        ];
                    }),
                ];
            });
    }

    /**
     * Recalculate all inventories for a year.
     */
    public function recalculateAllInventories(?int $year = null): array
    {
        $year = $year ?? ScholarshipPoint::getCurrentApplicationYear();

        $inventories = AdminScholarshipInventory::where('application_year', $year)->get();
        $updated = 0;

        foreach ($inventories as $inventory) {
            $inventory->recalculate();
            $updated++;
        }

        Log::info('Recalculated all admin inventories', [
            'year' => $year,
            'updated_count' => $updated,
        ]);

        return [
            'year' => $year,
            'updated_count' => $updated,
        ];
    }

    /**
     * Create missing inventories for all university+degree combinations.
     */
    public function createMissingInventories(?int $year = null): array
    {
        $year = $year ?? ScholarshipPoint::getCurrentApplicationYear();
        $created = 0;

        // Get all universities with scholarship requirements
        $universities = University::whereNotNull('scholarship_requirements')->get();

        foreach ($universities as $university) {
            $requirements = $university->scholarship_requirements ?? [];

            foreach ($requirements as $degreeType => $requirement) {
                $degree = Degree::where('name', $degreeType)->first();

                if ($degree) {
                    $inventory = AdminScholarshipInventory::firstOrCreate([
                        'university_id' => $university->id,
                        'degree_id' => $degree->id,
                        'application_year' => $year,
                    ]);

                    if ($inventory->wasRecentlyCreated) {
                        $inventory->recalculate();
                        $created++;
                    }
                }
            }
        }

        Log::info('Created missing admin inventories', [
            'year' => $year,
            'created_count' => $created,
        ]);

        return [
            'year' => $year,
            'created_count' => $created,
        ];
    }

    /**
     * Get top performing combinations by admin profit.
     */
    public function getTopPerformingCombinations(int $limit = 10, ?int $year = null): Collection
    {
        $year = $year ?? ScholarshipPoint::getCurrentApplicationYear();

        return AdminScholarshipInventory::with(['university', 'degree'])
            ->where('application_year', $year)
            ->active()
            ->orderByDesc('available_scholarships')
            ->limit($limit)
            ->get();
    }

    /**
     * Get opportunities (combinations with high potential).
     */
    public function getOpportunities(?int $year = null): Collection
    {
        $year = $year ?? ScholarshipPoint::getCurrentApplicationYear();

        return AdminScholarshipInventory::with(['university', 'degree'])
            ->where('application_year', $year)
            ->active()
            ->where('unclaimed_scholarships', '>', 0)
            ->orderByDesc('unclaimed_scholarships')
            ->get();
    }

    /**
     * Close inventories for ended cycle.
     */
    public function closeInventoriesForCycle(int $year): array
    {
        $closed = AdminScholarshipInventory::where('application_year', $year)
            ->where('status', 'active')
            ->update(['status' => 'closed']);

        Log::info('Closed admin inventories for cycle', [
            'year' => $year,
            'closed_count' => $closed,
        ]);

        return [
            'year' => $year,
            'closed_count' => $closed,
        ];
    }

    /**
     * Get historical performance data.
     */
    public function getHistoricalPerformance(): array
    {
        $data = AdminScholarshipInventory::selectRaw('
                application_year,
                COUNT(*) as total_combinations,
                SUM(total_scholarships_from_university) as total_from_universities,
                SUM(scholarships_given_to_agents) as total_to_agents,
                SUM(margin_scholarships) as total_margin,
                SUM(unclaimed_scholarships) as total_unclaimed,
                SUM(available_scholarships) as total_available
            ')
            ->groupBy('application_year')
            ->orderBy('application_year', 'desc')
            ->get();

        return $data->map(function ($row) {
            return [
                'year' => $row->application_year,
                'combinations' => $row->total_combinations,
                'from_universities' => (float) $row->total_from_universities,
                'to_agents' => (float) $row->total_to_agents,
                'admin_margin' => (float) $row->total_margin,
                'unclaimed' => (float) $row->total_unclaimed,
                'total_available' => (float) $row->total_available,
                'efficiency_rate' => $row->total_from_universities > 0
                    ? round(($row->total_to_agents / $row->total_from_universities) * 100, 1)
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Simulate inventory changes for "what if" scenarios.
     */
    public function simulateInventoryChanges(int $universityId, int $degreeId, int $additionalApplications, ?int $year = null): array
    {
        $year = $year ?? ScholarshipPoint::getCurrentApplicationYear();

        $inventory = AdminScholarshipInventory::getOrCreateFor($universityId, $degreeId, $year);
        $university = University::find($universityId);
        $degree = Degree::find($degreeId);

        $requirements = $university->getScholarshipRequirementForDegree($degree->name);
        if (! $requirements) {
            return ['error' => 'No scholarship requirements found'];
        }

        $adminThreshold = $requirements['min_agent_scholarships'] ?? 4;
        $agentThreshold = $requirements['min_students'] ?? 5;

        $currentApplications = $inventory->total_approved_applications;
        $newApplications = $currentApplications + $additionalApplications;

        $currentFromUniversity = $inventory->total_scholarships_from_university;
        $newFromUniversity = $newApplications / $adminThreshold;

        $additionalFromUniversity = $newFromUniversity - $currentFromUniversity;
        $additionalToAgents = floor($additionalApplications / $agentThreshold);
        $additionalMargin = $additionalFromUniversity - $additionalToAgents;

        return [
            'current' => [
                'applications' => $currentApplications,
                'from_university' => $currentFromUniversity,
                'available_to_admin' => $inventory->available_scholarships,
            ],
            'projected' => [
                'applications' => $newApplications,
                'from_university' => $newFromUniversity,
                'additional_from_university' => $additionalFromUniversity,
                'additional_to_agents' => $additionalToAgents,
                'additional_margin' => $additionalMargin,
                'new_available_to_admin' => $inventory->available_scholarships + $additionalMargin,
            ],
        ];
    }
}
