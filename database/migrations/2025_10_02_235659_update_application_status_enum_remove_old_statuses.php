<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing statuses to new simplified flow
        DB::statement("UPDATE applications SET status = 'submitted' WHERE status = 'under_review'");
        DB::statement("UPDATE applications SET status = 'submitted' WHERE status = 'draft'");
        DB::statement("UPDATE applications SET status = 'submitted' WHERE status = 'pending'");
        DB::statement("UPDATE applications SET status = 'applied' WHERE status = 'waiting_to_apply'");
        DB::statement("UPDATE applications SET status = 'payment_approval' WHERE status = 'payment_pending'");

        // Update the ENUM to remove old statuses
        DB::statement("ALTER TABLE applications CHANGE status status ENUM(
            'needs_review',
            'submitted',
            'additional_documents_needed',
            'applied',
            'offer_received',
            'payment_approval',
            'ready_for_approval',
            'approved',
            'rejected'
        ) NOT NULL DEFAULT 'needs_review'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the old statuses
        DB::statement("ALTER TABLE applications CHANGE status status ENUM(
            'needs_review',
            'submitted',
            'additional_documents_needed',
            'waiting_to_apply',
            'applied',
            'offer_received',
            'payment_pending',
            'payment_approval',
            'ready_for_approval',
            'approved',
            'rejected'
        ) NOT NULL DEFAULT 'needs_review'");
    }
};
