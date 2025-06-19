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
        if (!Schema::hasTable('repeat_assessment_policies')) {
            Schema::create('repeat_assessment_policies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                
                // Core policy configuration
                $table->enum('timing_type', ['immediate', 'end_of_module', 'scheduled_period', 'next_intake', 'flexible'])
                      ->default('immediate');
                
                // Payment configuration
                $table->boolean('payment_required')->default(true);
                $table->decimal('default_payment_amount', 8, 2)->nullable();
                $table->boolean('payment_amount_configurable')->default(true);
                
                // Grade restrictions
                $table->decimal('grade_cap_percentage', 5, 2)->nullable();
                $table->boolean('grade_cap_configurable')->default(false);
                
                // Attempt limits
                $table->integer('max_attempts')->default(3);
                $table->boolean('max_attempts_configurable')->default(false);
                
                // Workflow configuration
                $table->json('workflow_stages')->nullable();
                $table->json('notification_schedule')->nullable();
                
                // Scheduling rules (for scheduled_period timing)
                $table->json('scheduling_rules')->nullable();
                
                // Complete policy rules as JSON for flexibility
                $table->json('policy_rules')->nullable();
                
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('timing_type');
                $table->index('is_active');
            });
        }
        
        // Add columns and foreign key constraints to existing tables
        Schema::table('programmes', function (Blueprint $table) {
            if (!Schema::hasColumn('programmes', 'repeat_assessment_policy_id')) {
                $table->bigInteger('repeat_assessment_policy_id')->unsigned()->nullable()->after('config_overrides');
                $table->foreign('repeat_assessment_policy_id')
                      ->references('id')
                      ->on('repeat_assessment_policies')
                      ->onDelete('set null');
            }
        });
        
        Schema::table('programme_types', function (Blueprint $table) {
            if (!Schema::hasColumn('programme_types', 'default_repeat_policy_id')) {
                $table->bigInteger('default_repeat_policy_id')->unsigned()->nullable()->after('is_active');
                $table->foreign('default_repeat_policy_id')
                      ->references('id')
                      ->on('repeat_assessment_policies')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropForeign(['repeat_assessment_policy_id']);
        });
        
        Schema::table('programme_types', function (Blueprint $table) {
            $table->dropForeign(['default_repeat_policy_id']);
        });
        
        Schema::dropIfExists('repeat_assessment_policies');
    }
};