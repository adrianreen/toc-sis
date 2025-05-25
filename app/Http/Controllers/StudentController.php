<?php
// app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['createdBy', 'updatedBy'])
            ->latest()
            ->paginate(20);
            
        return view('students.index', compact('students'));
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
}