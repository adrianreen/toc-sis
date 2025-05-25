<?php
// app/Http/Controllers/CohortController.php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\Programme;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CohortController extends Controller
{
    public function index()
    {
        $cohorts = Cohort::with('programme')
            ->orderBy('start_date', 'desc')
            ->paginate(20);
            
        return view('cohorts.index', compact('cohorts'));
    }

    public function create()
    {
        $programmes = Programme::where('enrolment_type', 'cohort')
            ->where('is_active', true)
            ->get();
            
        return view('cohorts.create', compact('programmes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:planned,active,completed',
        ]);

        // Check for unique code within programme
        $exists = Cohort::where('programme_id', $validated['programme_id'])
            ->where('code', $validated['code'])
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['code' => 'This cohort code already exists for this programme.']);
        }

        $cohort = Cohort::create($validated);

        return redirect()->route('cohorts.show', $cohort)
            ->with('success', 'Cohort created successfully.');
    }

    public function show(Cohort $cohort)
    {
        $cohort->load(['programme', 'enrolments.student']);
        return view('cohorts.show', compact('cohort'));
    }

    public function edit(Cohort $cohort)
    {
        $programmes = Programme::where('enrolment_type', 'cohort')
            ->where('is_active', true)
            ->get();
            
        return view('cohorts.edit', compact('cohort', 'programmes'));
    }

    public function update(Request $request, Cohort $cohort)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:planned,active,completed',
        ]);

        $cohort->update($validated);

        return redirect()->route('cohorts.show', $cohort)
            ->with('success', 'Cohort updated successfully.');
    }

    // Helper method to generate cohort code
    public static function generateCohortCode($date)
    {
        $carbon = Carbon::parse($date);
        return $carbon->format('ym'); // 2501 for Jan 2025
    }
}