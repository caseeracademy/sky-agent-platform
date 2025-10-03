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
        Schema::create('degrees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Bachelor, Master, PhD, Diploma, Certificate
            $table->string('slug')->unique(); // bachelor, master, phd, diploma, certificate
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0); // For ordering in UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default degrees
        DB::table('degrees')->insert([
            ['name' => 'Certificate', 'slug' => 'certificate', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Diploma', 'slug' => 'diploma', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bachelor', 'slug' => 'bachelor', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Master', 'slug' => 'master', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PhD', 'slug' => 'phd', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degrees');
    }
};
