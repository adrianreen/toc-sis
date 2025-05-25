<?php
// app/Http/Controllers/ModuleInstanceController.php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Cohort;
use App\Models\ModuleInstance;
use App\Models\User;
use Illuminate\Http\Request;

class ModuleInstanceController extends Controller
{
    public function index()
    {
        $instances = ModuleInstance::with(['module', 'cohort', 'teacher'])
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        return view('module-instances.index', compact('instances'));
    }

    public function create()
    {
        $modules = Module::where('is_active', true)->get();
        $cohorts = Cohort::where('status', '!=', 'completed')->get();
        $teachers = User::where('role', 'teacher')->get();

        return view('module-instances.create', compact('modules', 'cohorts', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'teacher_id' => 'nullable|exists:users,id',
            'status' => 'required|in:planned,active,completed',
        ]);

        // Generate instance code
        $module = Module::find($validated['module_id']);
        $cohort = Cohort::find($validated['cohort_id']);
        $validated['instance_code'] = ModuleInstance::generateInstanceCode($module->code, $cohort->code);

        // Check if instance already exists
        if (ModuleInstance::where('instance_code', $validated['instance_code'])->exists()) {
            return back()->withErrors(['module_id' => 'This module instance already exists for this cohort.']);
        }

        $instance = ModuleInstance::create($validated);

        // Auto-enrol students from the cohort
        $this->autoEnrolStudents($instance, $cohort);

        return redirect()->route('module-instances.show', $instance)
            ->with('success', 'Module instance created successfully.');
    }

    public function assignTeacher(Request $request, ModuleInstance $instance)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);

        $oldTeacher = $instance->teacher;
        $instance->update(['teacher_id' => $validated['teacher_id']]);

        // Log teacher change
        activity()
            ->performedOn($instance)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_teacher_id' => $oldTeacher?->id,
                'new_teacher_id' => $validated['teacher_id'],
            ])
            ->log('Teacher assignment changed for ' . $instance->instance_code);

        return back()->with('success', 'Teacher assigned successfully.');
    }

    private function autoEnrolStudents(ModuleInstance $instance, Cohort $cohort)
    {
        // Get all active enrolments for this cohort
        $enrolments = $cohort->enrolments()
            ->where('status', 'active')
            ->get();

        foreach ($enrolments as $enrolment) {
            \App\Models\StudentModuleEnrolment::create([
                'student_id' => $enrolment->student_id,
                'enrolment_id' => $enrolment->id,
                'module_instance_id' => $instance->id,
                'status' => 'enrolled',
                'attempt_number' => 1,
            ]);
        }
    }
}