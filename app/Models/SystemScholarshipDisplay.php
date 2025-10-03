<?php

namespace App\Models;

use App\Services\SystemScholarshipService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Virtual model to represent system scholarships for display purposes.
 */
class SystemScholarshipDisplay extends Model
{
    protected $fillable = [
        'id',
        'university_id',
        'degree_id',
        'total_students',
        'university_threshold',
        'agent_threshold',
        'students_per_system_scholarship',
        'system_scholarships_earned',
        'current_cycle_progress',
        'progress_percentage',
        'students_needed_for_next',
        'contributing_agents',
        'status',
        'progress_text',
        'status_text',
        'color',
    ];

    protected $casts = [
        'total_students' => 'integer',
        'university_threshold' => 'integer',
        'agent_threshold' => 'integer',
        'students_per_system_scholarship' => 'integer',
        'system_scholarships_earned' => 'integer',
        'current_cycle_progress' => 'integer',
        'progress_percentage' => 'integer',
        'students_needed_for_next' => 'integer',
        'contributing_agents' => 'array',
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

    public static function fromSystemScholarshipData(array $data): self
    {
        $instance = new self;
        $instance->fill($data);

        // Manually set relationships for virtual model
        $instance->setRelation('university', $data['university']);
        $instance->setRelation('degree', $data['degree']);

        return $instance;
    }

    /**
     * Get all system scholarship displays.
     */
    public static function getAllSystemScholarships(): Collection
    {
        $systemService = app(SystemScholarshipService::class);
        $data = $systemService->getSystemScholarshipData();

        $allDisplays = new Collection;

        foreach ($data as $scholarshipData) {
            $allDisplays->add(self::fromSystemScholarshipData($scholarshipData));
        }

        // Sort: in_progress first, then earned, by progress percentage desc
        return $allDisplays->sortBy(function ($item) {
            if ($item->status === 'in_progress') {
                return 0 - $item->progress_percentage; // Higher progress first within in_progress
            }

            return 1; // Earned items second
        })->values();
    }
}
