<?php

namespace App\Observers;

use App\Models\Application;
use App\Models\Commission;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use App\Notifications\CommissionEarned;
use App\Notifications\ApplicationAssigned;
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
        //
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
                Log::error("Failed to send status change notification for Application [{$application->application_number}]: " . $e->getMessage());
            }

            // Handle commission creation when approved
            if ($newStatus === 'approved') {
                Log::info("Application [{$application->application_number}] status changed to 'approved'. Checking for commission.");

                // Only proceed if a commission does not already exist
                if ($application->commission()->exists()) {
                    Log::warning("Commission check for [{$application->application_number}]: Skipped, commission already exists.");
                    return;
                }

                // Create the commission
                try {
                    // Ensure relationships are loaded
                    $application->load(['student', 'program']);

                    // Validate that required relationships exist
                    if (!$application->student || !$application->program || !$application->student->agent_id) {
                        Log::error("Cannot create commission for [{$application->application_number}]: Missing required relationships");
                        return;
                    }

                    $commission = $application->commission()->create([
                        'agent_id' => $application->student->agent_id,
                        'amount' => $application->program->agent_commission,
                    ]);

                    app(WalletService::class)->credit($commission);

                    Log::info("Commission successfully created for Application [{$application->application_number}].");

                    // Send commission earned notification to the agent
                    if ($application->student->agent) {
                        $application->student->agent->notify(new CommissionEarned($commission));
                        Log::info("Commission earned notification sent to agent for Application [{$application->application_number}].");
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to create commission for Application [{$application->application_number}]: " . $e->getMessage());
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
                Log::error("Failed to send assignment notification for Application [{$application->application_number}]: " . $e->getMessage());
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
}
