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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('agent_staff')->after('email');
            $table->foreignId('parent_agent_id')->nullable()->constrained('users')->onDelete('set null')->after('role');
            $table->boolean('is_active')->default(true)->after('parent_agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_agent_id']);
            $table->dropColumn(['role', 'parent_agent_id', 'is_active']);
        });
    }
};
