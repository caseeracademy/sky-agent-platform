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
        Schema::table('students', function (Blueprint $table) {
            // Split name into separate fields
            $table->string('first_name')->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->after('middle_name');

            // Add new required fields
            $table->string('passport_number')->after('last_name');
            $table->string('mothers_name')->after('passport_number');
            $table->string('nationality')->after('mothers_name');

            // Keep the old 'name' field for backward compatibility but make it nullable
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'passport_number',
                'mothers_name',
                'nationality',
            ]);

            // Restore name field as required
            $table->string('name')->nullable(false)->change();
        });
    }
};
