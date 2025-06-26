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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable(); // Rich text content (optional)
            $table->foreignId('policy_category_id')->constrained()->onDelete('cascade');
            $table->enum('scope', ['college', 'programme']); // College-wide or Programme-specific
            $table->enum('programme_type', ['all', 'elc', 'degree_obu', 'qqi'])->default('all'); // Programme types
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('file_path')->nullable(); // PDF file storage path
            $table->string('file_name')->nullable(); // Original filename
            $table->integer('file_size')->nullable(); // File size in bytes
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->integer('version')->default(1);
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['status', 'scope']);
            $table->index(['programme_type', 'status']);
            $table->index(['policy_category_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
