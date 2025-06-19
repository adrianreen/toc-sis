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
        Schema::table('modules', function (Blueprint $table) {
            // Standalone enrollment support
            $table->boolean('standalone_enrollable')->default(false)->after('description');
            
            // QQI award type for certification tracking
            $table->enum('qqi_award_type', ['major', 'minor', 'supplemental', 'special_purpose'])
                  ->nullable()
                  ->after('standalone_enrollable')
                  ->comment('QQI award type for standalone modules');
            
            // Prerequisites as JSON array of module IDs
            $table->json('prerequisite_modules')->nullable()->after('qqi_award_type');
            
            // Individual pacing options for rolling enrollment
            $table->integer('typical_duration_weeks')->nullable()->after('prerequisite_modules');
            $table->integer('max_duration_weeks')->nullable()->after('typical_duration_weeks');
            
            // QQI-specific fields for standalone modules
            $table->string('qqi_component_code', 50)->nullable()->after('max_duration_weeks');
            $table->decimal('qqi_credit_value', 5, 2)->nullable()->after('qqi_component_code');
            
            // Enrollment constraints
            $table->boolean('requires_prerequisite_verification')->default(false)->after('qqi_credit_value');
            $table->text('enrollment_requirements')->nullable()->after('requires_prerequisite_verification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'standalone_enrollable',
                'qqi_award_type',
                'prerequisite_modules',
                'typical_duration_weeks',
                'max_duration_weeks',
                'qqi_component_code',
                'qqi_credit_value',
                'requires_prerequisite_verification',
                'enrollment_requirements'
            ]);
        });
    }
};