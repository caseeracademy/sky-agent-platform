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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', [
                'pending',
                'submitted', 
                'under_review',
                'additional_documents_required',
                'approved',
                'rejected',
                'enrolled',
                'cancelled'
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('documents')->nullable(); // Store document file paths
            $table->date('intake_date')->nullable();
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->boolean('commission_paid')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['agent_id', 'status']);
            $table->index(['assigned_admin_id', 'status']);
            $table->index('application_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
