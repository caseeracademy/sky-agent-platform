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
        Schema::dropIfExists('commission_payout');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('commission_payout', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payout_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['commission_id', 'payout_id']);
        });
    }
};
