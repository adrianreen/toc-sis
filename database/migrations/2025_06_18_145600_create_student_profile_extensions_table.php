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
        // Student Profile Extensions for programme-specific data
        if (!Schema::hasTable('student_profile_extensions')) {
            Schema::create('student_profile_extensions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('programme_id')->unsigned()->nullable(); // null for global extensions
            $table->string('extension_type'); // 'placement', 'certification', 'prerequisite', 'custom'
            
            $table->string('field_name'); // e.g., 'placement_provider', 'prerequisite_cert'
            $table->string('field_label'); // Human-readable label
            $table->text('field_value')->nullable(); // The actual data
            $table->string('field_type')->default('text'); // text, file, date, boolean, select
            $table->json('field_options')->nullable(); // For select fields, validation rules, etc.
            
            $table->boolean('is_required')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->date('verified_date')->nullable();
            $table->bigInteger('verified_by')->unsigned()->nullable();
            
            $table->date('expiry_date')->nullable(); // For time-sensitive certifications
            $table->enum('status', ['pending', 'complete', 'expired', 'not_applicable'])->default('pending');
            
            $table->text('staff_notes')->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('programme_id')->references('id')->on('programmes')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['student_id', 'programme_id']);
            $table->index(['extension_type', 'field_name']);
            $table->index('status');
            });
        }
        
        // Programme Extension Requirements (defines what extensions are needed)
        if (!Schema::hasTable('programme_extension_requirements')) {
            Schema::create('programme_extension_requirements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('programme_id')->unsigned();
            $table->string('extension_type');
            $table->string('field_name');
            $table->string('field_label');
            $table->string('field_type')->default('text');
            $table->json('field_options')->nullable();
            
            $table->boolean('is_required')->default(false);
            $table->boolean('required_for_enrollment')->default(false);
            $table->boolean('required_for_progression')->default(false);
            $table->boolean('required_for_completion')->default(false);
            
            $table->integer('display_order')->default(0);
            $table->text('description')->nullable();
            $table->text('validation_rules')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('programme_id')->references('id')->on('programmes')->onDelete('cascade');
            
            $table->index(['programme_id', 'extension_type'], 'prog_ext_req_idx');
            $table->index('is_active');
            $table->unique(['programme_id', 'field_name']);
            });
        }
        
        // Student Document Storage (for file attachments)
        if (!Schema::hasTable('student_documents')) {
            Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('profile_extension_id')->unsigned()->nullable();
            $table->string('document_type'); // 'prerequisite_cert', 'placement_agreement', 'id_verification'
            
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            
            $table->boolean('is_verified')->default(false);
            $table->date('verified_date')->nullable();
            $table->bigInteger('verified_by')->unsigned()->nullable();
            
            $table->date('expiry_date')->nullable();
            $table->text('verification_notes')->nullable();
            
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('profile_extension_id')->references('id')->on('student_profile_extensions')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['student_id', 'document_type']);
            $table->index('is_verified');
            });
        }
        
        // Add some default extension types via seeder data structure
        // Common extension types that will be seeded:
        // - ELC Programmes: placement_provider, placement_start_date, placement_end_date, garda_vetting
        // - OBU Programmes: prerequisite_degree, english_proficiency, portfolio_submission
        // - QQI Programmes: prerequisite_certification, work_experience_verification
        // - All: emergency_contact, special_requirements, accessibility_needs
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('programme_extension_requirements');
        Schema::dropIfExists('student_profile_extensions');
    }
};