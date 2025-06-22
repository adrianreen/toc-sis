<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleInstance;
use App\Models\User;
use Illuminate\Http\Request;

class ModuleInstanceController extends Controller
{
    public function index(Request $request)
    {
        $query = ModuleInstance::with(['module', 'tutor', 'studentGradeRecords.student'])
            ->withCount(['enrolments', 'programmeInstances']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('module', function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('module_code', 'LIKE', "%{$search}%");
            });
        }

        // Filter by delivery style
        if ($request->filled('delivery_style')) {
            $query->where('delivery_style', $request->delivery_style);
        }

        // Filter by tutor
        if ($request->filled('tutor_id')) {
            $query->where('tutor_id', $request->tutor_id);
        }

        // Filter by status (based on dates)
        if ($request->filled('status')) {
            $now = now();
            switch($request->status) {
                case 'upcoming':
                    $query->where('start_date', '>', $now);
                    break;
                case 'active':
                    $query->where('start_date', '<=', $now)
                          ->where(function($q) use ($now) {
                              $q->whereNull('target_end_date')
                                ->orWhere('target_end_date', '>=', $now);
                          });
                    break;
                case 'completed':
                    $query->where('target_end_date', '<', $now);
                    break;
                case 'no_tutor':
                    $query->whereNull('tutor_id');
                    break;
            }
        }

        // Filter by programme association
        if ($request->filled('programme_association')) {
            if ($request->programme_association === 'standalone') {
                $query->whereDoesntHave('programmeInstances');
            } elseif ($request->programme_association === 'programme_linked') {
                $query->whereHas('programmeInstances');
            }
        }

        // Filter by date range
        if ($request->filled('start_date_from')) {
            $query->where('start_date', '>=', $request->start_date_from);
        }
        if ($request->filled('start_date_to')) {
            $query->where('start_date', '<=', $request->start_date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'start_date');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        switch($sortBy) {
            case 'module_title':
                $query->join('modules', 'module_instances.module_id', '=', 'modules.id')
                      ->orderBy('modules.title', $sortDirection)
                      ->select('module_instances.*');
                break;
            case 'tutor_name':
                $query->leftJoin('users', 'module_instances.tutor_id', '=', 'users.id')
                      ->orderBy('users.name', $sortDirection)
                      ->select('module_instances.*');
                break;
            case 'student_count':
                $query->withCount('studentGradeRecords as student_count')
                      ->orderBy('student_count', $sortDirection);
                break;
            default:
                $query->orderBy($sortBy, $sortDirection);
        }

        $instances = $query->paginate(20)->withQueryString();

        // Get filter options
        $tutors = User::where('role', 'teacher')->orderBy('name')->get();
        $modules = \App\Models\Module::orderBy('title')->get();

        return view('module-instances.index', compact('instances', 'tutors', 'modules'));
    }

    public function create()
    {
        $modules = Module::orderBy('title')->get();
        $tutors = User::where('role', 'teacher')->orderBy('name')->get();

        return view('module-instances.create', compact('modules', 'tutors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'tutor_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'target_end_date' => 'nullable|date|after_or_equal:start_date',
            'delivery_style' => 'required|in:sync,async',
        ]);

        // Validate tutor role if provided
        if ($validated['tutor_id']) {
            $tutor = User::findOrFail($validated['tutor_id']);
            if ($tutor->role !== 'teacher') {
                return back()->withErrors(['tutor_id' => 'Selected user must be a teacher.'])
                             ->withInput();
            }
        }

        $instance = ModuleInstance::create($validated);

        return redirect()->route('module-instances.show', $instance)
            ->with('success', 'Module instance created successfully.');
    }

    public function show(ModuleInstance $moduleInstance)
    {
        $moduleInstance->load([
            'module',
            'tutor',
            'programmeInstances.programme',
            'enrolments.student',
            'studentGradeRecords.student'
        ]);

        return view('module-instances.show', compact('moduleInstance'));
    }

    public function edit(ModuleInstance $moduleInstance)
    {
        $modules = Module::orderBy('title')->get();
        $tutors = User::where('role', 'teacher')->orderBy('name')->get();

        return view('module-instances.edit', compact('moduleInstance', 'modules', 'tutors'));
    }

    public function update(Request $request, ModuleInstance $moduleInstance)
    {
        $validated = $request->validate([
            'tutor_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'target_end_date' => 'nullable|date|after_or_equal:start_date',
            'delivery_style' => 'required|in:sync,async',
        ]);

        // Validate tutor role if provided
        if ($validated['tutor_id']) {
            $tutor = User::findOrFail($validated['tutor_id']);
            if ($tutor->role !== 'teacher') {
                return back()->withErrors(['tutor_id' => 'Selected user must be a teacher.'])
                             ->withInput();
            }
        }

        $moduleInstance->update($validated);

        return redirect()->route('module-instances.show', $moduleInstance)
            ->with('success', 'Module instance updated successfully.');
    }

    public function destroy(ModuleInstance $moduleInstance)
    {
        // Check if instance has any active enrolments
        $activeEnrolments = $moduleInstance->enrolments()
            ->whereIn('status', ['active', 'deferred'])
            ->count();

        if ($activeEnrolments > 0) {
            return redirect()->route('module-instances.index')
                ->with('error', 'Cannot delete module instance with active enrolments.');
        }

        // Check if instance is part of any programme curricula
        $programmeLinks = $moduleInstance->programmeInstances()->count();
        
        if ($programmeLinks > 0) {
            return redirect()->route('module-instances.index')
                ->with('error', 'Cannot delete module instance that is part of programme curricula. Remove from programme instances first.');
        }

        $moduleInstance->delete();

        return redirect()->route('module-instances.index')
            ->with('success', 'Module instance deleted successfully.');
    }

