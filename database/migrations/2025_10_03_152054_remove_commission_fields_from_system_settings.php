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
        Schema::table('system_settings', function (Blueprint $table) {
            // Check if columns exist before dropping
            if (Schema::hasColumn('system_settings', 'default_commission_rate')) {
                $table->dropColumn('default_commission_rate');
            }
            if (Schema::hasColumn('system_settings', 'scholarship_points_per_application')) {
                $table->dropColumn('scholarship_points_per_application');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->decimal('default_commission_rate', 5, 2)->default(10.00)->after('company_logo_path');
            $table->integer('scholarship_points_per_application')->default(1)->after('default_commission_rate');
        });
    }
};
