<?php
// app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Programme;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        // Get all programmes for filter dropdown
        $programmes = Programme::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'title']);

        // Get all students with their enrolment data
        // Note: For large datasets (1000+ students), consider implementing 
        // server-side search with AJAX to improve performance
        $students = Student::with(['enrolments.programme'])
            ->latest()
            ->get();

        // Structure data for Alpine.js component
        $studentsData = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'full_name' => $student->full_name,
                'email' => $student->email,
                'status' => $student->status,
                'programmes' => $student->enrolments
                    ->where('status', '!=', 'cancelled')
                    ->pluck('programme.code')
                    ->unique()
                    ->values()
                    ->toArray()
            ];
        });

        return view('students.index', compact('studentsData', 'programmes'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'eircode' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['student_number'] = Student::generateStudentNumber();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $validated['status'] = 'enquiry';

        $student = Student::create($validated);

        return redirect()->route('students.show', $student->id)
            ->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $student->load(['enrolments.programme', 'enrolments.cohort']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'eircode' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date',
            'status' => 'required|in:enquiry,enrolled,active,deferred,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $student->update($validated);

        return redirect()->route('students.show', $student->id)
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Future: Server-side search endpoint for AJAX implementation
     * Use this if client-side filtering becomes too slow with large datasets
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $programme = $request->get('programme', '');
        
        $query = Student::with(['enrolments.programme']);
        
        // Apply search filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                  ->orWhere('student_number', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($programme) {
            $query->whereHas('enrolments.programme', function ($q) use ($programme) {
                $q->where('code', $programme);
            });
        }
        
        $students = $query->latest()->get();
        
        $studentsData = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'full_name' => $student->full_name,
                'email' => $student->email,
                'status' => $student->status,
                'programmes' => $student->enrolments
                    ->where('status', '!=', 'cancelled')
                    ->pluck('programme.code')
                    ->unique()
                    ->values()
                    ->toArray()
            ];
        });
        
        return response()->json([
            'students' => $studentsData,
            'total' => $studentsData->count()
        ]);
    }
}