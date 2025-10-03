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
        Schema::table('scholarship_awards', function (Blueprint $table) {
            // Remove amount field - we're just tracking scholarship counts now
            $table->dropColumn('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scholarship_awards', function (Blueprint $table) {
            // Re-add amount field
            $table->decimal('amount', 10, 2)->after('degree_type');
        });
    }
};
