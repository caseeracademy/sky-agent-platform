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
        Schema::table('universities', function (Blueprint $table) {
            // Scholarship eligibility requirements by degree type
            $table->json('scholarship_requirements')->nullable()->after('is_active');

            // Example structure:
            // {
            //   "Certificate": {"min_students": 5, "scholarship_amount": 1000},
            //   "Diploma": {"min_students": 8, "scholarship_amount": 1500},
            //   "Bachelor": {"min_students": 10, "scholarship_amount": 2000},
            //   "Master": {"min_students": 15, "scholarship_amount": 3000},
            //   "PhD": {"min_students": 20, "scholarship_amount": 5000}
            // }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn('scholarship_requirements');
        });
    }
};
