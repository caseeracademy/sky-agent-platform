<?php

namespace App\Observers;

use App\Models\ScholarshipAward;
use App\Models\SystemScholarshipAward;
use Illuminate\Support\Facades\Log;

class ScholarshipAwardObserver
{
    /**
     * Handle the ScholarshipAward "created" event.
     */
    public function created(ScholarshipAward $scholarshipAward): void
    {
        //
    }

    /**
     * Handle the ScholarshipAward "updated" event.
     */
    public function updated(ScholarshipAward $scholarshipAward): void
    {
        // Check for system scholarship eligibility when agent scholarship is paid
        if ($scholarshipAward->isDirty('status') && $scholarshipAward->status === 'paid') {
            $this->checkSystemScholarshipEligibility($scholarshipAward);
        }
    }

    /**
     * Handle the ScholarshipAward "deleted" event.
     */
    public function deleted(ScholarshipAward $scholarshipAward): void
    {
        //
    }

    /**
     * Handle the ScholarshipAward "restored" event.
     */
    public function restored(ScholarshipAward $scholarshipAward): void
    {
        //
    }

    /**
     * Handle the ScholarshipAward "force deleted" event.
     */
    public function forceDeleted(ScholarshipAward $scholarshipAward): void
    {
        //
    }

    /**
     * Check if system is eligible for scholarship after agent scholarship is paid.
     */
    private function checkSystemScholarshipEligibility(ScholarshipAward $scholarshipAward): void
    {
        try {
            // Load university relationship
            $scholarshipAward->load('university');

            if (! $scholarshipAward->university) {
                Log::warning("System scholarship eligibility check skipped for ScholarshipAward [{$scholarshipAward->award_number}]: Missing university relationship");

                return;
            }

            $university = $scholarshipAward->university;
            $degreeType = $scholarshipAward->degree_type;

            Log::info("Checking system scholarship eligibility for University [{$university->name}] degree [{$degreeType}] after agent scholarship payment");

            // Check if university has system scholarship requirements for this degree type
            if (! $university->system_scholarship_requirements || ! isset($university->system_scholarship_requirements[$degreeType])) {
                Log::info("No system scholarship requirements found for degree type [{$degreeType}] at University [{$university->name}]");

                return;
            }

            // Use university method to check eligibility
            if ($university->isSystemEligibleForScholarship($degreeType)) {
                // System is eligible! Create system scholarship award
                $minAgentScholarships = $university->getMinAgentScholarshipsForSystem($degreeType);

                // Count current awarded agent scholarships
                $awardedAgentScholarships = ScholarshipAward::where('university_id', $university->id)
                    ->where('degree_type', $degreeType)
                    ->whereIn('status', ['approved', 'paid'])
                    ->count();

                $systemScholarshipAward = SystemScholarshipAward::create([
                    'university_id' => $university->id,
                    'degree_type' => $degreeType,
                    'qualifying_agent_scholarships_count' => $awardedAgentScholarships,
                    'status' => 'pending',
                    'notes' => "Automatically awarded after {$awardedAgentScholarships} agent scholarships (requirement: {$minAgentScholarships})",
                ]);

                Log::info("System scholarship award created: [{$systemScholarshipAward->award_number}] for University [{$university->name}] - Qualifying agent scholarships: {$awardedAgentScholarships}");

                // TODO: Send notification to admin about system scholarship award
                // Notification::route('mail', config('app.admin_email'))->notify(new SystemScholarshipAwarded($systemScholarshipAward));
            } else {
                Log::info("System not yet eligible for scholarship at University [{$university->name}] for degree [{$degreeType}]");
            }
        } catch (\Exception $e) {
            Log::error("Failed to check system scholarship eligibility for ScholarshipAward [{$scholarshipAward->award_number}]: ".$e->getMessage());
        }
    }
}
