<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('sent_by')->constrained('users');
            $table->string('recipient_email');
            $table->string('subject');
            $table->json('variables_used')->nullable();
            $table->enum('delivery_status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->boolean('has_attachment')->default(false);
            $table->text('attachment_info')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'sent_at']);
            $table->index(['delivery_status', 'sent_at']);
            $table->index('email_template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};