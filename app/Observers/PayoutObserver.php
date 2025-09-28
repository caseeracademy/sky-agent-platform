<?php

namespace App\Observers;

use App\Models\Payout;
use App\Models\User;
use App\Notifications\PayoutRequested;
use App\Notifications\PayoutStatusChanged;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PayoutObserver
{
    /**
     * Handle the Payout "created" event.
     */
    public function created(Payout $payout): void
    {
        // Send notification to all admins when a payout is requested
        try {
            $admins = User::whereIn('role', ['super_admin', 'admin_staff'])->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new PayoutRequested($payout));
            }
            
            Log::info("Payout request notifications sent to admins for Payout [{$payout->id}].");
        } catch (\Exception $e) {
            Log::error("Failed to send payout request notifications for Payout [{$payout->id}]: " . $e->getMessage());
        }
    }

    /**
     * Handle the Payout "updated" event.
     */
    public function updated(Payout $payout): void
    {
        // Handle status changes
        if ($payout->isDirty('status')) {
            $oldStatus = $payout->getOriginal('status');
            $newStatus = $payout->status;

            Log::info("Payout [{$payout->id}] status changed from '{$oldStatus}' to '{$newStatus}'.");

            // Send notification to the agent about status change
            try {
                $payout->load('agent');
                
                if ($payout->agent) {
                    $payout->agent->notify(
                        new PayoutStatusChanged($payout, $oldStatus, $newStatus)
                    );
                    Log::info("Payout status change notification sent to agent for Payout [{$payout->id}].");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send payout status change notification for Payout [{$payout->id}]: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Payout "deleted" event.
     */
    public function deleted(Payout $payout): void
    {
        //
    }

    /**
     * Handle the Payout "restored" event.
     */
    public function restored(Payout $payout): void
    {
        //
    }

    /**
     * Handle the Payout "force deleted" event.
     */
    public function forceDeleted(Payout $payout): void
    {
        //
    }
}