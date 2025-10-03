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
        Schema::create('system_scholarship_awards', function (Blueprint $table) {
            $table->id();
            $table->string('award_number')->unique(); // e.g., SYS-2025-ABC123
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');
            $table->string('degree_type'); // Bachelor, Master, PhD, etc.
            $table->decimal('amount', 10, 2); // System scholarship amount
            $table->integer('qualifying_agent_scholarships_count'); // Number of agent scholarships that qualified this
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('awarded_at'); // When the system scholarship was awarded
            $table->timestamp('approved_at')->nullable(); // When admin approved payment
            $table->timestamp('paid_at')->nullable(); // When scholarship was paid
            $table->timestamps();

            // Indexes for performance
            $table->index(['university_id', 'degree_type']);
            $table->index(['status']);
            $table->index('award_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_scholarship_awards');
    }
};
