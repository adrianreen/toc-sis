<?php

namespace App\Http\Controllers;

use App\Models\ProgrammeType;
use App\Models\GradingScheme;
use App\Models\AssessmentStrategy;
use App\Models\ModuleProgressionRule;
use App\Models\Programme;
use App\Services\GradingService;
use App\Services\ArchetypeValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchetypeController extends Controller
{
    public function __construct(
        private GradingService $gradingService,
        private ArchetypeValidationService $archetypeValidationService
    ) {
    }

    /**
     * Display archetype management dashboard
     */
    public function index()
    {
        $archetypes = ProgrammeType::with([
            'defaultGradingScheme',
            'defaultAssessmentStrategy', 
            'defaultModuleProgressionRule'
        ])->get();

        $statistics = [
            'total_archetypes' => $archetypes->count(),
            'programmes_using_archetypes' => Programme::whereNotNull('programme_type_id')->count(),
            'total_programmes' => Programme::count(),
        ];

        // Get usage statistics for each archetype
        foreach ($archetypes as $archetype) {
            $archetype->programme_count = Programme::where('programme_type_id', $archetype->id)->count();
            $archetype->active_programme_count = Programme::where('programme_type_id', $archetype->id)
                ->where('is_active', true)->count();
        }

        return view('admin.archetypes.index', compact('archetypes', 'statistics'));
    }

    /**
     * Display detailed archetype overview
     */
    public function show(ProgrammeType $archetype)
    {
        $archetype->load([
            'defaultGradingScheme',
            'defaultAssessmentStrategy',
            'defaultModuleProgressionRule'
        ]);

        // Get programmes using this archetype
        $programmes = Programme::where('programme_type_id', $archetype->id)
            ->with(['cohorts', 'gradingScheme', 'assessmentStrategy', 'moduleProgressionRule'])
            ->get();

        // Get configuration analysis
        $configAnalysis = $this->analyzeArchetypeConfiguration($archetype, $programmes);

        return view('admin.archetypes.show', compact('archetype', 'programmes', 'configAnalysis'));
    }

    /**
     * Display archetype configuration form
     */
    public function edit(ProgrammeType $archetype)
    {
        $gradingSchemes = GradingScheme::active()->get();
        $assessmentStrategies = AssessmentStrategy::active()->get();
        $moduleProgressionRules = ModuleProgressionRule::active()->get();

        return view('admin.archetypes.edit', compact(
            'archetype',
            'gradingSchemes',
            'assessmentStrategies',
            'moduleProgressionRules'
        ));
    }

    /**
     * Update archetype configuration
     */
    public function update(Request $request, ProgrammeType $archetype)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'awarding_body' => 'required|string|max:255',
            'nfq_level' => 'required|string|max:10',
            'default_duration_months' => 'required|integer|min:1',
            'default_credit_value' => 'required|integer|min:1',
            'minimum_pass_grade' => 'required|numeric|min:0|max:100',
            'requires_placement' => 'boolean',
            'requires_external_verification' => 'boolean',
            'supports_rolling_enrolment' => 'boolean',
            'supports_cohort_enrolment' => 'boolean',
            'default_grading_scheme_id' => 'nullable|exists:grading_schemes,id',
            'default_assessment_strategy_id' => 'nullable|exists:assessment_strategies,id',
            'default_module_progression_rule_id' => 'nullable|exists:module_progression_rules,id',
        ]);

        $archetype->update($validated);

        return redirect()->route('admin.archetypes.show', $archetype)
            ->with('success', 'Archetype configuration updated successfully.');
    }

    /**
     * Display archetype validation dashboard
     */
    public function validationDashboard()
    {
        $programmes = Programme::with(['programmeType', 'gradingScheme', 'assessmentStrategy', 'moduleProgressionRule'])
            ->whereNotNull('programme_type_id')
            ->get();

        $validationResults = [];
        foreach ($programmes as $programme) {
            $validation = $this->gradingService->validateGradingConfiguration($programme);
            $constraints = $this->archetypeValidationService->getGradingConstraints($programme);
            
            $validationResults[] = [
                'programme' => $programme,
                'validation' => $validation,
                'constraints' => $constraints,
            ];
        }

        return view('admin.archetypes.validation', compact('validationResults'));
    }

    /**
     * API endpoint for archetype statistics
     */
    public function getStatistics()
    {
        $stats = [
            'archetype_usage' => $this->getArchetypeUsageStats(),
            'configuration_compliance' => $this->getConfigurationComplianceStats(),
            'progression_type_distribution' => $this->getProgressionTypeDistribution(),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk update programme archetypes
     */
    public function bulkUpdateProgrammes(Request $request)
    {
        $validated = $request->validate([
            'programme_ids' => 'required|array',
            'programme_ids.*' => 'exists:programmes,id',
            'archetype_id' => 'required|exists:programme_types,id',
        ]);

        DB::transaction(function () use ($validated) {
            $archetype = ProgrammeType::find($validated['archetype_id']);
            
            foreach ($validated['programme_ids'] as $programmeId) {
                $programme = Programme::find($programmeId);
                
                // Apply archetype defaults to programme
                $defaults = $archetype->createProgrammeDefaults();
                $programme->update([
                    'programme_type_id' => $archetype->id,
                    'grading_scheme_id' => $defaults['grading_scheme_id'],
                    'assessment_strategy_id' => $defaults['assessment_strategy_id'],
                    'module_progression_rule_id' => $defaults['module_progression_rule_id'],
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Programmes updated successfully']);
    }

    // Private helper methods

    private function analyzeArchetypeConfiguration(ProgrammeType $archetype, $programmes)
    {
        $analysis = [
            'default_compliance' => 0,
            'configuration_variants' => [],
            'common_overrides' => [],
        ];

        $totalProgrammes = $programmes->count();
        if ($totalProgrammes === 0) {
            return $analysis;
        }

        $compliantCount = 0;
        $configVariations = [];

        foreach ($programmes as $programme) {
            $defaults = $archetype->createProgrammeDefaults();
            
            $isCompliant = 
                $programme->grading_scheme_id == $defaults['grading_scheme_id'] &&
                $programme->assessment_strategy_id == $defaults['assessment_strategy_id'] &&
                $programme->module_progression_rule_id == $defaults['module_progression_rule_id'];
                
            if ($isCompliant) {
                $compliantCount++;
            }

            // Track configuration variations
            $configKey = sprintf('%s_%s_%s',
                $programme->grading_scheme_id,
                $programme->assessment_strategy_id,
                $programme->module_progression_rule_id
            );
            
            if (!isset($configVariations[$configKey])) {
                $configVariations[$configKey] = [
                    'count' => 0,
                    'grading_scheme' => $programme->gradingScheme?->name,
                    'assessment_strategy' => $programme->assessmentStrategy?->name,
                    'progression_rule' => $programme->moduleProgressionRule?->name,
                ];
            }
            $configVariations[$configKey]['count']++;
        }

        $analysis['default_compliance'] = round(($compliantCount / $totalProgrammes) * 100, 1);
        $analysis['configuration_variants'] = array_values($configVariations);

        return $analysis;
    }

    private function getArchetypeUsageStats()
    {
        return ProgrammeType::withCount('programmes')
            ->get()
            ->map(function ($archetype) {
                return [
                    'name' => $archetype->name,
                    'code' => $archetype->code,
                    'programme_count' => $archetype->programmes_count,
                ];
            });
    }

    private function getConfigurationComplianceStats()
    {
        $programmes = Programme::with('programmeType')->whereNotNull('programme_type_id')->get();
        $totalProgrammes = $programmes->count();
        
        if ($totalProgrammes === 0) {
            return ['compliance_rate' => 0, 'total_programmes' => 0];
        }

        $compliantCount = 0;
        foreach ($programmes as $programme) {
            $validation = $this->gradingService->validateGradingConfiguration($programme);
            if ($validation['valid']) {
                $compliantCount++;
            }
        }

        return [
            'compliance_rate' => round(($compliantCount / $totalProgrammes) * 100, 1),
            'total_programmes' => $totalProgrammes,
            'compliant_programmes' => $compliantCount,
        ];
    }

    private function getProgressionTypeDistribution()
    {
        return Programme::whereNotNull('module_progression_rule_id')
            ->with('moduleProgressionRule')
            ->get()
            ->groupBy('moduleProgressionRule.progression_type')
            ->map(function ($programmes, $type) {
                return [
                    'type' => $type,
                    'count' => $programmes->count(),
                ];
            })
            ->values();
    }
}