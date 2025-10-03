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
        Schema::table('system_scholarship_awards', function (Blueprint $table) {
            $table->integer('application_year')->after('degree_type')->default(2025);
            $table->integer('total_applications_count')->after('qualifying_agent_scholarships_count')->default(0);
            $table->decimal('system_scholarships_earned', 8, 2)->after('total_applications_count')->default(0);
            $table->decimal('margin_scholarships', 8, 2)->after('system_scholarships_earned')->default(0);
            $table->decimal('unclaimed_scholarships', 8, 2)->after('margin_scholarships')->default(0);
            $table->json('calculation_details')->after('unclaimed_scholarships')->nullable();
            $table->timestamp('last_updated_at')->after('paid_at')->nullable();

            $table->index(['application_year', 'status'], 'sys_awards_year_status_idx');
            $table->index(['university_id', 'degree_type', 'application_year'], 'sys_awards_uni_degree_year_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_scholarship_awards', function (Blueprint $table) {
            $table->dropIndex('sys_awards_year_status_idx');
            $table->dropIndex('sys_awards_uni_degree_year_idx');

            $table->dropColumn([
                'application_year',
                'total_applications_count',
                'system_scholarships_earned',
                'margin_scholarships',
                'unclaimed_scholarships',
                'calculation_details',
                'last_updated_at',
            ]);
        });
    }
};
