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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['earned', 'requested', 'paid'])->default('earned');
            $table->timestamp('earned_at')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['agent_id', 'status']);
            $table->index(['application_id']);
            
            // Ensure one commission per application
            $table->unique('application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
