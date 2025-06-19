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
        // Enhance programmes table
        Schema::table('programmes', function (Blueprint $table) {
            // Delivery pattern for archetype-specific workflows
            $table->enum('delivery_pattern', ['sequential', 'concurrent', 'semester_based', 'flexible'])
                  ->default('sequential')
                  ->after('enrolment_type');
            
            // Configuration overrides as JSON (can override programme type defaults)
            $table->json('config_overrides')->nullable()->after('delivery_pattern');
            
            // Intake scheduling for cohort-based programmes
            $table->json('intake_schedule')->nullable()->after('config_overrides');
            
            // Academic calendar configuration
            $table->boolean('uses_academic_calendar')->default(false)->after('intake_schedule');
            $table->date('academic_year_start')->nullable()->after('uses_academic_calendar');
            $table->date('academic_year_end')->nullable()->after('academic_year_start');
            
            // Programme-specific policies
            $table->bigInteger('repeat_assessment_policy_id')->unsigned()->nullable()->after('module_progression_rule_id');
            
            // Certification tracking
            $table->boolean('requires_qqi_certification')->default(false)->after('repeat_assessment_policy_id');
            $table->string('qqi_programme_code', 50)->nullable()->after('requires_qqi_certification');
        });
        
        // Enhance programme_types table with archetype configuration
        Schema::table('programme_types', function (Blueprint $table) {
            // Complete archetype configuration as JSON
            $table->json('archetype_config')->nullable()->after('external_examiner_required');
            
            // Default repeat policy
            $table->bigInteger('default_repeat_policy_id')->unsigned()->nullable()->after('archetype_config');
            
            // QQI-specific defaults
            $table->boolean('default_qqi_certification')->default(false)->after('default_repeat_policy_id');
            $table->string('awarding_body_code', 20)->nullable()->after('default_qqi_certification');
        });
        
        // Enhance programme_modules table for better module relationships
        Schema::table('programme_modules', function (Blueprint $table) {
            // Pillar module support (for ELC programmes)
            $table->boolean('is_pillar')->default(false)->after('is_core');
            
            // Semester delivery (for OBU programmes) 
            $table->integer('delivery_semester')->nullable()->after('is_pillar');
            
            // Year level (for multi-year programmes)
            $table->integer('year_level')->default(1)->after('delivery_semester');
            
            // Module timing constraints
            $table->integer('start_week_offset')->nullable()->after('year_level');
            $table->integer('duration_weeks')->nullable()->after('start_week_offset');
        });
        
        // Add performance indexes
        Schema::table('programmes', function (Blueprint $table) {
            $table->index('delivery_pattern');
            $table->index(['uses_academic_calendar', 'academic_year_start']);
            $table->index('requires_qqi_certification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['delivery_pattern']);
            $table->dropIndex(['uses_academic_calendar', 'academic_year_start']);
            $table->dropIndex(['requires_qqi_certification']);
            
            // Drop columns
            $table->dropColumn([
                'delivery_pattern',
                'config_overrides',
                'intake_schedule',
                'uses_academic_calendar', 
                'academic_year_start',
                'academic_year_end',
                'repeat_assessment_policy_id',
                'requires_qqi_certification',
                'qqi_programme_code'
            ]);
        });
        
        Schema::table('programme_types', function (Blueprint $table) {
            $table->dropColumn([
                'archetype_config',
                'default_repeat_policy_id',
                'default_qqi_certification',
                'awarding_body_code'
            ]);
        });
        
        Schema::table('programme_modules', function (Blueprint $table) {
            $table->dropColumn([
                'is_pillar',
                'delivery_semester',
                'year_level',
                'start_week_offset',
                'duration_weeks'
            ]);
        });
    }
};