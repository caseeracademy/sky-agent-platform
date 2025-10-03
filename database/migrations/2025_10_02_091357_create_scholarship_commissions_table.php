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
        Schema::create('scholarship_commissions', function (Blueprint $table) {
            $table->id();
            $table->string('commission_number')->unique(); // SC-2025-001
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->foreignId('degree_id')->constrained('degrees')->onDelete('cascade');

            // Commission details
            $table->integer('qualifying_points_count')->default(5); // Usually 5 points = 1 scholarship
            $table->enum('status', ['earned', 'used', 'expired'])->default('earned');

            // Tracking
            $table->timestamp('earned_at');
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_in_application_id')->nullable()->constrained('applications')->onDelete('set null');

            // Application cycle
            $table->integer('application_year');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['agent_id', 'university_id', 'degree_id']);
            $table->index(['status', 'application_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_commissions');
    }
};
