<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Display a paginated list of modules with search and filtering
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Module::withCount(['moduleInstances']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('module_code', 'LIKE', "%{$search}%");
            });
        }

        // Filter by standalone enrolment
        if ($request->filled('standalone')) {
            $standalone = $request->standalone === 'yes';
            $query->where('allows_standalone_enrolment', $standalone);
        }

        // Filter by credit range
        if ($request->filled('credit_range')) {
            switch ($request->credit_range) {
                case '1-5':
                    $query->whereBetween('credit_value', [1, 5]);
                    break;
                case '6-10':
                    $query->whereBetween('credit_value', [6, 10]);
                    break;
                case '11-15':
                    $query->whereBetween('credit_value', [11, 15]);
                    break;
                case '16+':
                    $query->where('credit_value', '>=', 16);
                    break;
            }
        }

        // Filter by instance count
        if ($request->filled('instance_count')) {
            switch ($request->instance_count) {
                case 'none':
                    $query->having('module_instances_count', '=', 0);
                    break;
                case 'low':
                    $query->having('module_instances_count', '>=', 1)
                        ->having('module_instances_count', '<=', 2);
                    break;
                case 'medium':
                    $query->having('module_instances_count', '>=', 3)
                        ->having('module_instances_count', '<=', 5);
                    break;
                case 'high':
                    $query->having('module_instances_count', '>', 5);
                    break;
            }
        }

        // Filter by async cadence
        if ($request->filled('cadence')) {
            $query->where('async_instance_cadence', $request->cadence);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'module_code');
        $sortDirection = $request->get('sort_direction', 'asc');

        switch ($sortBy) {
            case 'instances_count':
                $query->orderBy('module_instances_count', $sortDirection);
                break;
            default:
                $query->orderBy($sortBy, $sortDirection);
        }

        $modules = $query->paginate(20)->withQueryString();

        return view('modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new module
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('modules.create');
    }

    /**
     * Store a newly created module with assessment strategy validation
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate(Module::rules());

        // Validate total weighting adds up to required total
        $requiredTotal = config('academic.assessment.total_weighting');
        $totalWeight = collect($validated['assessment_strategy'])->sum('weighting');
        if ($totalWeight != $requiredTotal) {
            return back()->withErrors(['assessment_strategy' => "Assessment component weightings must total {$requiredTotal}%."])
                ->withInput();
        }

        // Transform assessment components for storage
        $assessmentStrategy = collect($validated['assessment_strategy'])->map(function ($component) {
            return [
                'component_name' => $component['component_name'],
                'weighting' => (float) $component['weighting'],
                'is_must_pass' => (bool) ($component['is_must_pass'] ?? false),
                'component_pass_mark' => $component['component_pass_mark'] ? (float) $component['component_pass_mark'] : null,
            ];
        })->toArray();

        $module = Module::create([
            'title' => $validated['title'],
            'module_code' => $validated['module_code'],
            'credit_value' => $validated['credit_value'],
            'description' => $validated['description'],
            'nfq_level' => $validated['nfq_level'],
            'assessment_strategy' => $assessmentStrategy,
            'allows_standalone_enrolment' => $validated['allows_standalone_enrolment'] ?? false,
            'async_instance_cadence' => $validated['async_instance_cadence'],
        ]);

        return redirect()->route('modules.show', $module)
            ->with('success', 'Module created successfully.');
    }

    public function show(Module $module)
    {
        $module->load([
            'moduleInstances' => function ($query) {
                $query->with(['tutor', 'programmeInstances'])
                    ->withCount('enrolments')
                    ->orderBy('start_date', 'desc');
            },
        ]);

        return view('modules.show', compact('module'));
    }

    public function edit(Module $module)
    {
        // Transform assessment strategy back to form format
        $assessmentComponents = collect($module->assessment_strategy)->map(function ($component) {
            return [
                'component_name' => $component['component_name'],
                'weighting' => $component['weighting'],
                'is_must_pass' => $component['is_must_pass'],
                'component_pass_mark' => $component['component_pass_mark'],
            ];
        })->toArray();

        return view('modules.edit', compact('module', 'assessmentComponents'));
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate(Module::rules($module->id));

        // Validate total weighting adds up to required total
        $requiredTotal = config('academic.assessment.total_weighting');
        $totalWeight = collect($validated['assessment_strategy'])->sum('weighting');
        if ($totalWeight != $requiredTotal) {
            return back()->withErrors(['assessment_strategy' => "Assessment component weightings must total {$requiredTotal}%."])
                ->withInput();
        }

        // Transform assessment components for storage
        $assessmentStrategy = collect($validated['assessment_strategy'])->map(function ($component) {
            return [
                'component_name' => $component['component_name'],
                'weighting' => (float) $component['weighting'],
                'is_must_pass' => (bool) ($component['is_must_pass'] ?? false),
                'component_pass_mark' => $component['component_pass_mark'] ? (float) $component['component_pass_mark'] : null,
            ];
        })->toArray();

        $module->update([
            'title' => $validated['title'],
            'module_code' => $validated['module_code'],
            'credit_value' => $validated['credit_value'],
            'description' => $validated['description'],
            'nfq_level' => $validated['nfq_level'],
            'assessment_strategy' => $assessmentStrategy,
            'allows_standalone_enrolment' => $validated['allows_standalone_enrolment'] ?? false,
            'async_instance_cadence' => $validated['async_instance_cadence'],
        ]);

        return redirect()->route('modules.show', $module)
            ->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module)
    {
        // Check if module has any instances with active enrolments
        $activeInstances = $module->moduleInstances()
            ->whereHas('enrolments', function ($query) {
                $query->whereIn('status', ['active', 'deferred']);
            })
            ->count();

        if ($activeInstances > 0) {
            return redirect()->route('modules.index')
                ->with('error', 'Cannot delete module with active enrolments.');
        }

        $module->delete();

        return redirect()->route('modules.index')
            ->with('success', 'Module deleted successfully.');
    }
}
