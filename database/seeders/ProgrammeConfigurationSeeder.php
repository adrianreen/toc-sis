<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgrammeType;
use App\Models\GradingScheme;
use App\Models\AssessmentStrategy;
use App\Models\ModuleProgressionRule;
use App\Models\Programme;

class ProgrammeConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        // Create programme types
        $this->createProgrammeTypes();
        
        // Create grading schemes
        $this->createGradingSchemes();
        
        // Create assessment strategies
        $this->createAssessmentStrategies();
        
        // Create module progression rules
        $this->createModuleProgressionRules();
        
        // Link programme types to their default configurations
        $this->linkProgrammeTypeDefaults();
        
        // Update existing programmes with configuration
        $this->updateExistingProgrammes();
    }
    
    private function createProgrammeTypes(): void
    {
        // QQI Level 5
        ProgrammeType::updateOrCreate(['code' => 'QQI5'], [
            'name' => 'QQI Level 5',
            'code' => 'QQI5',
            'description' => 'Quality and Qualifications Ireland Level 5 programmes',
            'awarding_body' => 'QQI',
            'nfq_level' => '5',
            'default_duration_months' => 12,
            'default_credit_value' => 120,
            'minimum_pass_grade' => 40.00,
            'requires_placement' => true,
            'requires_external_verification' => true,
            'supports_rolling_enrolment' => true,
            'supports_cohort_enrolment' => true,
            'default_config' => json_encode([
                'assessment' => [
                    'typical_components' => 3,
                    'component_types' => ['assignment', 'assignment', 'exam'],
                    'weights' => [40, 35, 25],
                ],
                'progression' => [
                    'sequential_modules' => true,
                    'placement_parallel' => true,
                ]
            ]),
        ]);

        // QQI Level 6
        ProgrammeType::updateOrCreate(['code' => 'QQI6'], [
            'name' => 'QQI Level 6',
            'code' => 'QQI6',
            'description' => 'Quality and Qualifications Ireland Level 6 programmes',
            'awarding_body' => 'QQI',
            'nfq_level' => '6',
            'default_duration_months' => 12,
            'default_credit_value' => 120,
            'minimum_pass_grade' => 40.00,
            'requires_placement' => true,
            'requires_external_verification' => true,
            'supports_rolling_enrolment' => true,
            'supports_cohort_enrolment' => true,
            'default_config' => json_encode([
                'assessment' => [
                    'typical_components' => 4,
                    'component_types' => ['assignment', 'assignment', 'project', 'exam'],
                    'weights' => [25, 25, 30, 20],
                ],
                'progression' => [
                    'sequential_modules' => true,
                    'placement_parallel' => true,
                ]
            ]),
        ]);

        // Degree Level
        ProgrammeType::updateOrCreate(['code' => 'DEGREE'], [
            'name' => 'Degree Programme',
            'code' => 'DEGREE',
            'description' => 'University degree programmes',
            'awarding_body' => 'Oxford Brookes University',
            'nfq_level' => '7',
            'default_duration_months' => 36,
            'default_credit_value' => 360,
            'minimum_pass_grade' => 40.00,
            'requires_placement' => false,
            'requires_external_verification' => true,
            'supports_rolling_enrolment' => false,
            'supports_cohort_enrolment' => true,
            'default_config' => json_encode([
                'grading' => [
                    'uses_classifications' => true,
                    'classification_boundaries' => [
                        ['min' => 70, 'max' => 100, 'grade' => 'First Class'],
                        ['min' => 60, 'max' => 69, 'grade' => 'Upper Second Class'],
                        ['min' => 50, 'max' => 59, 'grade' => 'Lower Second Class'],
                        ['min' => 40, 'max' => 49, 'grade' => 'Third Class'],
                    ],
                ],
                'assessment' => [
                    'typical_components' => 4,
                    'requires_external_examiner' => true,
                    'component_types' => ['coursework', 'assignment', 'project', 'exam'],
                    'weights' => [20, 30, 30, 20],
                ],
                'progression' => [
                    'type' => 'credit_based',
                    'requires_120_credits_per_year' => true,
                    'allows_module_compensation' => true,
                ]
            ]),
        ]);
    }
    
    private function createGradingSchemes(): void
    {
        // Standard Percentage Grading
        GradingScheme::updateOrCreate(['code' => 'PERCENTAGE'], [
            'name' => 'Standard Percentage Grading',
            'code' => 'PERCENTAGE',
            'description' => 'Traditional percentage-based grading system',
            'type' => 'percentage',
            'calculation_method' => 'weighted_average',
            'components_graded_out_of_total' => true,
            'all_components_required' => false,
            'overall_pass_threshold' => 40.00,
            'compensatory_grading_allowed' => true,
            'compensation_threshold' => 35.00,
            'display_format' => json_encode(['show_percentage' => true]),
        ]);

        // Direct Component Grading
        GradingScheme::updateOrCreate(['code' => 'DIRECT'], [
            'name' => 'Direct Component Grading',
            'code' => 'DIRECT',
            'description' => 'Components graded out of their individual maximum marks',
            'type' => 'direct',
            'calculation_method' => 'weighted_average',
            'components_graded_out_of_total' => false,
            'all_components_required' => true,
            'component_pass_threshold' => 40.00,
            'overall_pass_threshold' => 40.00,
            'compensatory_grading_allowed' => false,
            'display_format' => json_encode(['show_percentage' => true]),
        ]);

        // Degree Classification
        GradingScheme::updateOrCreate(['code' => 'CLASSIFICATION'], [
            'name' => 'Degree Classification',
            'code' => 'CLASSIFICATION',
            'description' => 'University degree classification system',
            'type' => 'classification',
            'calculation_method' => 'grade_boundaries',
            'components_graded_out_of_total' => true,
            'all_components_required' => false,
            'overall_pass_threshold' => 40.00,
            'compensatory_grading_allowed' => true,
            'compensation_threshold' => 35.00,
            'grade_boundaries' => json_encode([
                ['min' => 70, 'max' => 100, 'grade' => 'First Class', 'classification' => '1st'],
                ['min' => 60, 'max' => 69, 'grade' => 'Upper Second Class', 'classification' => '2:1'],
                ['min' => 50, 'max' => 59, 'grade' => 'Lower Second Class', 'classification' => '2:2'],
                ['min' => 40, 'max' => 49, 'grade' => 'Third Class', 'classification' => '3rd'],
                ['min' => 0, 'max' => 39, 'grade' => 'Fail', 'classification' => 'Fail'],
            ]),
            'display_format' => json_encode(['show_classification' => true, 'show_percentage' => true]),
        ]);

        // Competency-Based Assessment
        GradingScheme::updateOrCreate(['code' => 'COMPETENCY'], [
            'name' => 'Competency-Based Assessment',
            'code' => 'COMPETENCY',
            'description' => 'Competency-based assessment system',
            'type' => 'competency',
            'calculation_method' => 'competency_based',
            'components_graded_out_of_total' => false,
            'all_components_required' => true,
            'component_pass_threshold' => 100.00, // Must achieve competency
            'overall_pass_threshold' => 100.00,
            'compensatory_grading_allowed' => false,
            'display_format' => json_encode(['show_competency' => true]),
        ]);
    }
    
    private function createAssessmentStrategies(): void
    {
        // Standard Component-Weighted
        AssessmentStrategy::updateOrCreate(['code' => 'STANDARD'], [
            'name' => 'Standard Component-Weighted',
            'code' => 'STANDARD',
            'description' => 'Traditional weighted component assessment strategy',
            'assessment_type' => 'component_weighted',
            'typical_component_count' => 3,
            'supports_resubmission' => true,
            'max_resubmissions' => 1,
            'supports_extensions' => true,
            'default_extension_days' => 7,
            'requires_moderation' => false,
            'requires_external_examiner' => false,
            'supports_draft_submissions' => false,
            'progress_calculation' => 'all_complete',
            'allows_partial_completion' => true,
            'repeat_strategy' => 'component_only',
        ]);

        // Portfolio Assessment
        AssessmentStrategy::updateOrCreate(['code' => 'PORTFOLIO'], [
            'name' => 'Portfolio Assessment',
            'code' => 'PORTFOLIO',
            'description' => 'Portfolio-based assessment strategy',
            'assessment_type' => 'portfolio',
            'typical_component_count' => 2,
            'supports_resubmission' => true,
            'max_resubmissions' => 2,
            'supports_extensions' => true,
            'default_extension_days' => 14,
            'requires_moderation' => true,
            'requires_external_examiner' => false,
            'supports_draft_submissions' => true,
            'progress_calculation' => 'continuous',
            'allows_partial_completion' => true,
            'repeat_strategy' => 'portfolio_rebuild',
        ]);

        // Project-Based Assessment
        AssessmentStrategy::updateOrCreate(['code' => 'PROJECT'], [
            'name' => 'Project-Based Assessment',
            'code' => 'PROJECT',
            'description' => 'Project-based assessment strategy',
            'assessment_type' => 'project_based',
            'typical_component_count' => 3,
            'supports_resubmission' => true,
            'max_resubmissions' => 1,
            'supports_extensions' => true,
            'default_extension_days' => 14,
            'requires_moderation' => true,
            'requires_external_examiner' => true,
            'supports_draft_submissions' => true,
            'progress_calculation' => 'milestone_based',
            'allows_partial_completion' => false,
            'repeat_strategy' => 'full_module',
        ]);

        // Competency-Based Assessment
        AssessmentStrategy::updateOrCreate(['code' => 'COMPETENCY'], [
            'name' => 'Competency-Based Assessment',
            'code' => 'COMPETENCY',
            'description' => 'Competency-based assessment strategy',
            'assessment_type' => 'competency',
            'typical_component_count' => 4,
            'supports_resubmission' => true,
            'max_resubmissions' => null, // Unlimited until competency achieved
            'supports_extensions' => true,
            'default_extension_days' => 30,
            'requires_moderation' => false,
            'requires_external_examiner' => false,
            'supports_draft_submissions' => true,
            'progress_calculation' => 'all_complete',
            'allows_partial_completion' => false,
            'repeat_strategy' => 'component_only',
        ]);
    }
    
    private function createModuleProgressionRules(): void
    {
        // Sequential Progression
        ModuleProgressionRule::updateOrCreate(['code' => 'SEQUENTIAL'], [
            'name' => 'Sequential Progression',
            'code' => 'SEQUENTIAL',
            'description' => 'Students must complete modules in sequence',
            'progression_type' => 'sequential',
            'requires_previous_completion' => true,
            'allows_concurrent_modules' => false,
            'supports_module_prerequisites' => true,
            'failure_action' => 'repeat_module',
            'allows_compensation' => true,
            'compensation_threshold' => 35.00,
            'max_compensation_modules' => 1,
            'blocks_on_failed_placement' => true,
            'blocks_on_unpaid_fees' => false,
            'requires_all_modules_passed' => true,
            'overall_programme_threshold' => 40.00,
            'maximum_duration_months' => 24,
            'supports_programme_extensions' => true,
            'default_extension_months' => 6,
        ]);

        // Credit-Based Progression
        ModuleProgressionRule::updateOrCreate(['code' => 'CREDIT'], [
            'name' => 'Credit-Based Progression',
            'code' => 'CREDIT',
            'description' => 'Students progress based on credit accumulation',
            'progression_type' => 'credit_based',
            'requires_previous_completion' => false,
            'allows_concurrent_modules' => true,
            'supports_module_prerequisites' => true,
            'minimum_credits_per_period' => 60,
            'maximum_credits_per_period' => 180,
            'minimum_gpa_to_progress' => 2.0,
            'failure_action' => 'compensation',
            'allows_compensation' => true,
            'compensation_threshold' => 35.00,
            'max_compensation_modules' => 2,
            'blocks_on_failed_placement' => false,
            'blocks_on_unpaid_fees' => true,
            'requires_all_modules_passed' => false,
            'overall_programme_threshold' => 40.00,
            'maximum_duration_months' => 48,
            'supports_programme_extensions' => true,
            'default_extension_months' => 12,
        ]);

        // Flexible Progression
        ModuleProgressionRule::updateOrCreate(['code' => 'FLEXIBLE'], [
            'name' => 'Flexible Progression',
            'code' => 'FLEXIBLE',
            'description' => 'Students can progress with minimal restrictions',
            'progression_type' => 'flexible',
            'requires_previous_completion' => false,
            'allows_concurrent_modules' => true,
            'supports_module_prerequisites' => false,
            'failure_action' => 'repeat_components',
            'allows_compensation' => true,
            'compensation_threshold' => 30.00,
            'max_compensation_modules' => 3,
            'blocks_on_failed_placement' => false,
            'blocks_on_unpaid_fees' => false,
            'requires_all_modules_passed' => false,
            'overall_programme_threshold' => 35.00,
            'maximum_duration_months' => 60,
            'supports_programme_extensions' => true,
            'default_extension_months' => 12,
        ]);
    }
    
    private function linkProgrammeTypeDefaults(): void
    {
        // Get configuration IDs
        $percentageGrading = GradingScheme::where('code', 'PERCENTAGE')->first();
        $directGrading = GradingScheme::where('code', 'DIRECT')->first();
        $classificationGrading = GradingScheme::where('code', 'CLASSIFICATION')->first();
        $competencyGrading = GradingScheme::where('code', 'COMPETENCY')->first();
        
        $standardAssessment = AssessmentStrategy::where('code', 'STANDARD')->first();
        $portfolioAssessment = AssessmentStrategy::where('code', 'PORTFOLIO')->first();
        $projectAssessment = AssessmentStrategy::where('code', 'PROJECT')->first();
        $competencyAssessment = AssessmentStrategy::where('code', 'COMPETENCY')->first();
        
        $sequentialProgression = ModuleProgressionRule::where('code', 'SEQUENTIAL')->first();
        $creditProgression = ModuleProgressionRule::where('code', 'CREDIT')->first();
        $flexibleProgression = ModuleProgressionRule::where('code', 'FLEXIBLE')->first();
        
        // Update QQI Level 5 with typical QQI5 configuration
        ProgrammeType::where('code', 'QQI5')->update([
            'default_grading_scheme_id' => $percentageGrading->id,
            'default_assessment_strategy_id' => $standardAssessment->id,
            'default_module_progression_rule_id' => $sequentialProgression->id,
        ]);
        
        // Update QQI Level 6 with more advanced configuration
        ProgrammeType::where('code', 'QQI6')->update([
            'default_grading_scheme_id' => $directGrading->id,
            'default_assessment_strategy_id' => $portfolioAssessment->id,
            'default_module_progression_rule_id' => $sequentialProgression->id,
        ]);
        
        // Update Degree with university-style configuration
        ProgrammeType::where('code', 'DEGREE')->update([
            'default_grading_scheme_id' => $classificationGrading->id,
            'default_assessment_strategy_id' => $projectAssessment->id,
            'default_module_progression_rule_id' => $creditProgression->id,
        ]);
        
        // Add a fourth programme type for competency-based/flexible programmes
        ProgrammeType::updateOrCreate(['code' => 'FLEXIBLE'], [
            'name' => 'Flexible Learning',
            'code' => 'FLEXIBLE',
            'description' => 'Competency-based flexible learning programmes',
            'awarding_body' => 'QQI',
            'nfq_level' => '5-6',
            'default_duration_months' => 6,
            'default_credit_value' => 30,
            'minimum_pass_grade' => 100.00, // Competency requires 100%
            'requires_placement' => false,
            'requires_external_verification' => false,
            'supports_rolling_enrolment' => true,
            'supports_cohort_enrolment' => false,
            'default_config' => json_encode([
                'assessment' => [
                    'competency_based' => true,
                    'unlimited_attempts' => true,
                    'self_paced' => true,
                ],
                'progression' => [
                    'type' => 'competency_unlock',
                    'prerequisite_based' => true,
                ]
            ]),
            'default_grading_scheme_id' => $competencyGrading->id,
            'default_assessment_strategy_id' => $competencyAssessment->id,
            'default_module_progression_rule_id' => $flexibleProgression->id,
        ]);
    }
    
    private function updateExistingProgrammes(): void
    {
        $qqiLevel5Type = ProgrammeType::where('code', 'QQI5')->first();
        $qqiLevel6Type = ProgrammeType::where('code', 'QQI6')->first();
        $degreeType = ProgrammeType::where('code', 'DEGREE')->first();
        
        $percentageGrading = GradingScheme::where('code', 'PERCENTAGE')->first();
        $directGrading = GradingScheme::where('code', 'DIRECT')->first();
        $classificationGrading = GradingScheme::where('code', 'CLASSIFICATION')->first();
        
        $standardAssessment = AssessmentStrategy::where('code', 'STANDARD')->first();
        $portfolioAssessment = AssessmentStrategy::where('code', 'PORTFOLIO')->first();
        $projectAssessment = AssessmentStrategy::where('code', 'PROJECT')->first();
        
        $sequentialProgression = ModuleProgressionRule::where('code', 'SEQUENTIAL')->first();
        $creditProgression = ModuleProgressionRule::where('code', 'CREDIT')->first();
        
        // Update ELC5 programmes
        Programme::where('code', 'ELC5')->update([
            'programme_type_id' => $qqiLevel5Type->id,
            'grading_scheme_id' => $percentageGrading->id,
            'assessment_strategy_id' => $standardAssessment->id,
            'module_progression_rule_id' => $sequentialProgression->id,
            'awarding_body' => 'QQI',
            'nfq_level' => '5',
            'credit_value' => 60, // 4 modules * 15 credits each
            'minimum_pass_grade' => 40.00,
            'requires_placement' => true,
            'requires_external_verification' => true,
            'delivery_mode' => 'on_campus',
            'typical_duration_months' => 12,
            'intake_schedule' => json_encode(['months' => [1, 4, 9]]), // January, April, September
        ]);
        
        // Update ELC6 programmes
        Programme::where('code', 'ELC6')->update([
            'programme_type_id' => $qqiLevel6Type->id,
            'grading_scheme_id' => $directGrading->id, // Example of different grading scheme
            'assessment_strategy_id' => $portfolioAssessment->id, // Portfolio-based assessment
            'module_progression_rule_id' => $sequentialProgression->id,
            'awarding_body' => 'QQI',
            'nfq_level' => '6',
            'credit_value' => 60,
            'minimum_pass_grade' => 40.00,
            'requires_placement' => true,
            'requires_external_verification' => true,
            'delivery_mode' => 'on_campus',
            'typical_duration_months' => 12,
            'intake_schedule' => json_encode(['months' => [1, 4, 9]]),
            // Override: Require all components to pass
            'grading_overrides' => json_encode([
                'all_components_required' => true,
                'component_pass_threshold' => 40.00,
                'compensatory_grading_allowed' => false,
            ]),
        ]);
        
        // Update HSC degree programme
        Programme::where('code', 'HSC')->update([
            'programme_type_id' => $degreeType->id,
            'grading_scheme_id' => $classificationGrading->id,
            'assessment_strategy_id' => $projectAssessment->id,
            'module_progression_rule_id' => $creditProgression->id,
            'awarding_body' => 'Oxford Brookes University',
            'nfq_level' => '7',
            'credit_value' => 360, // 3-year degree
            'minimum_pass_grade' => 40.00,
            'requires_placement' => false,
            'requires_external_verification' => true,
            'external_examiner_required' => true,
            'delivery_mode' => 'on_campus',
            'typical_duration_months' => 36,
            'intake_schedule' => json_encode(['months' => [9]]), // September only for degree
        ]);
        
        // Update rolling programmes with flexible progression
        $flexibleProgression = ModuleProgressionRule::where('code', 'FLEXIBLE')->first();
        
        Programme::whereIn('code', ['QQI5-GEN', 'QQI6-SPEC'])->update([
            'grading_scheme_id' => $percentageGrading->id,
            'assessment_strategy_id' => $standardAssessment->id,
            'module_progression_rule_id' => $flexibleProgression->id,
            'delivery_mode' => 'online',
            'awarding_body' => 'QQI',
            'minimum_pass_grade' => 40.00,
        ]);
        
        // Update QQI5-GEN specifically
        Programme::where('code', 'QQI5-GEN')->update([
            'programme_type_id' => $qqiLevel5Type->id,
            'nfq_level' => '5',
            'typical_duration_months' => 3, // Per module
        ]);
        
        // Update QQI6-SPEC specifically  
        Programme::where('code', 'QQI6-SPEC')->update([
            'programme_type_id' => $qqiLevel6Type->id,
            'nfq_level' => '6',
            'typical_duration_months' => 3, // Per module
        ]);
    }
}