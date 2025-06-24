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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // assessment_due, grade_released, approval_required, announcement
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable(); // Link to relevant page
            $table->json('data')->nullable(); // Additional data (assessment_id, etc.)
            $table->boolean('is_read')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_for')->nullable(); // For future scheduling
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'scheduled_for']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
