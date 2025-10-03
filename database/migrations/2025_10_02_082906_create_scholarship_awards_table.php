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
        Schema::create('scholarship_awards', function (Blueprint $table) {
            $table->id();
            $table->string('award_number')->unique(); // e.g., SCH-2025-ABC123
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->string('degree_type'); // Bachelor, Master, PhD, etc.
            $table->decimal('amount', 10, 2); // Scholarship amount
            $table->integer('qualifying_applications_count'); // Number of applications that qualified
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('awarded_at'); // When the scholarship was awarded
            $table->timestamp('approved_at')->nullable(); // When admin approved payment
            $table->timestamp('paid_at')->nullable(); // When scholarship was paid
            $table->timestamps();

            // Indexes for performance
            $table->index(['agent_id', 'status']);
            $table->index(['university_id', 'degree_type']);
            $table->index('award_number');

            // Prevent duplicate awards for same agent/university/degree combination
            $table->unique(['agent_id', 'university_id', 'degree_type'], 'unique_agent_university_degree');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_awards');
    }
};
