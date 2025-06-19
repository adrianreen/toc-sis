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
        Schema::table('programme_types', function (Blueprint $table) {
            // Add foreign key columns for default configurations
            $table->bigInteger('default_grading_scheme_id')->unsigned()->nullable()->after('is_active');
            $table->bigInteger('default_assessment_strategy_id')->unsigned()->nullable()->after('default_grading_scheme_id');
            $table->bigInteger('default_module_progression_rule_id')->unsigned()->nullable()->after('default_assessment_strategy_id');
            
            // Add foreign key constraints
            $table->foreign('default_grading_scheme_id')
                  ->references('id')
                  ->on('grading_schemes')
                  ->onDelete('set null');
                  
            $table->foreign('default_assessment_strategy_id')
                  ->references('id')
                  ->on('assessment_strategies')
                  ->onDelete('set null');
                  
            $table->foreign('default_module_progression_rule_id')
                  ->references('id')
                  ->on('module_progression_rules')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programme_types', function (Blueprint $table) {
            $table->dropForeign(['default_grading_scheme_id']);
            $table->dropForeign(['default_assessment_strategy_id']);
            $table->dropForeign(['default_module_progression_rule_id']);
            
            $table->dropColumn([
                'default_grading_scheme_id',
                'default_assessment_strategy_id',
                'default_module_progression_rule_id'
            ]);
        });
    }
};