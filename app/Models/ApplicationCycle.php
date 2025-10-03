<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationCycle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get scholarship points for this cycle.
     */
    public function scholarshipPoints(): HasMany
    {
        return $this->hasMany(ScholarshipPoint::class, 'application_year', 'year');
    }

    /**
     * Get scholarship commissions for this cycle.
     */
    public function scholarshipCommissions(): HasMany
    {
        return $this->hasMany(ScholarshipCommission::class, 'application_year', 'year');
    }

    /**
     * Get the current active cycle.
     */
    public static function getCurrentCycle(): ?self
    {
        return static::where('status', 'active')->first();
    }

    /**
     * Get the current or upcoming cycle.
     */
    public static function getCurrentOrUpcomingCycle(): ?self
    {
        return static::whereIn('status', ['active', 'upcoming'])
            ->orderBy('year')
            ->first();
    }

    /**
     * Get cycle by year.
     */
    public static function getByYear(int $year): ?self
    {
        return static::where('year', $year)->first();
    }

    /**
     * Scope active cycles.
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope upcoming cycles.
     */
    public function scopeUpcoming(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'upcoming');
    }

    /**
     * Scope closed cycles.
     */
    public function scopeClosed(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'closed');
    }

    /**
     * Check if cycle is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && now()->between($this->start_date, $this->end_date);
    }

    /**
     * Check if cycle is open for applications.
     */
    public function isOpen(): bool
    {
        return $this->isActive();
    }

    /**
     * Check if cycle has ended.
     */
    public function hasEnded(): bool
    {
        return now()->isAfter($this->end_date);
    }

    /**
     * Get days remaining in cycle.
     */
    public function getDaysRemaining(): int
    {
        if ($this->hasEnded()) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->end_date, false));
    }

    /**
     * Get progress percentage through cycle.
     */
    public function getProgressPercentage(): float
    {
        if (now()->isBefore($this->start_date)) {
            return 0;
        }

        if (now()->isAfter($this->end_date)) {
            return 100;
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);
        $daysPassed = $this->start_date->diffInDays(now());

        return round(($daysPassed / $totalDays) * 100, 1);
    }

    /**
     * Activate this cycle.
     */
    public function activate(): void
    {
        // Close any other active cycles
        static::where('status', 'active')->update(['status' => 'closed']);

        // Activate this cycle
        $this->update(['status' => 'active']);
    }

    /**
     * Close this cycle.
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Archive this cycle.
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Update cycle statuses based on current date.
     */
    public static function updateStatuses(): array
    {
        $updated = [
            'activated' => 0,
            'closed' => 0,
        ];

        $now = now();

        // Activate cycles that should be active
        $toActivate = static::where('status', 'upcoming')
            ->where('start_date', '<=', $now->toDateString())
            ->where('end_date', '>=', $now->toDateString())
            ->get();

        foreach ($toActivate as $cycle) {
            $cycle->activate();
            $updated['activated']++;
        }

        // Close cycles that have ended
        $toClose = static::where('status', 'active')
            ->where('end_date', '<', $now->toDateString())
            ->get();

        foreach ($toClose as $cycle) {
            $cycle->close();
            $updated['closed']++;
        }

        return $updated;
    }
}
