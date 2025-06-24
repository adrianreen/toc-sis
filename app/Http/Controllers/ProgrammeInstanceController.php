<?php

namespace App\Http\Controllers;

use App\Models\ModuleInstance;
use App\Models\Programme;
use App\Models\ProgrammeInstance;
use Illuminate\Http\Request;

class ProgrammeInstanceController extends Controller
{
    public function index(Request $request)
    {
        $query = ProgrammeInstance::with(['programme', 'moduleInstances'])
            ->withCount('enrolments');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('label', 'LIKE', "%{$search}%")
                    ->orWhereHas('programme', function ($subq) use ($search) {
                        $subq->where('title', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filter by delivery style
        if ($request->filled('delivery_style')) {
            $query->where('default_delivery_style', $request->delivery_style);
        }

        // Filter by programme
        if ($request->filled('programme_id')) {
            $query->where('programme_id', $request->programme_id);
        }

        // Filter by status (based on dates)
        if ($request->filled('status')) {
            $now = now();
            switch ($request->status) {
                case 'upcoming':
                    $query->where('intake_start_date', '>', $now);
                    break;
                case 'active':
                    $query->where('intake_start_date', '<=', $now)
                        ->where(function ($q) use ($now) {
                            $q->whereNull('intake_end_date')
                                ->orWhere('intake_end_date', '>=', $now);
                        });
                    break;
                case 'closed':
                    $query->where('intake_end_date', '<', $now);
                    break;
            }
        }

        // Filter by enrolment level
        if ($request->filled('enrolment_level')) {
            switch ($request->enrolment_level) {
                case 'high':
                    $query->having('enrolments_count', '>=', 20);
                    break;
                case 'medium':
                    $query->having('enrolments_count', '>=', 5)
                        ->having('enrolments_count', '<=', 19);
                    break;
                case 'low':
                    $query->having('enrolments_count', '>=', 1)
                        ->having('enrolments_count', '<=', 4);
                    break;
                case 'none':
                    $query->having('enrolments_count', '=', 0);
                    break;
            }
        }

        // Filter by date range
        if ($request->filled('intake_start_from')) {
            $query->where('intake_start_date', '>=', $request->intake_start_from);
        }
        if ($request->filled('intake_start_to')) {
            $query->where('intake_start_date', '<=', $request->intake_start_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'intake_start_date');
        $sortDirection = $request->get('sort_direction', 'desc');

        switch ($sortBy) {
            case 'programme_title':
                $query->join('programmes', 'programme_instances.programme_id', '=', 'programmes.id')
                    ->orderBy('programmes.title', $sortDirection)
                    ->select('programme_instances.*');
                break;
            case 'label':
                $query->orderBy('label', $sortDirection);
                break;
            case 'enrolments_count':
                $query->orderBy('enrolments_count', $sortDirection);
                break;
            default:
                $query->orderBy($sortBy, $sortDirection);
        }

        $instances = $query->paginate(20)->withQueryString();

        // Get filter options
        $programmes = \App\Models\Programme::orderBy('title')->get();

        return view('programme-instances.index', compact('instances', 'programmes'));
    }

    public function create()
    {
        $programmes = Programme::orderBy('title')->get();

        return view('programme-instances.create', compact('programmes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'label' => 'required|string|max:255',
            'intake_start_date' => 'required|date',
            'intake_end_date' => 'nullable|date|after_or_equal:intake_start_date',
            'default_delivery_style' => 'required|in:sync,async',
        ]);

        $instance = ProgrammeInstance::create($validated);

        return redirect()->route('programme-instances.show', $instance)
            ->with('success', 'Programme instance created successfully.');
    }

    public function show(ProgrammeInstance $programmeInstance)
    {
        $programmeInstance->load([
            'programme',
            'moduleInstances.module',
            'moduleInstances.tutor',
            'enrolments.student',
        ]);

        // Get available module instances for curriculum building
        $availableModules = ModuleInstance::with(['module', 'tutor'])
            ->whereDoesntHave('programmeInstances', function ($query) use ($programmeInstance) {
                $query->where('programme_instance_id', $programmeInstance->id);
            })
            ->get();

        return view('programme-instances.show', compact('programmeInstance', 'availableModules'));
    }

    public function edit(ProgrammeInstance $programmeInstance)
    {
        $programmes = Programme::orderBy('title')->get();

        return view('programme-instances.edit', compact('programmeInstance', 'programmes'));
    }

    public function update(Request $request, ProgrammeInstance $programmeInstance)
    {
        $validated = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'label' => 'required|string|max:255',
            'intake_start_date' => 'required|date',
            'intake_end_date' => 'nullable|date|after_or_equal:intake_start_date',
            'default_delivery_style' => 'required|in:sync,async',
        ]);

        $programmeInstance->update($validated);

        return redirect()->route('programme-instances.show', $programmeInstance)
            ->with('success', 'Programme instance updated successfully.');
    }

    public function destroy(ProgrammeInstance $programmeInstance)
    {
        // Check if instance has any active enrolments
        $activeEnrolments = $programmeInstance->enrolments()
            ->whereIn('status', ['active', 'deferred'])
            ->count();

        if ($activeEnrolments > 0) {
            return redirect()->route('programme-instances.index')
                ->with('error', 'Cannot delete programme instance with active enrolments.');
        }

        $programmeInstance->delete();

        return redirect()->route('programme-instances.index')
            ->with('success', 'Programme instance deleted successfully.');
    }

    /**
     * Show curriculum management interface
     */
    public function curriculum(ProgrammeInstance $programmeInstance)
    {
        $programmeInstance->load([
            'programme',
            'moduleInstances' => function ($query) {
                $query->with(['module', 'tutor'])
                    ->orderBy('start_date');
            },
        ]);

        // Get available module instances that can be added to curriculum
        $availableModules = ModuleInstance::with(['module', 'tutor'])
            ->whereDoesntHave('programmeInstances', function ($query) use ($programmeInstance) {
                $query->where('programme_instance_id', $programmeInstance->id);
            })
            ->orderBy('start_date')
            ->get();

        return view('programme-instances.curriculum', compact('programmeInstance', 'availableModules'));
    }

    /**
     * Attach a module instance to the programme instance curriculum
     */
    public function attachModule(Request $request, ProgrammeInstance $programmeInstance)
    {
        $validated = $request->validate([
            'module_instance_id' => 'required|exists:module_instances,id',
        ]);

        $moduleInstance = ModuleInstance::findOrFail($validated['module_instance_id']);

        // Check if already linked
        if ($programmeInstance->moduleInstances()->where('module_instance_id', $moduleInstance->id)->exists()) {
            return redirect()->route('programme-instances.curriculum', $programmeInstance)
                ->with('error', 'Module instance is already part of this programme curriculum.');
        }

        $programmeInstance->moduleInstances()->attach($moduleInstance->id);

        return redirect()->route('programme-instances.curriculum', $programmeInstance)
            ->with('success', "Module instance '{$moduleInstance->module->title}' added to curriculum successfully.");
    }

    /**
     * Detach a module instance from the programme instance curriculum
     */
    public function detachModule(ProgrammeInstance $programmeInstance, ModuleInstance $moduleInstance)
    {
        // Check if any students are enrolled in this programme with grades in this module
        $hasGrades = $programmeInstance->enrolments()
            ->whereHas('student.studentGradeRecords', function ($query) use ($moduleInstance) {
                $query->where('module_instance_id', $moduleInstance->id);
            })
            ->exists();

        if ($hasGrades) {
            return redirect()->route('programme-instances.curriculum', $programmeInstance)
                ->with('error', 'Cannot remove module instance that has student grades recorded.');
        }

        $programmeInstance->moduleInstances()->detach($moduleInstance->id);

        return redirect()->route('programme-instances.curriculum', $programmeInstance)
            ->with('success', "Module instance '{$moduleInstance->module->title}' removed from curriculum successfully.");
    }

    /**
     * Add a module instance to the programme instance curriculum (legacy method)
     */
    public function addModule(Request $request, ProgrammeInstance $programmeInstance)
    {
        return $this->attachModule($request, $programmeInstance);
    }

    /**
     * Remove a module instance from the programme instance curriculum (legacy method)
     */
    public function removeModule(ProgrammeInstance $programmeInstance, ModuleInstance $moduleInstance)
    {
        return $this->detachModule($programmeInstance, $moduleInstance);
    }
}
