<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Application extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'application_number',
        'student_id',
        'program_id',
        'agent_id',
        'assigned_admin_id',
        'status',
        'notes',
        'admin_notes',
        'documents',
        'intake_date',
        'commission_amount',
        'commission_paid',
        'submitted_at',
        'reviewed_at',
        'decision_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'documents' => 'array',
            'intake_date' => 'date',
            'commission_amount' => 'decimal:2',
            'commission_paid' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'decision_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Application $application) {
            if (empty($application->application_number)) {
                $application->application_number = static::generateApplicationNumber();
            }
            if (empty($application->status)) {
                $application->status = 'pending';
            }
        });

        static::created(function (Application $application) {
            // Log application creation (only if user is authenticated)
            if (auth()->check()) {
                ApplicationLog::logCreation($application, auth()->user());
            }
        });

        static::updating(function (Application $application) {
            // Log status changes (only if user is authenticated)
            if ($application->isDirty('status') && auth()->check()) {
                $oldStatus = $application->getOriginal('status');
                $newStatus = $application->status;
                
                ApplicationLog::logStatusChange(
                    $application,
                    auth()->user(),
                    $oldStatus,
                    $newStatus
                );
            }
        });
    }

    /**
     * Generate a unique application number.
     */
    public static function generateApplicationNumber(): string
    {
        do {
            $number = 'APP-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        } while (static::where('application_number', $number)->exists());

        return $number;
    }

    /**
     * Get the student that owns the application.
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the program for the application.
     */
    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the agent that created the application.
     */
    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the admin assigned to the application.
     */
    public function assignedAdmin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    /**
     * Get the university through the program.
     */
    public function university(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(University::class, Program::class, 'id', 'id', 'program_id', 'university_id');
    }

    /**
     * Get the logs for the application.
     */
    public function applicationLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationLog::class);
    }

    /**
     * Get the documents for the application.
     */
    public function applicationDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    /**
     * Get the commission for the application.
     */
    public function commission(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Commission::class);
    }

    /**
     * Scope applications for a specific agent.
     */
    public function scopeForAgent(\Illuminate\Database\Eloquent\Builder $query, int $agentId): void
    {
        $query->where('agent_id', $agentId);
    }

    /**
     * Scope applications by status.
     */
    public function scopeByStatus(\Illuminate\Database\Eloquent\Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    /**
     * Scope applications pending review.
     */
    public function scopePendingReview(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->whereIn('status', ['submitted', 'under_review', 'additional_documents_required']);
    }

    /**
     * Scope applications with commission pending.
     */
    public function scopeCommissionPending(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'approved')
              ->where('commission_paid', false)
              ->whereNotNull('commission_amount');
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'submitted' => 'info',
            'under_review' => 'warning',
            'additional_documents_required' => 'danger',
            'approved' => 'success',
            'rejected' => 'danger',
            'enrolled' => 'success',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the formatted status for display.
     */
    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'additional_documents_required' => 'Additional Documents Required',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'enrolled' => 'Enrolled',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Check if the application can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'additional_documents_required']);
    }

    /**
     * Check if the application can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark application as submitted.
     */
    public function markAsSubmitted(): void
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    /**
     * Calculate commission amount based on program.
     */
    public function calculateCommission(): void
    {
        if ($this->program) {
            $this->update([
                'commission_amount' => $this->program->agent_commission,
            ]);
        }
    }
}
