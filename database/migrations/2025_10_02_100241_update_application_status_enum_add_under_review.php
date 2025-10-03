<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any invalid statuses to 'submitted'
        DB::statement("UPDATE applications SET status = 'submitted' WHERE status NOT IN ('pending', 'submitted', 'approved', 'rejected')");

        // Update the enum to include 'under_review' status
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('pending', 'submitted', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'under_review' from enum
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('pending', 'submitted', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
