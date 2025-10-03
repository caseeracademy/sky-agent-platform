<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipCommission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'commission_number',
        'agent_id',
        'university_id',
        'degree_id',
        'qualifying_points_count',
        'status',
        'earned_at',
        'used_at',
        'used_in_application_id',
        'application_id',
        'application_year',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (ScholarshipCommission $commission) {
            if (empty($commission->commission_number)) {
                $commission->commission_number = static::generateCommissionNumber();
            }

            if (empty($commission->earned_at)) {
                $commission->earned_at = now();
            }

            if (empty($commission->application_year)) {
                $commission->application_year = ScholarshipPoint::getCurrentApplicationYear();
            }
        });
    }

    /**
     * Generate unique commission number.
     */
    private static function generateCommissionNumber(): string
    {
        $year = now()->year;
        $lastCommission = static::where('commission_number', 'like', "SC-{$year}-%")
            ->orderBy('commission_number', 'desc')
            ->first();

        if ($lastCommission) {
            $lastNumber = (int) substr($lastCommission->commission_number, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('SC-%d-%03d', $year, $newNumber);
    }

    /**
     * Get the agent who earned this commission.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the university for this commission.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * Get the degree for this commission.
     */
    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }

    /**
     * Get the application where this commission was used.
     */
    public function usedInApplication(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'used_in_application_id');
    }

    /**
     * Scope available commissions.
     */
    public function scopeAvailable(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'earned');
    }

    /**
     * Scope commissions for specific combination.
     */
    public function scopeForCombination(\Illuminate\Database\Eloquent\Builder $query, int $agentId, int $universityId, int $degreeId): void
    {
        $query->where('agent_id', $agentId)
            ->where('university_id', $universityId)
            ->where('degree_id', $degreeId);
    }

    /**
     * Mark commission as used.
     */
    public function markAsUsed(int $applicationId): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
            'used_in_application_id' => $applicationId,
        ]);
    }

    /**
     * Check if commission can be used for given university and degree.
     */
    public function canBeUsedFor(int $universityId, int $degreeId): bool
    {
        return $this->status === 'earned'
               && $this->university_id === $universityId
               && $this->degree_id === $degreeId;
    }

    /**
     * Get display name for this commission.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->university->name} - {$this->degree->name} Scholarship";
    }
}
