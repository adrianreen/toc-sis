<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', [
                'rpl_proof',
                'transcript',
                'certificate',
                'identity_document',
                'qualification_certificate',
                'other',
            ])->index();
            $table->string('title')->nullable(); // User-friendly title
            $table->string('original_filename');
            $table->string('stored_filename'); // System generated filename
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->string('file_hash', 64)->nullable(); // SHA-256 for integrity
            $table->enum('status', ['uploaded', 'verified', 'rejected', 'archived'])->default('uploaded');
            $table->text('description')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('metadata')->nullable(); // Additional file metadata as JSON

            // Tracking fields
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('uploaded_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();

            // Standard Laravel fields
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['student_id', 'document_type']);
            $table->index(['status', 'uploaded_at']);
            $table->index('file_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
