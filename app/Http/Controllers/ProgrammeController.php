<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    public function index(Request $request)
    {
        $query = Programme::with(['programmeInstances' => function ($query) {
            $query->withCount('enrolments');
        }])
        ->withCount(['programmeInstances']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('awarding_body', 'LIKE', "%{$search}%");
            });
        }

        // Filter by NFQ level
        if ($request->filled('nfq_level')) {
            $query->where('nfq_level', $request->nfq_level);
        }

        // Filter by awarding body
        if ($request->filled('awarding_body')) {
            $query->where('awarding_body', $request->awarding_body);
        }

        // Filter by credit range
        if ($request->filled('credit_range')) {
            switch($request->credit_range) {
                case '60-120':
                    $query->whereBetween('total_credits', [60, 120]);
                    break;
                case '121-180':
                    $query->whereBetween('total_credits', [121, 180]);
                    break;
                case '181-240':
                    $query->whereBetween('total_credits', [181, 240]);
                    break;
                case '240+':
                    $query->where('total_credits', '>=', 240);
                    break;
            }
        }

        // Filter by instance count
        if ($request->filled('instance_count')) {
            switch($request->instance_count) {
                case 'none':
                    $query->having('programme_instances_count', '=', 0);
                    break;
                case 'low':
                    $query->having('programme_instances_count', '>=', 1)
                          ->having('programme_instances_count', '<=', 2);
                    break;
                case 'medium':
                    $query->having('programme_instances_count', '>=', 3)
                          ->having('programme_instances_count', '<=', 5);
                    break;
                case 'high':
                    $query->having('programme_instances_count', '>', 5);
                    break;
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'title');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        switch($sortBy) {
            case 'instances_count':
                $query->orderBy('programme_instances_count', $sortDirection);
                break;
            default:
                $query->orderBy($sortBy, $sortDirection);
        }

        $programmes = $query->paginate(20)->withQueryString();

        // Get filter options
        $awardingBodies = Programme::distinct()->orderBy('awarding_body')->pluck('awarding_body');
        
        return view('programmes.index', compact('programmes', 'awardingBodies'));
    }

    public function create()
    {
        return view('programmes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Programme::rules());

        $programme = Programme::create($validated);

        return redirect()->route('programmes.show', $programme)
            ->with('success', 'Programme created successfully.');
    }

    public function show(Programme $programme)
    {
        $programme->load([
            'programmeInstances' => function ($query) {
                $query->with(['moduleInstances.module'])
                      ->withCount('enrolments')
                      ->orderBy('intake_start_date', 'desc');
            }
        ]);

        return view('programmes.show', compact('programme'));
    }

    public function edit(Programme $programme)
    {
        return view('programmes.edit', compact('programme'));
    }

    public function update(Request $request, Programme $programme)
    {
        $validated = $request->validate(Programme::rules());

        $programme->update($validated);

        return redirect()->route('programmes.show', $programme)
            ->with('success', 'Programme updated successfully.');
    }

    public function destroy(Programme $programme)
    {
        // Check if programme has any active instances
        $activeInstances = $programme->programmeInstances()
            ->whereHas('enrolments', function ($query) {
                $query->whereIn('status', ['active', 'deferred']);
            })
            ->count();

        if ($activeInstances > 0) {
            return redirect()->route('programmes.index')
                ->with('error', 'Cannot delete programme with active enrolments.');
        }

        $programme->delete();

        return redirect()->route('programmes.index')
            ->with('success', 'Programme deleted successfully.');
    }
}