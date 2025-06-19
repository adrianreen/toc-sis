<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check what columns already exist and add missing ones
        $existingColumns = DB::select("SHOW COLUMNS FROM module_instances");
        $columnNames = array_column($existingColumns, 'Field');
        
        Schema::table('module_instances', function (Blueprint $table) use ($columnNames) {
            // Make cohort_id nullable if not already
            if (in_array('cohort_id', $columnNames)) {
                $table->bigInteger('cohort_id')->unsigned()->nullable()->change();
            }
            
            // Add missing columns only if they don't exist
            if (!in_array('instance_type', $columnNames)) {
                $table->enum('instance_type', ['cohort', 'rolling', 'academic_term', 'standalone'])
                      ->default('cohort')
                      ->after('cohort_id');
            }
            
            if (!in_array('max_enrollments', $columnNames)) {
                $table->integer('max_enrollments')->nullable()->after('instance_type');
            }
            
            if (!in_array('current_enrollments', $columnNames)) {
                $table->integer('current_enrollments')->default(0)->after('max_enrollments');
            }
            
            if (!in_array('enrollment_open_date', $columnNames)) {
                $table->date('enrollment_open_date')->nullable()->after('current_enrollments');
            }
            
            if (!in_array('enrollment_close_date', $columnNames)) {
                $table->date('enrollment_close_date')->nullable()->after('enrollment_open_date');
            }
            
            if (!in_array('academic_term_id', $columnNames)) {
                $table->bigInteger('academic_term_id')->unsigned()->nullable()->after('enrollment_close_date');
            }
            
            if (!in_array('self_paced', $columnNames)) {
                $table->boolean('self_paced')->default(false)->after('academic_term_id');
            }
            
            if (!in_array('flexible_duration_weeks', $columnNames)) {
                $table->integer('flexible_duration_weeks')->nullable()->after('self_paced');
            }
            
            if (!in_array('instance_config', $columnNames)) {
                $table->json('instance_config')->nullable()->after('flexible_duration_weeks');
            }
            
            if (!in_array('enrollment_status', $columnNames)) {
                $table->enum('enrollment_status', ['open', 'closed', 'full', 'suspended'])
                      ->default('open')
                      ->after('instance_config');
            }
        });
        
        // Add indexes that don't exist
        try {
            DB::statement('ALTER TABLE module_instances ADD INDEX mi_type_status_idx (instance_type, enrollment_status)');
        } catch (Exception $e) {
            // Index might already exist
        }
        
        try {
            DB::statement('ALTER TABLE module_instances ADD INDEX mi_enrollment_dates_idx (enrollment_open_date, enrollment_close_date)');
        } catch (Exception $e) {
            // Index might already exist
        }
        
        try {
            DB::statement('ALTER TABLE module_instances ADD INDEX mi_academic_term_idx (academic_term_id)');
        } catch (Exception $e) {
            // Index might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        try {
            DB::statement('ALTER TABLE module_instances DROP INDEX mi_type_status_idx');
        } catch (Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE module_instances DROP INDEX mi_enrollment_dates_idx');
        } catch (Exception $e) {}
        
        try {
            DB::statement('ALTER TABLE module_instances DROP INDEX mi_academic_term_idx');
        } catch (Exception $e) {}
        
        Schema::table('module_instances', function (Blueprint $table) {
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