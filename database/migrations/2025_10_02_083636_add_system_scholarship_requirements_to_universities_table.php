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
            // System scholarship requirements by degree type
            $table->json('system_scholarship_requirements')->nullable()->after('scholarship_requirements');

            // Example structure:
            // {
            //   "Certificate": {"min_agent_scholarships": 3, "system_scholarship_amount": 500},
            //   "Diploma": {"min_agent_scholarships": 4, "system_scholarship_amount": 750},
            //   "Bachelor": {"min_agent_scholarships": 4, "system_scholarship_amount": 1000},
            //   "Master": {"min_agent_scholarships": 5, "system_scholarship_amount": 1500},
            //   "PhD": {"min_agent_scholarships": 6, "system_scholarship_amount": 2000}
            // }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn('system_scholarship_requirements');
        });
    }
};
