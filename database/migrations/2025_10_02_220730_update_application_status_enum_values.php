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
        // Update status enum to include all new statuses
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
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
            'rejected',
            'draft',
            'under_review',
            'additional_documents_required',
            'enrolled',
            'cancelled',
            'pending'
        ) NOT NULL DEFAULT 'needs_review'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum values
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
            'draft',
            'pending',
            'submitted',
            'under_review',
            'additional_documents_required',
            'approved',
            'rejected',
            'enrolled',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");
    }
};
