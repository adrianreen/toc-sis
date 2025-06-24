<?php

namespace App\Http\Controllers;

use App\Models\Extension;
use App\Models\Student;
use App\Models\StudentGradeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtensionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:manager,student_services,teacher']);
    }

    public function index()
    {
        $query = Extension::with([
            'student',
            'studentGradeRecord.moduleInstance.module',
            'studentGradeRecord.moduleInstance.tutor',
            'requestedBy',
            'approvedBy',
        ]);

        if (auth()->user()->role === 'teacher') {
            // Teachers only see extensions for their modules
            $query->whereHas('studentGradeRecord.moduleInstance', function ($q) {
                $q->where('tutor_id', auth()->id());
            });
        }

        $extensions = $query->latest()->paginate(20);

        return view('extensions.index', compact('extensions'));
    }

    public function create(Student $student)
    {
        // Get student's active grade records that could need extensions
        $gradeRecords = StudentGradeRecord::where('student_id', $student->id)
            ->whereNull('grade') // Not yet graded
            ->with(['moduleInstance.module', 'moduleInstance.tutor'])
            ->get()
            ->filter(function ($record) {
                // Only show records where student has active enrolment
                return $student->enrolments()
                    ->where('status', 'active')
                    ->where(function ($query) use ($record) {
                        $query->where('programme_instance_id', $record->moduleInstance->programmeInstances()->first()?->id)
                            ->orWhere('module_instance_id', $record->module_instance_id);
                    })
                    ->exists();
            });

        return view('extensions.create', compact('student', 'gradeRecords'));
    }

    public function store(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_grade_record_id' => 'required|exists:student_grade_records,id',
            'new_due_date' => 'required|date|after:today',
            'reason' => 'required|string|max:1000',
        ]);

        $gradeRecord = StudentGradeRecord::findOrFail($validated['student_grade_record_id']);

        // Verify grade record belongs to student
        if ($gradeRecord->student_id !== $student->id) {
            abort(403, 'Grade record does not belong to this student.');
        }

        // Check if extension already exists for this grade record
        $existingExtension = Extension::where('student_grade_record_id', $gradeRecord->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingExtension) {
            return back()->withErrors([
                'student_grade_record_id' => 'An extension already exists for this assessment component.',
            ]);
        }

        DB::transaction(function () use ($validated, $student, $gradeRecord) {
            Extension::create([
                'student_grade_record_id' => $gradeRecord->id,
                'student_id' => $student->id,
                'original_due_date' => now(), // In new architecture, we don't have explicit due dates, use current date
                'new_due_date' => $validated['new_due_date'],
                'reason' => $validated['reason'],
                'requested_by' => auth()->id(),
                'status' => 'pending',
            ]);

            activity()
                ->performedOn($student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'grade_record_id' => $gradeRecord->id,
                    'module' => $gradeRecord->moduleInstance->module->title,
                    'assessment_component' => $gradeRecord->assessment_component_name,
                ])
                ->log("Extension requested for {$gradeRecord->assessment_component_name} in {$gradeRecord->moduleInstance->module->title}");
        });

        return redirect()->route('extensions.index')
            ->with('success', 'Extension request created successfully.');
    }

    public function approve(Extension $extension)
    {
        $this->authorize('update', $extension);

        DB::transaction(function () use ($extension) {
            $extension->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // In the new architecture, we don't update due dates on grade records
            // Extensions are tracked separately and handled by business logic

            activity()
                ->performedOn($extension->student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'extension_id' => $extension->id,
                    'new_due_date' => $extension->new_due_date->format('Y-m-d'),
                    'module' => $extension->studentGradeRecord->moduleInstance->module->title,
                    'assessment_component' => $extension->studentGradeRecord->assessment_component_name,
                ])
                ->log("Extension approved - new due date: {$extension->new_due_date->format('d M Y')}");
        });

        return back()->with('success', 'Extension approved successfully.');
    }

    public function reject(Extension $extension, Request $request)
    {
        $this->authorize('update', $extension);

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($extension, $validated) {
            $extension->update([
                'status' => 'rejected',
                'admin_notes' => $validated['admin_notes'],
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            activity()
                ->performedOn($extension->student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'extension_id' => $extension->id,
                    'rejection_reason' => $validated['admin_notes'],
                    'module' => $extension->studentGradeRecord->moduleInstance->module->title,
                    'assessment_component' => $extension->studentGradeRecord->assessment_component_name,
                ])
                ->log("Extension rejected for {$extension->studentGradeRecord->assessment_component_name}");
        });

        return back()->with('success', 'Extension rejected.');
    }

    public function show(Extension $extension)
    {
        $extension->load([
            'student',
            'studentGradeRecord.moduleInstance.module',
            'studentGradeRecord.moduleInstance.tutor',
            'requestedBy',
            'approvedBy',
        ]);

        return view('extensions.show', compact('extension'));
    }

    public function destroy(Extension $extension)
    {
        $this->authorize('delete', $extension);

        if ($extension->status === 'approved') {
            return back()->withErrors(['error' => 'Cannot delete an approved extension.']);
        }

        DB::transaction(function () use ($extension) {
            activity()
                ->performedOn($extension->student)
                ->causedBy(auth()->user())
                ->withProperties([
                    'extension_id' => $extension->id,
                    'module' => $extension->studentGradeRecord->moduleInstance->module->title,
                    'assessment_component' => $extension->studentGradeRecord->assessment_component_name,
                ])
                ->log("Extension request deleted for {$extension->studentGradeRecord->assessment_component_name}");

            $extension->delete();
        });

        return back()->with('success', 'Extension request deleted.');
    }
}
