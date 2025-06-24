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
        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type', 50); // e.g., 'student_performance', 'programme_completion'
            $table->string('metric_key', 100); // e.g., 'programme_1_completion_rate'
            $table->json('metric_data'); // Flexible data storage
            $table->string('period_type', 20); // 'daily', 'weekly', 'monthly', 'yearly'
            $table->date('period_date'); // The date this metric represents
            $table->timestamp('calculated_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['metric_type', 'period_type', 'period_date']);
            $table->index(['metric_key', 'period_date']);
            $table->index('calculated_at');
        });

        Schema::create('analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key', 255)->unique();
            $table->json('cache_data');
            $table->timestamp('expires_at');
            $table->timestamps();

            // Indexes for performance
            $table->index('cache_key');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_cache');
        Schema::dropIfExists('analytics_metrics');
    }
};
