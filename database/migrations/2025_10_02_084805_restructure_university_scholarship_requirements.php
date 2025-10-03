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
            // Remove the old system_scholarship_requirements field since we're combining everything
            $table->dropColumn('system_scholarship_requirements');
        });

        // Update existing scholarship_requirements to new structure
        // The new structure will be:
        // {
        //   "Bachelor": {
        //     "min_students": 5,
        //     "min_agent_scholarships": 4
        //   },
        //   "Master": {
        //     "min_students": 8,
        //     "min_agent_scholarships": 3
        //   }
        // }

        // Note: Existing data will need to be manually updated or will be empty
        // This is acceptable since we're removing the money concept entirely
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            // Re-add the system_scholarship_requirements field
            $table->json('system_scholarship_requirements')->nullable()->after('scholarship_requirements');
        });
    }
};
