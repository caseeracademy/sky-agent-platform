<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ScholarshipAward extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'award_number',
        'agent_id',
        'university_id',
        'degree_type',
        'qualifying_applications_count',
        'status',
        'notes',
        'awarded_at',
        'approved_at',
        'paid_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (ScholarshipAward $award) {
            if (empty($award->award_number)) {
                $award->award_number = static::generateAwardNumber();
            }
            if (empty($award->awarded_at)) {
                $award->awarded_at = now();
            }
        });
    }

    /**
     * Generate a unique award number.
     */
    public static function generateAwardNumber(): string
    {
        do {
            $number = 'SCH-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (static::where('award_number', $number)->exists());

        return $number;
    }

    /**
     * Get the agent that received the scholarship.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the university that awarded the scholarship.
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    /**
     * Scope scholarships by status.
     */
    public function scopeByStatus(\Illuminate\Database\Eloquent\Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope scholarships for a specific agent.
     */
    public function scopeForAgent(\Illuminate\Database\Eloquent\Builder $query, int $agentId): void
    {
        $query->where('agent_id', $agentId);
    }

    /**
     * Scope scholarships for a specific university.
     */
    public function scopeForUniversity(\Illuminate\Database\Eloquent\Builder $query, int $universityId): void
    {
        $query->where('university_id', $universityId);
    }

    /**
     * Scope scholarships by degree type.
     */
    public function scopeByDegreeType(\Illuminate\Database\Eloquent\Builder $query, string $degreeType): void
    {
        $query->where('degree_type', $degreeType);
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get the formatted status for display.
     */
    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Mark scholarship as approved.
     */
    public function markAsApproved(): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark scholarship as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark scholarship as cancelled.
     */
    public function markAsCancelled(?string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason ? "Cancelled: {$reason}" : 'Cancelled',
        ]);
    }

    /**
     * Check if the scholarship can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the scholarship can be paid.
     */
    public function canBePaid(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the scholarship can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }
}
