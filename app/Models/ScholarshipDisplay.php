<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Virtual model for displaying both completed and progress scholarships in the agent panel.
 * This is not a database table, but a wrapper for display purposes.
 */
class ScholarshipDisplay extends Model
{
    protected $fillable = [
        'id',
        'type', // 'completed' or 'progress'
        'commission_number',
        'university_id',
        'degree_id',
        'status',
        'current_points',
        'threshold',
        'progress_percentage',
        'progress_text',
        'status_text',
        'color',
        'qualifying_points_count',
        'earned_at',
        'used_at',
        'notes',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'used_at' => 'datetime',
        'current_points' => 'integer',
        'threshold' => 'integer',
        'progress_percentage' => 'integer',
        'qualifying_points_count' => 'integer',
    ];

    // Disable timestamps since this is a virtual model
    public $timestamps = false;

    // Disable database operations since this is a virtual model
    protected $table = null;

    // Disable auto-incrementing for virtual model
    public $incrementing = false;

    // Set key type to string for custom IDs
    protected $keyType = 'string';

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function degree(): BelongsTo
    {
        return $this->belongsTo(Degree::class);
    }

    /**
     * Create a ScholarshipDisplay instance from a completed ScholarshipCommission.
     */
    public static function fromCompletedScholarship(ScholarshipCommission $commission): self
    {
        $instance = new self;
        $instance->fill([
            'id' => 'completed_'.$commission->id,
            'type' => 'completed',
            'commission_number' => $commission->commission_number,
            'university_id' => $commission->university_id,
            'degree_id' => $commission->degree_id,
            'status' => $commission->status,
            'qualifying_points_count' => $commission->qualifying_points_count,
            'earned_at' => $commission->earned_at,
            'used_at' => $commission->used_at,
            'notes' => $commission->notes,
            'progress_percentage' => 100,
            'progress_text' => 'Completed',
            'status_text' => $commission->status === 'earned' ? 'Ready to Use' : 'Already Used',
            'color' => $commission->status === 'earned' ? 'success' : 'info',
        ]);

        // Set relationships
        $instance->setRelation('university', $commission->university);
        $instance->setRelation('degree', $commission->degree);

        return $instance;
    }

    /**
     * Create a ScholarshipDisplay instance from progress data.
     */
    public static function fromProgressData(array $progressData): self
    {
        $instance = new self;
        $instance->fill([
            'id' => $progressData['id'],
            'type' => 'progress',
            'commission_number' => null,
            'university_id' => $progressData['university']->id,
            'degree_id' => $progressData['degree']->id,
            'status' => 'in_progress',
            'current_points' => $progressData['current_points'],
            'threshold' => $progressData['threshold'],
            'progress_percentage' => $progressData['progress_percentage'],
            'progress_text' => $progressData['progress_text'],
            'status_text' => $progressData['status_text'],
            'color' => $progressData['color'],
            'qualifying_points_count' => null,
            'earned_at' => null,
            'used_at' => null,
            'notes' => null,
        ]);

        // Set relationships
        $instance->setRelation('university', $progressData['university']);
        $instance->setRelation('degree', $progressData['degree']);

        return $instance;
    }

    /**
     * Get all scholarship displays for an agent (both completed and progress).
     */
    public static function getAllForAgent(int $agentId): \Illuminate\Support\Collection
    {
        $simpleService = app(\App\Services\SimpleScholarshipService::class);
        $data = $simpleService->getScholarshipPageData($agentId);

        $displays = collect();

        // Add completed scholarships
        foreach ($data['completed_scholarships'] as $completedData) {
            $commission = ScholarshipCommission::find($completedData['id']);
            if ($commission) {
                $displays->push(self::fromCompletedScholarship($commission));
            }
        }

        // Add progress scholarships
        foreach ($data['progress_scholarships'] as $progressData) {
            $displays->push(self::fromProgressData($progressData));
        }

        // Sort by priority: progress first (so users see what they're working on), then completed
        return $displays->sortBy(function ($item) {
            if ($item->type === 'progress') {
                return 0; // Progress items first
            }

            return 1; // Completed items second
        })->values();
    }
}
