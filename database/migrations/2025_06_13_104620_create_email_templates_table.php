<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('subject');
            $table->enum('category', ['academic', 'administrative', 'system'])->default('administrative');
            $table->text('description')->nullable();
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->json('available_variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('system_template')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index('system_template');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
