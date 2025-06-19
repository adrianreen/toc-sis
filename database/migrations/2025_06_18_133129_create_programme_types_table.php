<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programme_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // QQI Level 5, QQI Level 6, Degree, Certificate, etc.
            $table->string('code')->unique(); // QQI5, QQI6, DEGREE, CERT
            $table->text('description')->nullable();
            
            // Default configuration template for programmes of this type
            $table->json('default_config')->nullable();
            
            // Quality framework settings
            $table->string('awarding_body')->nullable(); // QQI, Oxford Brookes, etc.
            $table->string('nfq_level')->nullable(); // National Framework of Qualifications level
            
            // Academic structure
            $table->integer('default_duration_months')->nullable();
            $table->integer('default_credit_value')->nullable();
            $table->decimal('minimum_pass_grade', 5, 2)->default(40.00);
            
            // Workflow configuration
            $table->boolean('requires_placement')->default(false);
            $table->boolean('requires_external_verification')->default(false);
            $table->boolean('supports_rolling_enrolment')->default(false);
            $table->boolean('supports_cohort_enrolment')->default(true);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programme_types');
    }
};