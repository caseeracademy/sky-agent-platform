<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SystemScholarshipAward extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'award_number',
        'university_id',
        'degree_type',
        'application_year',
        'qualifying_agent_scholarships_count',
        'total_applications_count',
        'system_scholarships_earned',
        'margin_scholarships',
        'unclaimed_scholarships',
        'calculation_details',
        'status',
        'notes',
        'awarded_at',
        'approved_at',
        'paid_at',
        'last_updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'system_scholarships_earned' => 'decimal:2',
            'margin_scholarships' => 'decimal:2',
            'unclaimed_scholarships' => 'decimal:2',
            'calculation_details' => 'array',
            'awarded_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'last_updated_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (SystemScholarshipAward $award) {
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
            $number = 'SYS-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (static::where('award_number', $number)->exists());

        return $number;
    }

    /**
     * Get the university that awarded the system scholarship.
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
