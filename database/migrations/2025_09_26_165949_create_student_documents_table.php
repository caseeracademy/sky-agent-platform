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
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Document name/title
            $table->string('type'); // e.g., 'passport', 'certificate', 'transcript', 'other'
            $table->string('file_path'); // Storage path
            $table->string('file_name'); // Original filename
            $table->string('mime_type'); // File mime type
            $table->integer('file_size'); // File size in bytes
            $table->text('description')->nullable(); // Optional description
            $table->foreignId('uploaded_by')->constrained('users'); // Who uploaded it
            $table->timestamps();
            
            $table->index(['student_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
