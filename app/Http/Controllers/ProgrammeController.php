<?php
// app/Http/Controllers/ProgrammeController.php

namespace App\Http\Controllers;

use App\Models\Programme;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    public function index()
    {
        $programmes = Programme::with('cohorts')->get();
        return view('programmes.index', compact('programmes'));
    }

    public function create()
    {
        return view('programmes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:programmes',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'enrolment_type' => 'required|in:cohort,rolling,academic_term',
            'settings' => 'nullable|array',
        ]);

        $programme = Programme::create($validated);

        return redirect()->route('programmes.show', $programme)
            ->with('success', 'Programme created successfully.');
    }

    public function show(Programme $programme)
    {
        $programme->load(['cohorts' => function ($query) {
            $query->withCount('enrolments');
        }, 'modules']);
        return view('programmes.show', compact('programme'));
    }

    public function edit(Programme $programme)
    {
        return view('programmes.edit', compact('programme'));
    }

    public function update(Request $request, Programme $programme)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'enrolment_type' => 'required|in:cohort,rolling,academic_term',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $programme->update($validated);

        return redirect()->route('programmes.show', $programme)
            ->with('success', 'Programme updated successfully.');
    }
}