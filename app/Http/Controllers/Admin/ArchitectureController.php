<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ArchitectureValidationService;
use App\Models\Programme;
use App\Models\ProgrammeInstance;
use App\Models\Module;
use App\Models\ModuleInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ArchitectureController extends Controller
{
    private $validationService;

    public function __construct(ArchitectureValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Architecture health dashboard
     */
    public function dashboard()
    {
        $validation = $this->validationService->validateEntireArchitecture();
        
        // Get recent activity
        $recentProgrammes = Programme::latest()->take(5)->get();
        $recentInstances = ProgrammeInstance::with('programme')->latest()->take(5)->get();
        $recentModules = Module::latest()->take(5)->get();
        $recentModuleInstances = ModuleInstance::with('module')->latest()->take(5)->get();

        return view('admin.architecture.dashboard', compact(
            'validation',
            'recentProgrammes',
            'recentInstances', 
            'recentModules',
            'recentModuleInstances'
        ));
    }

    /**
     * Detailed validation report
     */
    public function validation()
    {
        $validation = $this->validationService->validateEntireArchitecture();
        
        return view('admin.architecture.validation', compact('validation'));
    }

    /**
     * Auto-fix issues
     */
    public function autoFix()
    {
        $results = $this->validationService->autoFixIssues();
        
        $message = '';
        if (!empty($results['fixed'])) {
            $message .= 'Fixed: ' . implode(', ', $results['fixed']) . '. ';
        }
        if (!empty($results['failed'])) {
            $message .= 'Failed: ' . implode(', ', $results['failed']);
        }
        
        if (empty($results['fixed']) && empty($results['failed'])) {
            $message = 'No issues found to fix.';
        }
        
        return redirect()->route('admin.architecture.validation')
            ->with('success', $message);
    }

    /**
     * System statistics API endpoint
     */
    public function statistics()
    {
        $validation = $this->validationService->validateEntireArchitecture();
        
        return response()->json([
            'stats' => $validation['stats'],
            'health' => [
                'valid' => $validation['valid'],
                'errors' => count($validation['errors']),
                'warnings' => count($validation['warnings']),
            ]
        ]);
    }

    /**
     * Programme instance curriculum validation
     */
    public function validateCurriculum(ProgrammeInstance $programmeInstance)
    {
        $validation = $this->validationService->validateProgrammeCurriculum($programmeInstance);
        
        return response()->json($validation);
    }

    /**
     * Export validation report
     */
    public function exportReport(Request $request)
    {
        $validation = $this->validationService->validateEntireArchitecture();
        
        $content = $this->generateReportContent($validation);
        
        $filename = 'architecture_validation_' . now()->format('Y-m-d_H-i-s') . '.txt';
        
        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Run architecture validation command
     */
    public function runValidation(Request $request)
    {
        $options = [];
        
        if ($request->boolean('fix')) {
            $options['--fix'] = true;
        }
        
        if ($request->boolean('stats')) {
            $options['--stats'] = true;
        }
        
        if ($request->filled('programme')) {
            $options['--programme'] = $request->get('programme');
        }

        try {
            Artisan::call('architecture:validate', $options);
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateReportContent(array $validation): string
    {
        $content = "TOC-SIS Architecture Validation Report\n";
        $content .= "Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= str_repeat("=", 50) . "\n\n";

        // System Statistics
        $content .= "SYSTEM STATISTICS:\n";
        $content .= str_repeat("-", 20) . "\n";
        foreach ($validation['stats'] as $key => $value) {
            $content .= ucwords(str_replace('_', ' ', $key)) . ": {$value}\n";
        }
        $content .= "\n";

        // Validation Status
        $content .= "VALIDATION STATUS: " . ($validation['valid'] ? 'PASSED' : 'FAILED') . "\n";
        $content .= str_repeat("-", 20) . "\n\n";

        // Errors
        if (!empty($validation['errors'])) {
            $content .= "ERRORS (" . count($validation['errors']) . "):\n";
            $content .= str_repeat("-", 10) . "\n";
            foreach ($validation['errors'] as $error) {
                $content .= "• {$error}\n";
            }
            $content .= "\n";
        }

        // Warnings
        if (!empty($validation['warnings'])) {
            $content .= "WARNINGS (" . count($validation['warnings']) . "):\n";
            $content .= str_repeat("-", 10) . "\n";
            foreach ($validation['warnings'] as $warning) {
                $content .= "• {$warning}\n";
            }
            $content .= "\n";
        }

        if ($validation['valid'] && empty($validation['warnings'])) {
            $content .= "No issues found. Architecture is healthy!\n";
        }

        return $content;
    }
}