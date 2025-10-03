<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminScholarshipInventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'university_id',
        'degree_id',
        'application_year',
        'total_scholarships_from_university',
        'scholarships_given_to_agents',
        'margin_scholarships',
        'unclaimed_scholarships',
        'available_scholarships',
        'total_approved_applications',
        'completed_agent_scholarships',
        'status',
        'calculation_details',
        'last_calculated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_scholarships_from_university' => 'decimal:2',
            'scholarships_given_to_agents' => 'decimal:2',
            'margin_scholarships' => 'decimal:2',
            'unclaimed_scholarships' => 'decimal:2',
            'available_scholarships' => 'decimal:2',
            'calculation_details' => 'array',
            'last_calculated_at' => 'datetime',
        ];
    }

    /**
     * Get the university for this inventory.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * Get the degree for this inventory.
     */
    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }

    /**
     * Get the application cycle for this inventory.
     */
    public function applicationCycle(): BelongsTo
    {
        return $this->belongsTo(ApplicationCycle::class, 'application_year', 'year');
    }

    /**
     * Scope active inventories.
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope for current application year.
     */
    public function scopeCurrentYear(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('application_year', ScholarshipPoint::getCurrentApplicationYear());
    }

    /**
     * Scope for specific combination.
     */
    public function scopeForCombination(\Illuminate\Database\Eloquent\Builder $query, int $universityId, int $degreeId, int $year): void
    {
        $query->where('university_id', $universityId)
            ->where('degree_id', $degreeId)
            ->where('application_year', $year);
    }

    /**
     * Calculate and update inventory based on current data.
     */
    public function recalculate(): void
    {
        $university = $this->university;
        $degree = $this->degree;

        // Get scholarship requirements
        $requirements = $university->getScholarshipRequirementForDegree($degree->name);
        if (! $requirements) {
            return;
        }

        $systemThreshold = $requirements['system_threshold'] ?? $requirements['min_agent_scholarships'] ?? 4; // System gets 1 per 4 students FROM university
        $agentThreshold = $requirements['agent_threshold'] ?? $requirements['min_students'] ?? 5; // Agent gets 1 per 5 students FROM system

        // Count total approved scholarship applications for this combination in this year
        $totalApplications = ScholarshipPoint::where('university_id', $this->university_id)
            ->where('degree_id', $this->degree_id)
            ->where('application_year', $this->application_year)
            ->count();

        // Calculate what university owes system (Sky)
        $totalFromUniversity = $totalApplications / $systemThreshold;

        // Count completed agent scholarships
        $completedAgentScholarships = ScholarshipCommission::where('university_id', $this->university_id)
            ->where('degree_id', $this->degree_id)
            ->where('application_year', $this->application_year)
            ->count();

        // Calculate scholarships given to agents
        $scholarshipsToAgents = $completedAgentScholarships;

        // Calculate margin (what system keeps as profit from threshold difference)
        // This is the guaranteed profit from the difference between system and agent thresholds
        $theoreticalAgentScholarships = $totalApplications / $agentThreshold;
        $marginScholarships = max(0, $totalFromUniversity - $theoreticalAgentScholarships);

        // Calculate unclaimed (from agents who didn't complete their individual quotas)
        $unclaimedScholarships = max(0, $theoreticalAgentScholarships - $scholarshipsToAgents);

        // Total available to admin
        $availableScholarships = $marginScholarships + $unclaimedScholarships;

        // Calculation details for transparency
        $calculationDetails = [
            'total_applications' => $totalApplications,
            'system_threshold' => $systemThreshold,
            'agent_threshold' => $agentThreshold,
            'calculation_breakdown' => [
                'from_university' => [
                    'formula' => "{$totalApplications} applications ÷ {$systemThreshold} system threshold",
                    'result' => $totalFromUniversity,
                ],
                'theoretical_agent_scholarships' => [
                    'formula' => "{$totalApplications} applications ÷ {$agentThreshold} agent threshold",
                    'result' => $theoreticalAgentScholarships,
                ],
                'to_agents' => [
                    'completed_scholarships' => $completedAgentScholarships,
                    'result' => $scholarshipsToAgents,
                ],
                'margin' => [
                    'formula' => 'From university - Theoretical agent scholarships (threshold difference profit)',
                    'result' => $marginScholarships,
                ],
                'unclaimed' => [
                    'formula' => 'Theoretical agent scholarships - Actually given to agents (incomplete quotas)',
                    'result' => $unclaimedScholarships,
                ],
            ],
            'calculated_at' => now()->toISOString(),
        ];

        // Update the inventory
        $this->update([
            'total_scholarships_from_university' => $totalFromUniversity,
            'scholarships_given_to_agents' => $scholarshipsToAgents,
            'margin_scholarships' => $marginScholarships,
            'unclaimed_scholarships' => $unclaimedScholarships,
            'available_scholarships' => $availableScholarships,
            'total_approved_applications' => $totalApplications,
            'completed_agent_scholarships' => $completedAgentScholarships,
            'calculation_details' => $calculationDetails,
            'last_calculated_at' => now(),
        ]);
    }

    /**
     * Get or create inventory for combination.
     */
    public static function getOrCreateFor(int $universityId, int $degreeId, int $year): self
    {
        return static::firstOrCreate([
            'university_id' => $universityId,
            'degree_id' => $degreeId,
            'application_year' => $year,
        ]);
    }

    /**
     * Get display name for this inventory.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->university->name} - {$this->degree->name} ({$this->application_year})";
    }

    /**
     * Check if inventory has scholarships available.
     */
    public function hasAvailableScholarships(): bool
    {
        return $this->available_scholarships > 0;
    }

    /**
     * Get breakdown summary.
     */
    public function getBreakdownSummary(): array
    {
        return [
            'total_from_university' => $this->total_scholarships_from_university,
            'given_to_agents' => $this->scholarships_given_to_agents,
            'admin_margin' => $this->margin_scholarships,
            'unclaimed' => $this->unclaimed_scholarships,
            'available_to_admin' => $this->available_scholarships,
        ];
    }

    /**
     * Close this inventory (end of cycle).
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Archive this inventory.
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }
}
