<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of students with pagination and filtering
     */
    public function index(Request $request)
    {
        // Start with base query
        $query = Student::with(['enrolments.programme', 'createdBy', 'updatedBy']);
        
        // Apply server-side filters if needed (optional - you can keep client-side for now)
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        
        // Order by most recent first
        $query->orderBy('created_at', 'desc');
        
        // Paginate results
        $students = $query->paginate(25);
        
        // Transform data for Alpine.js (keep your existing client-side filtering working)
        $studentsData = $students->getCollection()->map(function ($student) {
            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'full_name' => $student->full_name,
                'email' => $student->email,
                'status' => $student->status,
                'programmes' => $student->enrolments->pluck('programme.code')->unique()->values(),
                'created_at' => $student->created_at->format('d M Y'),
            ];
        });
        
        // Get programmes for filter dropdown
        $programmes = Programme::select('code', 'title')->orderBy('code')->get();
        
        return view('students.index', [
            'students' => $students,
            'studentsData' => $studentsData,
            'programmes' => $programmes,
        ]);
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'eircode' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'notes' => 'nullable|string',
        ]);

        // Generate student number
        $validated['student_number'] = Student::generateStudentNumber();
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $student = Student::create($validated);

        // Log the activity
        activity()
            ->causedBy(Auth::user())
            ->performedOn($student)
            ->log('Student created');

        return redirect()->route('students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        $student->load(['enrolments.programme', 'enrolments.cohort', 'createdBy', 'updatedBy']);
        
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('students', 'email')->ignore($student->id)
            ],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'eircode' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'status' => 'required|in:enquiry,enrolled,active,deferred,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = Auth::id();

        // Track what changed for activity log
        $changes = [];
        foreach ($validated as $key => $value) {
            if ($student->$key != $value) {
                $changes[$key] = [
                    'old' => $student->$key,
                    'new' => $value
                ];
            }
        }

        $student->update($validated);

        // Log the activity with changes
        if (!empty($changes)) {
            activity()
                ->causedBy(Auth::user())
                ->performedOn($student)
                ->withProperties(['changes' => $changes])
                ->log('Student updated');
        }

        return redirect()->route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student from storage
     */
    public function destroy(Student $student)
    {
        // Check if student has any active enrolments
        $activeEnrolments = $student->enrolments()->whereIn('status', ['active', 'deferred'])->count();
        
        if ($activeEnrolments > 0) {
            // Determine where to redirect back to
            $redirectRoute = request()->header('referer') && str_contains(request()->header('referer'), '/students/' . $student->id) 
                ? 'students.show' 
                : 'students.index';
                
            if ($redirectRoute === 'students.show') {
                return redirect()->route('students.show', $student)
                    ->with('error', "Cannot delete student with {$activeEnrolments} active enrolment(s). Please complete or cancel their enrolments first.");
            }
            
            return redirect()->route('students.index')
                ->with('error', "Cannot delete {$student->full_name} - they have {$activeEnrolments} active enrolment(s). Please complete or cancel their enrolments first.");
        }

        // Store student name for success message
        $studentName = $student->full_name;
        
        // Log the activity before deletion
        activity()
            ->causedBy(Auth::user())
            ->performedOn($student)
            ->log('Student moved to recycle bin');

        // Perform soft delete
        $student->delete();

        // Determine where to redirect back to
        $redirectRoute = request()->header('referer') && str_contains(request()->header('referer'), '/students/' . $student->id) 
            ? 'students.index'  // Always redirect to index if coming from show page (since student page no longer exists)
            : 'students.index';

        return redirect()->route('students.index')
            ->with('success', "Student {$studentName} moved to recycle bin successfully. You can restore them from the recycle bin if needed.");
    }

    /**
     * Show deleted students (recycle bin)
     */
    public function recycleBin()
    {
        $deletedStudents = Student::onlyTrashed()
            ->with(['enrolments.programme', 'createdBy', 'updatedBy'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(25);

        return view('students.recycle-bin', compact('deletedStudents'));
    }

    /**
     * Restore a soft-deleted student
     */
    public function restore($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);

        // Log the activity before restoration
        activity()
            ->causedBy(Auth::user())
            ->performedOn($student)
            ->log('Student restored from recycle bin');

        $student->restore();

        return redirect()->route('students.recycle-bin')
            ->with('success', "Student {$student->full_name} restored successfully.");
    }

    /**
     * Permanently delete a student
     */
    public function forceDelete($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);

        // Check if student has any enrolments (should never happen, but safety check)
        if ($student->enrolments()->exists()) {
            return redirect()->route('students.recycle-bin')
                ->with('error', 'Cannot permanently delete student with existing enrolments.');
        }

        // Log the activity before permanent deletion
        activity()
            ->causedBy(Auth::user())
            ->performedOn($student)
            ->log('Student permanently deleted');

        $student->forceDelete();

        return redirect()->route('students.recycle-bin')
            ->with('success', 'Student permanently deleted.');
    }

    /**
     * Search students for dashboard AJAX search
     */
    public function search(Request $request)
    {
        $query = Student::with(['enrolments.programme']);
        
        // Get search term from either 'search' or 'q' parameter
        $search = $request->get('search') ?: $request->get('q');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        
        if ($request->filled('programme')) {
            $query->whereHas('enrolments.programme', function($q) use ($request) {
                $q->where('code', $request->get('programme'));
            });
        }
        
        // Limit results for dashboard search (top 20 matches)
        $students = $query->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
            
        return response()->json([
            'students' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_number' => $student->student_number,
                    'full_name' => $student->full_name,
                    'email' => $student->email,
                    'status' => $student->status,
                    'programmes' => $student->enrolments->pluck('programme.code')->unique()->values(),
                    'created_at' => $student->created_at->format('d M Y'),
                ];
            }),
            'total' => $students->count()
        ]);
    }


    /**
     * Bulk operations (for future implementation)
     */
    /*
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'action' => 'required|in:status_change,bulk_email,export',
            'status' => 'required_if:action,status_change|in:enquiry,enrolled,active,deferred,completed,cancelled',
        ]);
        
        $students = Student::whereIn('id', $validated['student_ids'])->get();
        
        switch ($validated['action']) {
            case 'status_change':
                foreach ($students as $student) {
                    $student->update([
                        'status' => $validated['status'],
                        'updated_by' => Auth::id()
                    ]);
                    
                    activity()
                        ->causedBy(Auth::user())
                        ->performedOn($student)
                        ->log("Status changed to {$validated['status']} via bulk update");
                }
                
                return response()->json([
                    'message' => "Updated {$students->count()} students successfully."
                ]);
                
            case 'export':
                // Handle export logic
                break;
                
            case 'bulk_email':
                // Handle bulk email logic
                break;
        }
        
        return response()->json(['message' => 'Operation completed successfully.']);
    }
    */
}