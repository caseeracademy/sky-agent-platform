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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('phone_number')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->timestamps();

            // Ensure email is unique per agent
            $table->unique(['agent_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
