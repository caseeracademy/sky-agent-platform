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
            // Payment related fields
            $table->string('payment_receipt_path')->nullable()->after('commission_paid');
            $table->timestamp('payment_receipt_uploaded_at')->nullable()->after('payment_receipt_path');
            $table->unsignedBigInteger('payment_receipt_uploaded_by')->nullable()->after('payment_receipt_uploaded_at');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_receipt_uploaded_by');
            $table->unsignedBigInteger('payment_verified_by')->nullable()->after('payment_verified_at');

            // Offer letter fields
            $table->string('offer_letter_path')->nullable()->after('payment_verified_by');
            $table->timestamp('offer_letter_sent_at')->nullable()->after('offer_letter_path');
            $table->date('university_response_date')->nullable()->after('offer_letter_sent_at');

            // Rejection fields
            $table->text('rejection_reason')->nullable()->after('university_response_date');
            $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_at');

            // Foreign keys
            $table->foreign('payment_receipt_uploaded_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('payment_verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['payment_receipt_uploaded_by']);
            $table->dropForeign(['payment_verified_by']);
            $table->dropForeign(['rejected_by']);

            $table->dropColumn([
                'payment_receipt_path',
                'payment_receipt_uploaded_at',
                'payment_receipt_uploaded_by',
                'payment_verified_at',
                'payment_verified_by',
                'offer_letter_path',
                'offer_letter_sent_at',
                'university_response_date',
                'rejection_reason',
                'rejected_at',
                'rejected_by',
            ]);
        });
    }
};
