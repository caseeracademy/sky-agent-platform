<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
        'is_active',
        'scholarship_requirements',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'scholarship_requirements' => 'array',
        ];
    }

    /**
     * Get the programs for the university.
     */
    public function programs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * Get the scholarship awards given by this university.
     */
    public function scholarshipAwards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ScholarshipAward::class);
    }

    /**
     * Get the system scholarship awards given by this university.
     */
    public function systemScholarshipAwards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SystemScholarshipAward::class);
    }

    /**
     * Scope to get only active universities.
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Get scholarship requirements for a specific degree type.
     */
    public function getScholarshipRequirementForDegree(string $degreeType): ?array
    {
        if (! $this->scholarship_requirements || ! is_array($this->scholarship_requirements)) {
            return null;
        }

        // Handle direct key access (new format)
        if (isset($this->scholarship_requirements[$degreeType])) {
            return $this->scholarship_requirements[$degreeType];
        }

        // Handle array format (from form)
        foreach ($this->scholarship_requirements as $requirement) {
            if (is_array($requirement) && isset($requirement['degree_type']) && $requirement['degree_type'] === $degreeType) {
                return [
                    'system_threshold' => $requirement['system_threshold'] ?? $requirement['min_agent_scholarships'] ?? 4,
                    'agent_threshold' => $requirement['agent_threshold'] ?? $requirement['min_students'] ?? 5,
                ];
            }
        }

        return null;
    }

    /**
     * Get minimum students required for scholarship eligibility for a degree type.
     */
    public function getMinStudentsForScholarship(string $degreeType): ?int
    {
        $requirement = $this->getScholarshipRequirementForDegree($degreeType);

        return $requirement['min_students'] ?? null;
    }

    /**
     * Check if university has any scholarship requirements.
     */
    public function hasAnyScholarshipRequirements(): bool
    {
        return ! empty($this->scholarship_requirements);
    }

    /**
     * Check if degree type has scholarship requirements.
     */
    public function hasScholarshipRequirements(string $degreeType): bool
    {
        return $this->getScholarshipRequirementForDegree($degreeType) !== null;
    }

    /**
     * Check if agent is eligible for scholarship for a specific degree type.
     */
    public function isAgentEligibleForScholarship(int $agentId, string $degreeType): bool
    {
        $minStudents = $this->getMinStudentsForScholarship($degreeType);

        if (! $minStudents) {
            return false;
        }

        // Count approved applications for this agent, university, and degree type
        $approvedCount = Application::whereHas('program', function ($query) use ($degreeType) {
            $query->where('university_id', $this->id)
                ->where('degree_type', $degreeType);
        })
            ->whereHas('student', function ($query) use ($agentId) {
                $query->where('agent_id', $agentId);
            })
            ->where('status', 'approved')
            ->count();

        return $approvedCount >= $minStudents;
    }

    /**
     * Get all degree types that have scholarship requirements.
     */
    public function getScholarshipDegreeTypes(): array
    {
        if (! $this->scholarship_requirements || ! is_array($this->scholarship_requirements)) {
            return [];
        }

        return array_keys($this->scholarship_requirements);
    }

    /**
     * Get minimum agent scholarships required for system scholarship eligibility.
     */
    public function getMinAgentScholarshipsForSystem(string $degreeType): ?int
    {
        $requirement = $this->getScholarshipRequirementForDegree($degreeType);

        return $requirement['min_agent_scholarships'] ?? null;
    }

    /**
     * Check if system is eligible for scholarship for a specific degree type.
     */
    public function isSystemEligibleForScholarship(string $degreeType): bool
    {
        $minAgentScholarships = $this->getMinAgentScholarshipsForSystem($degreeType);

        if (! $minAgentScholarships) {
            return false;
        }

        // Count awarded agent scholarships for this university and degree type
        $awardedAgentScholarships = ScholarshipAward::where('university_id', $this->id)
            ->where('degree_type', $degreeType)
            ->whereIn('status', ['approved', 'paid'])
            ->count();

        // Count existing system scholarships to avoid duplicates
        $existingSystemScholarships = SystemScholarshipAward::where('university_id', $this->id)
            ->where('degree_type', $degreeType)
            ->count();

        // Calculate how many system scholarships should exist
        $expectedSystemScholarships = intval($awardedAgentScholarships / $minAgentScholarships);

        return $expectedSystemScholarships > $existingSystemScholarships;
    }
}
