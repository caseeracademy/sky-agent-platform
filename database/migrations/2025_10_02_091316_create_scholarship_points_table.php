<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scholarship_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->foreignId('degree_id')->constrained('degrees')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            // Point status
            $table->enum('status', ['active', 'redeemed', 'expired'])->default('active');

            // Tracking fields
            $table->timestamp('earned_at'); // When the point was earned (application approved)
            $table->timestamp('redeemed_at')->nullable(); // When point was used for scholarship
            $table->timestamp('expires_at')->nullable(); // When point expires (Nov 30)

            // Application cycle tracking
            $table->integer('application_year'); // 2025, 2026, etc.
            $table->date('cycle_start_date'); // July 1
            $table->date('cycle_end_date'); // November 30

            $table->timestamps();

            // Indexes for performance
            $table->index(['agent_id', 'university_id', 'degree_id', 'status']);
            $table->index(['application_year', 'status']);
            $table->index(['expires_at', 'status']);

            // Ensure one point per application
            $table->unique('application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_points');
    }
};
