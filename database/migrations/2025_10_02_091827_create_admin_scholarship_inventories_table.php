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
        Schema::create('admin_scholarship_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->foreignId('degree_id')->constrained('degrees')->onDelete('cascade');
            $table->integer('application_year'); // 2025, 2026, etc.

            // Scholarship counts
            $table->decimal('total_scholarships_from_university', 10, 2)->default(0); // What university gave admin
            $table->decimal('scholarships_given_to_agents', 10, 2)->default(0); // What admin gave to agents
            $table->decimal('margin_scholarships', 10, 2)->default(0); // Admin's margin profit
            $table->decimal('unclaimed_scholarships', 10, 2)->default(0); // From incomplete agents
            $table->decimal('available_scholarships', 10, 2)->default(0); // Total available to admin

            // Tracking
            $table->integer('total_approved_applications')->default(0); // Total applications processed
            $table->integer('completed_agent_scholarships')->default(0); // Scholarships earned by agents

            // Status and actions
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->json('calculation_details')->nullable(); // Store calculation breakdown
            $table->timestamp('last_calculated_at')->nullable();

            $table->timestamps();

            // Unique constraint
            $table->unique(['university_id', 'degree_id', 'application_year'], 'admin_inventory_unique');

            // Indexes
            $table->index(['university_id', 'degree_id']);
            $table->index(['application_year', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_scholarship_inventories');
    }
};
