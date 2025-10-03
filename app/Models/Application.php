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
        'commission_type',
        'reviewed_by',
        'review_notes',
        'needs_review',
        'notes',
        'admin_notes',
        'additional_documents_request',
        'documents',
        'intake_date',
        'commission_amount',
        'commission_paid',
        'submitted_at',
        'reviewed_at',
        'decision_at',
        // Payment fields
        'payment_receipt_path',
        'payment_receipt_uploaded_at',
        'payment_receipt_uploaded_by',
        'payment_verified_at',
        'payment_verified_by',
        // Offer letter fields
        'offer_letter_path',
        'offer_letter_sent_at',
        'university_response_date',
        // Rejection fields
        'rejection_reason',
        'rejected_at',
        'rejected_by',
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
            'needs_review' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'decision_at' => 'datetime',
            'payment_receipt_uploaded_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'offer_letter_sent_at' => 'datetime',
            'university_response_date' => 'date',
            'rejected_at' => 'datetime',
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
            $number = 'APP-'.now()->format('Y').'-'.strtoupper(Str::random(6));
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
     * Get the admin who reviewed the application.
     */
    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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
     * Get the scholarship points for the application.
     */
    public function scholarshipPoints(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ScholarshipPoint::class);
    }

    /**
     * Get the status history for the application.
     */
    public function statusHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the user who uploaded payment receipt.
     */
    public function paymentReceiptUploader(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_receipt_uploaded_by');
    }

    /**
     * Get the user who verified payment.
     */
    public function paymentVerifier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    /**
     * Get the user who rejected the application.
     */
    public function rejectedByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
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

    /**
     * Check if application needs initial review (commission type choice).
     */
    public function needsReview(): bool
    {
        return $this->needs_review && $this->status === 'submitted';
    }

    /**
     * Check if application is under review (commission type chosen, awaiting approval).
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    /**
     * Move application to under review status after commission type is chosen.
     */
    public function moveToUnderReview(string $commissionType, int $reviewerId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'under_review',
            'commission_type' => $commissionType,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes,
            'needs_review' => false,
        ]);
    }

    /**
     * Mark as reviewed with commission type choice.
     */
    public function markAsReviewed(string $commissionType, int $reviewerId, ?string $notes = null): void
    {
        $this->update([
            'commission_type' => $commissionType,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes,
            'needs_review' => false,
        ]);
    }

    /**
     * Check if commission type is scholarship.
     */
    public function isScholarshipCommission(): bool
    {
        return $this->commission_type === 'scholarship';
    }

    /**
     * Check if commission type is money.
     */
    public function isMoneyCommission(): bool
    {
        return $this->commission_type === 'money';
    }

    /**
     * Scope applications that need initial review (commission type choice).
     */
    public function scopeNeedsReview(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('needs_review', true)->where('status', 'submitted');
    }

    /**
     * Scope applications that are under review (awaiting approval).
     */
    public function scopeUnderReview(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('status', 'under_review');
    }

    /**
     * Scope applications reviewed by specific admin.
     */
    public function scopeReviewedBy(\Illuminate\Database\Eloquent\Builder $query, int $adminId): void
    {
        $query->where('reviewed_by', $adminId);
    }
}
