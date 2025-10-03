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
        Schema::create('application_cycles', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique(); // 2025, 2026, etc.
            $table->date('start_date'); // July 1
            $table->date('end_date'); // November 30
            $table->enum('status', ['upcoming', 'active', 'closed', 'archived'])->default('upcoming');
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['year', 'status']);
            $table->index('status');
        });

        // Insert current and next cycles
        $currentYear = now()->year;
        $cycles = [
            [
                'year' => $currentYear,
                'start_date' => "{$currentYear}-07-01",
                'end_date' => "{$currentYear}-11-30",
                'status' => now()->between("{$currentYear}-07-01", "{$currentYear}-11-30") ? 'active' : (now()->isAfter("{$currentYear}-11-30") ? 'closed' : 'upcoming'),
                'description' => "Application cycle for {$currentYear}",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'year' => $currentYear + 1,
                'start_date' => ($currentYear + 1).'-07-01',
                'end_date' => ($currentYear + 1).'-11-30',
                'status' => 'upcoming',
                'description' => 'Application cycle for '.($currentYear + 1),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('application_cycles')->insert($cycles);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_cycles');
    }
};
