<?php

namespace App\Observers;

use App\Models\Application;
use App\Models\Commission;
use App\Models\User;
use App\Notifications\ApplicationAssigned;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\CommissionEarned;
use App\Notifications\StudentApplicationSubmitted;
use App\Services\WalletService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ApplicationObserver
{
    /**
     * Handle the Application "created" event.
     */
    public function created(Application $application): void
    {
        // Send notification to the agent when a new application is submitted
        try {
            $application->load(['student.agent', 'program.university']);

            if ($application->student && $application->student->agent) {
                $application->student->agent->notify(
                    new StudentApplicationSubmitted($application)
                );
                Log::info("New application notification sent to agent for Application [{$application->application_number}].");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send new application notification for Application [{$application->application_number}]: ".$e->getMessage());
        }
    }

    /**
     * Handle the Application "updated" event.
     */
    public function updated(Application $application): void
    {
        // Handle status changes
        if ($application->isDirty('status')) {
            $oldStatus = $application->getOriginal('status');
            $newStatus = $application->status;

            Log::info("Application [{$application->application_number}] status changed from '{$oldStatus}' to '{$newStatus}'.");

            // Send notification to the agent about status change
            try {
                $application->load(['student.agent', 'program.university']);

                if ($application->student && $application->student->agent) {
                    $application->student->agent->notify(
                        new ApplicationStatusChanged($application, $oldStatus, $newStatus)
                    );
                    Log::info("Status change notification sent to agent for Application [{$application->application_number}].");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send status change notification for Application [{$application->application_number}]: ".$e->getMessage());
            }

            // Handle commission creation when approved
            if ($newStatus === 'approved') {
                Log::info("Application [{$application->application_number}] status changed to 'approved'. Processing commission based on type.");

                // Ensure application has commission type chosen
                if (! $application->commission_type) {
                    Log::error("Application [{$application->application_number}] approved but no commission type chosen. This should not happen!");

                    return;
                }

                // Note: reviewed_at/reviewed_by check removed - not required in new lifecycle

                // Only proceed if a commission does not already exist
                if ($application->commission()->exists()) {
                    Log::warning("Commission check for [{$application->application_number}]: Skipped, commission already exists.");

                    return;
                }

                try {
                    // Ensure relationships are loaded
                    $application->load(['student', 'program']);

                    // Validate that required relationships exist
                    if (! $application->student || ! $application->program || ! $application->student->agent_id) {
                        Log::error("Cannot create commission for [{$application->application_number}]: Missing required relationships");

                        return;
                    }

                    // Handle based on commission type
                    if ($application->isMoneyCommission()) {
                        $this->createMoneyCommission($application);
                    } elseif ($application->isScholarshipCommission()) {
                        $this->processScholarshipApplication($application);
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to process commission for Application [{$application->application_number}]: ".$e->getMessage());
                }
            }
        }

        // Handle admin assignment changes
        if ($application->isDirty('assigned_admin_id') && $application->assigned_admin_id) {
            try {
                $admin = User::find($application->assigned_admin_id);
                if ($admin) {
                    $admin->notify(new ApplicationAssigned($application));
                    Log::info("Application assignment notification sent to admin for Application [{$application->application_number}].");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send assignment notification for Application [{$application->application_number}]: ".$e->getMessage());
            }
        }
    }

    /**
     * Handle the Application "deleted" event.
     */
    public function deleted(Application $application): void
    {
        //
    }

    /**
     * Handle the Application "restored" event.
     */
    public function restored(Application $application): void
    {
        //
    }

    /**
     * Handle the Application "force deleted" event.
     */
    public function forceDeleted(Application $application): void
    {
        //
    }

    /**
     * Create money commission when application is approved with money commission type.
     */
    private function createMoneyCommission(Application $application): void
    {
        try {
            $commission = $application->commission()->create([
                'agent_id' => $application->student->agent_id,
                'amount' => $application->program->agent_commission,
            ]);

            app(WalletService::class)->credit($commission);

            Log::info("Money commission created for Application [{$application->application_number}]: Commission ID [{$commission->id}]");

            // Send commission earned notification to the agent
            if ($application->student->agent) {
                $application->student->agent->notify(new CommissionEarned($commission));
                Log::info("Commission earned notification sent to agent for Application [{$application->application_number}].");
            }

        } catch (\Exception $e) {
            Log::error("Failed to create money commission for Application [{$application->application_number}]: ".$e->getMessage());
        }
    }

    /**
     * Process scholarship application using the simple, reliable service.
     */
    private function processScholarshipApplication(Application $application): void
    {
        try {
            // CRITICAL: Skip point creation for converted scholarships
            if ($application->converted_from_scholarship) {
                Log::info("Application [{$application->application_number}] is a converted scholarship - skipping scholarship point creation to prevent double-counting.");

                return;
            }

            $simpleScholarshipService = app(\App\Services\SimpleScholarshipService::class);
            $result = $simpleScholarshipService->processApprovedApplication($application);

            if ($result['success']) {
                Log::info("SimpleScholarshipService processed Application [{$application->application_number}]: {$result['message']}", [
                    'point_created' => $result['point_created'],
                    'scholarship_earned' => $result['scholarship_earned'],
                    'debug' => $result['debug'],
                ]);
            } else {
                Log::warning("SimpleScholarshipService failed for Application [{$application->application_number}]: {$result['message']}", [
                    'debug' => $result['debug'],
                ]);
            }

            // Always run auto-repair after processing to ensure scholarships are created immediately
            if ($application->student && $application->student->agent_id) {
                $fixed = $simpleScholarshipService->fixMissingScholarships($application->student->agent_id);
                if (! empty($fixed)) {
                    Log::info("Auto-repair created missing scholarships for Application [{$application->application_number}]", [
                        'agent_id' => $application->student->agent_id,
                        'scholarships_created' => count($fixed),
                        'scholarships' => $fixed,
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error("Failed to process scholarship application [{$application->application_number}]: ".$e->getMessage());
        }
    }
}
