<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('commission_payout');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('commissions');
    }

    public function down(): void
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
            $table->unique('application_id');
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('commission_payout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained('commissions')->cascadeOnDelete();
            $table->foreignId('payout_id')->constrained('payouts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['commission_id', 'payout_id']);
        });
    }
};
