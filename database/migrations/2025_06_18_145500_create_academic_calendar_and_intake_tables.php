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
        // Academic Terms (for OBU semester-based programmes)
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Semester 1 2025", "Academic Year 2024-2025"
            $table->string('term_type'); // 'semester', 'trimester', 'quarter', 'academic_year'
            $table->integer('term_number')->nullable(); // 1, 2, 3 for semesters/trimesters
            $table->integer('academic_year'); // 2024, 2025
            
            $table->date('start_date');
            $table->date('end_date');
            $table->date('enrollment_open_date')->nullable();
            $table->date('enrollment_close_date')->nullable();
            $table->date('late_enrollment_close_date')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_current')->default(false);
            
            $table->json('term_metadata')->nullable(); // Additional term-specific data
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            $table->index(['academic_year', 'term_number']);
            $table->index(['start_date', 'end_date']);
            $table->index('is_current');
        });
        
        // Programme Intakes (for cohort-based programmes)
        Schema::create('programme_intakes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('programme_id')->unsigned();
            $table->string('intake_name'); // e.g., "September 2025 Intake", "Q1 2025"
            
            $table->date('intake_date');
            $table->date('application_open_date')->nullable();
            $table->date('application_close_date')->nullable();
            $table->date('enrollment_deadline')->nullable();
            
            $table->integer('max_students')->nullable();
            $table->integer('current_enrollments')->default(0);
            $table->integer('confirmed_enrollments')->default(0);
            
            $table->enum('status', ['planning', 'open', 'closed', 'full', 'cancelled', 'completed'])
                  ->default('planning');
            
            // Programme delivery dates
            $table->date('programme_start_date');
            $table->date('programme_end_date')->nullable();
            
            // Integration with cohorts
            $table->bigInteger('cohort_id')->unsigned()->nullable();
            $table->bigInteger('academic_term_id')->unsigned()->nullable();
            
            $table->json('intake_metadata')->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            
            $table->timestamps();
            
            $table->foreign('programme_id')->references('id')->on('programmes')->onDelete('cascade');
            $table->foreign('cohort_id')->references('id')->on('cohorts')->onDelete('set null');
            $table->foreign('academic_term_id')->references('id')->on('academic_terms')->onDelete('set null');
            
            $table->index(['programme_id', 'status']);
            $table->index('intake_date');
            $table->index('status');
        });
        
        // Link module instances to academic terms
        Schema::table('module_instances', function (Blueprint $table) {
            $table->foreign('academic_term_id')
                  ->references('id')
                  ->on('academic_terms')
                  ->onDelete('set null');
            
            $table->index('academic_term_id');
        });
        
        // Academic Calendar Events (holidays, important dates)
        Schema::create('academic_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('academic_term_id')->unsigned()->nullable();
            $table->string('event_name');
            $table->string('event_type'); // 'holiday', 'deadline', 'exam_period', 'break', 'other'
            
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            $table->boolean('affects_all_programmes')->default(true);
            $table->json('affected_programme_ids')->nullable(); // Programme IDs if not all
            
            $table->text('description')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->boolean('blocks_assessments')->default(false);
            
            $table->timestamps();
            
            $table->foreign('academic_term_id')->references('id')->on('academic_terms')->onDelete('cascade');
            
            $table->index(['start_date', 'end_date']);
            $table->index('event_type');
            $table->index('affects_all_programmes');
        });
        
        // Intake Applications/Expressions of Interest
        Schema::create('intake_applications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('programme_intake_id')->unsigned();
            $table->bigInteger('student_id')->unsigned();
            
            $table->date('application_date');
            $table->enum('application_status', [
                'submitted', 
                'under_review', 
                'accepted', 
                'conditionally_accepted', 
                'rejected', 
                'withdrawn'
            ])->default('submitted');
            
            $table->json('application_data')->nullable(); // Store application form data
            $table->text('requirements_status')->nullable(); // Track requirement completion
            $table->text('staff_notes')->nullable();
            
            $table->date('decision_date')->nullable();
            $table->bigInteger('reviewed_by')->unsigned()->nullable();
            
            $table->timestamps();
            
            $table->foreign('programme_intake_id')->references('id')->on('programme_intakes')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['programme_intake_id', 'student_id']);
            $table->index('application_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intake_applications');
        Schema::dropIfExists('academic_calendar_events');
        
        Schema::table('module_instances', function (Blueprint $table) {
            $table->dropForeign(['academic_term_id']);
            $table->dropIndex(['academic_term_id']);
        });
        
        Schema::dropIfExists('programme_intakes');
        Schema::dropIfExists('academic_terms');
    }
};