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
        // QQI Certification Batches (4 times per year)
        if (!Schema::hasTable('qqi_certification_batches')) {
            Schema::create('qqi_certification_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_name'); // e.g., "2025 Q1 Certification"
            $table->date('submission_deadline');
            $table->date('qbs_submission_date')->nullable();
            $table->date('certificates_received_date')->nullable();
            $table->date('certificates_posted_date')->nullable();
            
            $table->enum('status', ['preparation', 'submitted_to_qbs', 'certificates_received', 'certificates_posted', 'completed'])
                  ->default('preparation');
            
            $table->integer('total_students')->default(0);
            $table->integer('processed_students')->default(0);
            
            $table->text('notes')->nullable();
            $table->json('batch_metadata')->nullable(); // Additional QBS submission data
            
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('submitted_by')->unsigned()->nullable();
            
            $table->timestamps();
            
            $table->index('status');
            $table->index('submission_deadline');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('submitted_by')->references('id')->on('users');
            });
        }
        
        // Individual student certifications
        if (!Schema::hasTable('qqi_certifications')) {
            Schema::create('qqi_certifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('programme_id')->unsigned()->nullable(); // null for standalone modules
            $table->bigInteger('module_id')->unsigned()->nullable(); // for standalone module certifications
            $table->bigInteger('certification_batch_id')->unsigned()->nullable();
            
            $table->string('certification_type'); // 'programme', 'standalone_module', 'component'
            $table->string('qqi_award_type'); // 'major', 'minor', 'supplemental', 'special_purpose'
            $table->string('qqi_programme_code', 50)->nullable();
            $table->string('qqi_component_code', 50)->nullable();
            
            // Completion tracking
            $table->date('completion_date');
            $table->decimal('final_grade_percentage', 5, 2)->nullable();
            $table->string('grade_classification', 50)->nullable(); // Pass, Merit, Distinction
            
            // Certification status tracking
            $table->enum('certification_status', [
                'pending_review',
                'ready_for_submission', 
                'submitted_to_qbs',
                'certificate_received',
                'certificate_posted',
                'completed',
                'on_hold',
                'rejected'
            ])->default('pending_review');
            
            $table->date('status_updated_date')->nullable();
            $table->bigInteger('status_updated_by')->unsigned()->nullable();
            
            // QBS submission data
            $table->json('qbs_submission_data')->nullable();
            $table->string('qbs_reference_number', 100)->nullable();
            
            // Certificate delivery tracking
            $table->string('certificate_number', 100)->nullable();
            $table->date('certificate_issued_date')->nullable();
            $table->enum('delivery_method', ['post', 'collection', 'email'])->nullable();
            $table->text('delivery_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->boolean('delivery_confirmed')->default(false);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['student_id', 'certification_type']);
            $table->index('certification_status');
            $table->index('completion_date');
            $table->index('certification_batch_id');
            
            // Foreign keys
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('programme_id')->references('id')->on('programmes')->onDelete('set null');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('set null');
            $table->foreign('certification_batch_id')->references('id')->on('qqi_certification_batches')->onDelete('set null');
            $table->foreign('status_updated_by')->references('id')->on('users')->onDelete('set null');
            });
        }
        
        // QQI Certification Components (for programmes with multiple components)
        if (!Schema::hasTable('qqi_certification_components')) {
            Schema::create('qqi_certification_components', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('qqi_certification_id')->unsigned();
            $table->bigInteger('module_id')->unsigned();
            $table->string('component_code', 50);
            $table->decimal('component_grade', 5, 2)->nullable();
            $table->string('component_result', 50)->nullable(); // Pass, Merit, Distinction, Fail
            $table->date('component_completion_date');
            
            $table->timestamps();
            
            $table->foreign('qqi_certification_id')->references('id')->on('qqi_certifications')->onDelete('cascade');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            
            $table->unique(['qqi_certification_id', 'module_id'], 'qqi_cert_comp_unique');
            });
        }
        
        // Add indexes for performance
        if (Schema::hasTable('qqi_certification_batches')) {
            Schema::table('qqi_certification_batches', function (Blueprint $table) {
                try {
                    $table->index(['status', 'submission_deadline']);
                } catch (\Exception $e) {
                    // Index might already exist
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qqi_certification_components');
        Schema::dropIfExists('qqi_certifications');
        Schema::dropIfExists('qqi_certification_batches');
    }
};