<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipPoint extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'agent_id',
        'university_id',
        'degree_id',
        'program_id',
        'application_id',
        'student_id',
        'status',
        'earned_at',
        'redeemed_at',
        'expires_at',
        'application_year',
        'cycle_start_date',
        'cycle_end_date',
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
            'redeemed_at' => 'datetime',
            'expires_at' => 'datetime',
            'cycle_start_date' => 'date',
            'cycle_end_date' => 'date',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (ScholarshipPoint $point) {
            if (empty($point->earned_at)) {
                $point->earned_at = now();
            }

            // Set application cycle dates
            if (empty($point->application_year)) {
                $point->application_year = static::getCurrentApplicationYear();
            }

            if (empty($point->cycle_start_date) || empty($point->cycle_end_date)) {
                $dates = static::getApplicationCycleDates($point->application_year);
                $point->cycle_start_date = $dates['start'];
                $point->cycle_end_date = $dates['end'];
            }

            if (empty($point->expires_at)) {
                $point->expires_at = $point->cycle_end_date->endOfDay();
            }
        });
    }

    /**
     * Get current application year based on cycle.
     */
    public static function getCurrentApplicationYear(): int
    {
        $now = now();
        $currentYear = $now->year;

        // If before July 1, we're in previous year's cycle
        if ($now->month < 7) {
            return $currentYear - 1;
        }

        return $currentYear;
    }

    /**
     * Get application cycle dates for a given year.
     */
    public static function getApplicationCycleDates(int $year): array
    {
        return [
            'start' => now()->create($year, 7, 1), // July 1
            'end' => now()->create($year, 11, 30), // November 30
        ];
    }

    /**
     * Get the default expiry date for new scholarship points.
     */
    public static function getDefaultExpiryDate(): \Illuminate\Support\Carbon
    {
        $year = self::getCurrentApplicationYear();

        // Points expire on November 30 of the current cycle year
        return now()->create($year, 11, 30, 23, 59, 59);
    }

    /**
     * Get the agent who earned this point.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the university for this point.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * Get the degree for this point.
     */
    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }

    /**
     * Get the program for this point.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the application that earned this point.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the student for this point.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Scope active points.
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope points for specific agent+university+degree combination.
     */
    public function scopeForCombination(\Illuminate\Database\Eloquent\Builder $query, int $agentId, int $universityId, int $degreeId): void
    {
        $query->where('agent_id', $agentId)
            ->where('university_id', $universityId)
            ->where('degree_id', $degreeId);
    }

    /**
     * Scope points for current application cycle.
     */
    public function scopeCurrentCycle(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $currentYear = static::getCurrentApplicationYear();
        $query->where('application_year', $currentYear);
    }

    /**
     * Mark point as redeemed.
     */
    public function markAsRedeemed(): void
    {
        $this->update([
            'status' => 'redeemed',
            'redeemed_at' => now(),
        ]);
    }

    /**
     * Mark point as expired.
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Check if point is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    /**
     * Get days until expiry.
     */
    public function getDaysUntilExpiry(): int
    {
        if (! $this->expires_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->expires_at, false));
    }
}
