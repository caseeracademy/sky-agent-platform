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
            // Commission type choice (money or scholarship)
            $table->enum('commission_type', ['money', 'scholarship'])->nullable()->after('status');

            // Quick review fields (reviewed_at already exists)
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')->after('commission_type');
            $table->text('review_notes')->nullable()->after('reviewed_by');

            // Track if this needs quick review
            $table->boolean('needs_review')->default(true)->after('review_notes');

            // Index for quick review queue
            $table->index(['status', 'needs_review']);
            $table->index(['reviewed_at', 'reviewed_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['status', 'needs_review']);
            $table->dropIndex(['reviewed_at', 'reviewed_by']);

            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'commission_type',
                'reviewed_by',
                'review_notes',
                'needs_review',
            ]);
        });
    }
};
