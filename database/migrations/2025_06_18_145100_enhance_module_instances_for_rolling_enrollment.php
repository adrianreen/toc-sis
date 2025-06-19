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
        Schema::table('module_instances', function (Blueprint $table) {
            // Make cohort_id nullable to support rolling and standalone instances
            $table->bigInteger('cohort_id')->unsigned()->nullable()->change();
            
            // Instance type to distinguish between different enrollment patterns
            $table->enum('instance_type', ['cohort', 'rolling', 'academic_term', 'standalone'])
                  ->default('cohort')
                  ->after('cohort_id');
            
            // Rolling enrollment capacity management
            $table->integer('max_enrollments')->nullable()->after('instance_type');
            $table->integer('current_enrollments')->default(0)->after('max_enrollments');
            
            // Flexible enrollment windows
            $table->date('enrollment_open_date')->nullable()->after('current_enrollments');
            $table->date('enrollment_close_date')->nullable()->after('enrollment_open_date');
            
            // Academic term support (for OBU programmes)
            $table->bigInteger('academic_term_id')->unsigned()->nullable()->after('enrollment_close_date');
            
            // Individual pacing for rolling instances
            $table->boolean('self_paced')->default(false)->after('academic_term_id');
            $table->integer('flexible_duration_weeks')->nullable()->after('self_paced');
            
            // Instance-specific configuration overrides
            $table->json('instance_config')->nullable()->after('flexible_duration_weeks');
            
            // Status tracking for rolling instances
            $table->enum('enrollment_status', ['open', 'closed', 'full', 'suspended'])
                  ->default('open')
                  ->after('instance_config');
        });
        
        // Add indexes for performance
        Schema::table('module_instances', function (Blueprint $table) {
            $table->index(['instance_type', 'enrollment_status'], 'mi_type_status_idx');
            $table->index(['enrollment_open_date', 'enrollment_close_date'], 'mi_enrollment_dates_idx');
            $table->index('academic_term_id', 'mi_academic_term_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_instances', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('mi_type_status_idx');
            $table->dropIndex('mi_enrollment_dates_idx');
            $table->dropIndex('mi_academic_term_idx');
            
            // Drop columns
            $table->dropColumn([
                'instance_type',
                'max_enrollments',
                'current_enrollments',
                'enrollment_open_date',
                'enrollment_close_date',
                'academic_term_id',
                'self_paced',
                'flexible_duration_weeks',
                'instance_config',
                'enrollment_status'
            ]);
            
            // Make cohort_id required again
            $table->bigInteger('cohort_id')->unsigned()->nullable(false)->change();
        });
    }
};