    /**
     * Show students enrolled in this module instance
     */
    public function students(ModuleInstance $moduleInstance)
    {
        $moduleInstance->load([
            'module',
            'tutor',
            'enrolments' => function ($query) {
                $query->with('student')
                      ->where('status', 'active')
                      ->orderBy('enrolment_date');
            }
        ]);

        return view('module-instances.students', compact('moduleInstance'));
    }

    /**
     * Show grading interface for this module instance
     */
    public function grading(ModuleInstance $moduleInstance)
    {
        $moduleInstance->load([
            'module',
            'studentGradeRecords' => function ($query) {
                $query->with('student')
                      ->orderBy('assessment_component_name')
                      ->orderBy('student_id');
            }
        ]);

        // Group grade records by student and assessment component
        $gradingData = $moduleInstance->studentGradeRecords
            ->groupBy('student_id')
            ->map(function ($records) {
                return $records->keyBy('assessment_component_name');
            });

        return view('module-instances.grading', compact('moduleInstance', 'gradingData'));
    }

    /**
     * Copy an existing module instance as a template
     */
    public function copy(ModuleInstance $moduleInstance)
    {
        return view('module-instances.copy', compact('moduleInstance'));
    }

    /**
     * Store a copied module instance
     */
    public function storeCopy(Request $request, ModuleInstance $moduleInstance)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after:today',
            'target_end_date' => 'nullable|date|after_or_equal:start_date',
            'tutor_id' => 'nullable|exists:users,id',
            'delivery_style' => 'required|in:sync,async',
            'copy_enrolments' => 'boolean',
        ]);

        // Validate tutor role if provided
        if ($validated['tutor_id']) {
            $tutor = User::findOrFail($validated['tutor_id']);
            if ($tutor->role !== 'teacher') {
                return back()->withErrors(['tutor_id' => 'Selected user must be a teacher.'])
                             ->withInput();
            }
        }

        // Create the new instance
        $newInstance = ModuleInstance::create([
            'module_id' => $moduleInstance->module_id,
            'tutor_id' => $validated['tutor_id'] ?? $moduleInstance->tutor_id,
            'start_date' => $validated['start_date'],
            'target_end_date' => $validated['target_end_date'],
            'delivery_style' => $validated['delivery_style'],
        ]);

        // Copy programme instance associations if original was part of programmes
        if ($moduleInstance->programmeInstances()->exists()) {
            $programmeInstanceIds = $moduleInstance->programmeInstances()->pluck('programme_instance_id');
            $newInstance->programmeInstances()->attach($programmeInstanceIds);
        }

        // Optionally copy enrolments (usually not recommended, but available)
        if ($request->boolean('copy_enrolments')) {
            foreach ($moduleInstance->enrolments as $enrolment) {
                $newInstance->enrolments()->create([
                    'student_id' => $enrolment->student_id,
                    'enrolment_type' => $enrolment->enrolment_type,
                    'programme_instance_id' => $enrolment->programme_instance_id,
                    'enrolment_date' => now(),
                    'status' => 'active',
                ]);
            }
        }

        return redirect()->route('module-instances.show', $newInstance)
            ->with('success', 'Module instance copied successfully.' . 
                   ($request->boolean('copy_enrolments') ? ' Enrolments have been copied.' : ''));
    }

    /**
     * Create a new instance based on async cadence
     */
    public function createNext(ModuleInstance $moduleInstance)
    {
        $module = $moduleInstance->module;
        
        if (!$module->allows_standalone_enrolment) {
            return redirect()->route('module-instances.show', $moduleInstance)
                ->with('error', 'Only standalone modules can have automatic next instances created.');
        }

        // Calculate next start date based on cadence
        $nextStartDate = $this->calculateNextStartDate($moduleInstance->start_date, $module->async_instance_cadence);

        $nextInstance = ModuleInstance::create([
            'module_id' => $module->id,
            'tutor_id' => $moduleInstance->tutor_id,
            'start_date' => $nextStartDate,
            'target_end_date' => $this->calculateEndDate($nextStartDate, $module->async_instance_cadence),
            'delivery_style' => $moduleInstance->delivery_style,
        ]);

        return redirect()->route('module-instances.show', $nextInstance)
            ->with('success', 'Next module instance created successfully.');
    }

    private function calculateNextStartDate($currentStartDate, $cadence)
    {
        $date = \Carbon\Carbon::parse($currentStartDate);
        
        switch ($cadence) {
            case 'monthly':
                return $date->addMonth();
            case 'quarterly':
                return $date->addMonths(3);
            case 'bi_annually':
                return $date->addMonths(6);
            case 'annually':
                return $date->addYear();
            default:
                return $date->addMonths(3); // Default to quarterly
        }
    }

    private function calculateEndDate($startDate, $cadence)
    {
        $date = \Carbon\Carbon::parse($startDate);
        
        switch ($cadence) {
            case 'monthly':
                return $date->addMonth()->subDay();
            case 'quarterly':
                return $date->addMonths(3)->subDay();
            case 'bi_annually':
                return $date->addMonths(6)->subDay();
            case 'annually':
                return $date->addYear()->subDay();
            default:
                return $date->addMonths(3)->subDay();
        }
    }
}