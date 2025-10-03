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
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('converted_from_scholarship')->default(false)->after('commission_type');
            $table->unsignedBigInteger('scholarship_commission_id')->nullable()->after('converted_from_scholarship');
            $table->foreign('scholarship_commission_id')->references('id')->on('scholarship_commissions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['scholarship_commission_id']);
            $table->dropColumn(['converted_from_scholarship', 'scholarship_commission_id']);
        });
    }
};
