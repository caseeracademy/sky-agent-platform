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
        Schema::table('programs', function (Blueprint $table) {
            // Add degree_id foreign key
            $table->foreignId('degree_id')->nullable()->after('university_id')->constrained('degrees')->onDelete('cascade');

            // We'll keep degree_type for now during transition, but mark it as deprecated
            // Later we can remove it once we migrate all data
        });

        // Migrate existing degree_type data to degree_id
        $degreeMap = [
            'Certificate' => 1,
            'Diploma' => 2,
            'Bachelor' => 3,
            'Master' => 4,
            'PhD' => 5,
        ];

        foreach ($degreeMap as $degreeType => $degreeId) {
            DB::table('programs')
                ->where('degree_type', $degreeType)
                ->update(['degree_id' => $degreeId]);
        }

        // Make degree_id required after migration
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('degree_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['degree_id']);
            $table->dropColumn('degree_id');
        });
    }
};
