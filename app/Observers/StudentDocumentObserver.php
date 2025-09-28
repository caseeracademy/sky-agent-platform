<?php

namespace App\Observers;

use App\Models\StudentDocument;
use App\Models\User;
use App\Notifications\DocumentUploaded;
use Illuminate\Support\Facades\Log;

class StudentDocumentObserver
{
    /**
     * Handle the StudentDocument "created" event.
     */
    public function created(StudentDocument $studentDocument): void
    {
        // Send notification to all admins when a document is uploaded
        try {
            $studentDocument->load(['student.agent']);
            
            $admins = User::whereIn('role', ['super_admin', 'admin_staff'])->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new DocumentUploaded($studentDocument));
            }
            
            Log::info("Document upload notifications sent to admins for Document [{$studentDocument->id}].");
        } catch (\Exception $e) {
            Log::error("Failed to send document upload notifications for Document [{$studentDocument->id}]: " . $e->getMessage());
        }
    }

    /**
     * Handle the StudentDocument "updated" event.
     */
    public function updated(StudentDocument $studentDocument): void
    {
        //
    }

    /**
     * Handle the StudentDocument "deleted" event.
     */
    public function deleted(StudentDocument $studentDocument): void
    {
        //
    }

    /**
     * Handle the StudentDocument "restored" event.
     */
    public function restored(StudentDocument $studentDocument): void
    {
        //
    }

    /**
     * Handle the StudentDocument "force deleted" event.
     */
    public function forceDeleted(StudentDocument $studentDocument): void
    {
        //
    }
}