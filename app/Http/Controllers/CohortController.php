<?php
// app/Http/Controllers/CohortController.php

namespace App\Http\Controllers;

use App\Models\Cohort;
use App\Models\Programme;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CohortController extends Controller
{
    public function index(Request $request)
    {
        $query = Cohort::with('programme');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('programme', function($programme) use ($search) {
                      $programme->where('title', 'like', "%{$search}%")
                               ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('programme_id')) {
            $query->where('programme_id', $request->get('programme_id'));
        }

        if ($request->filled('year')) {
            $year = $request->get('year');
            $query->whereYear('start_date', $year);
        }

        $cohorts = $query->orderBy('start_date', 'desc')->paginate(20);
        
        // Get data for filter dropdowns
        $programmes = Programme::where('enrolment_type', 'cohort')
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
            
        $years = Cohort::selectRaw('YEAR(start_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Prepare cohort data for client-side filtering (all cohorts for live filtering)
        $allCohorts = Cohort::with('programme')
            ->get()
            ->map(function($cohort) {
                return [
                    'id' => $cohort->id,
                    'code' => $cohort->code,
                    'name' => $cohort->name,
                    'full_name' => $cohort->full_name,
                    'display_name' => $cohort->display_name,
                    'status' => $cohort->status,
                    'start_date' => $cohort->start_date->format('d M Y'),
                    'end_date' => $cohort->end_date ? $cohort->end_date->format('d M Y') : null,
                    'start_year' => $cohort->start_date->format('Y'),
                    'students_count' => $cohort->enrolments_count ?? 0,
                    'programme' => [
                        'id' => $cohort->programme->id,
                        'code' => $cohort->programme->code,
                        'title' => $cohort->programme->title,
                    ]
                ];
            });
            
        return view('cohorts.index', compact('cohorts', 'programmes', 'years', 'allCohorts'));
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