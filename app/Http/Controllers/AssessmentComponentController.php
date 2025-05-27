<?php
// app/Http/Controllers/AssessmentComponentController.php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\AssessmentComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentComponentController extends Controller
{
    public function index(Module $module)
    {
        $components = $module->assessmentComponents()
            ->orderBy('sequence')
            ->get();

        $totalWeight = $components->sum('weight');

        return view('assessment-components.index', compact('module', 'components', 'totalWeight'));
    }

    public function create(Module $module)
    {
        return view('assessment-components.create', compact('module'));
    }

    public function store(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:assignment,exam,project,presentation,other',
            'weight' => 'required|numeric|min:0|max:100',
            'sequence' => 'required|integer|min:1',
        ]);

        // Check total weight doesn't exceed 100%
        $currentWeight = $module->assessmentComponents()->sum('weight');
        if ($currentWeight + $validated['weight'] > 100) {
            return back()->withErrors(['weight' => 'Total weight would exceed 100%. Current total: ' . $currentWeight . '%']);
        }

        $validated['module_id'] = $module->id;
        $validated['is_active'] = true;

        $component = AssessmentComponent::create($validated);

        activity()
            ->performedOn($component)
            ->causedBy(auth()->user())
            ->log('Assessment component created: ' . $component->name);

        return redirect()->route('assessment-components.index', $module)
            ->with('success', 'Assessment component created successfully.');
    }

    public function edit(Module $module, AssessmentComponent $assessmentComponent)
    {
        // Verify component belongs to module
        if ($assessmentComponent->module_id !== $module->id) {
            abort(404);
        }

        return view('assessment-components.edit', compact('module', 'assessmentComponent'));
    }

    public function update(Request $request, Module $module, AssessmentComponent $assessmentComponent)
    {
        // Verify component belongs to module
        if ($assessmentComponent->module_id !== $module->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:assignment,exam,project,presentation,other',
            'weight' => 'required|numeric|min:0|max:100',
            'sequence' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Check total weight doesn't exceed 100%
        $currentWeight = $module->assessmentComponents()
            ->where('id', '!=', $assessmentComponent->id)
            ->sum('weight');
            
        if ($currentWeight + $validated['weight'] > 100) {
            return back()->withErrors(['weight' => 'Total weight would exceed 100%. Current total (excluding this component): ' . $currentWeight . '%']);
        }

        DB::transaction(function () use ($validated, $assessmentComponent) {
            $assessmentComponent->update($validated);

            activity()
                ->performedOn($assessmentComponent)
                ->causedBy(auth()->user())
                ->log('Assessment component updated: ' . $assessmentComponent->name);
        });

        return redirect()->route('assessment-components.index', $module)
            ->with('success', 'Assessment component updated successfully.');
    }

    public function destroy(Module $module, AssessmentComponent $assessmentComponent)
    {
        // Verify component belongs to module
        if ($assessmentComponent->module_id !== $module->id) {
            abort(404);
        }

        // Check if any student assessments exist
        if ($assessmentComponent->studentAssessments()->exists()) {
            return back()->withErrors(['delete' => 'Cannot delete component that has student assessments.']);
        }

        $name = $assessmentComponent->name;
        $assessmentComponent->delete();

        activity()
            ->causedBy(auth()->user())
            ->log('Assessment component deleted: ' . $name . ' from module ' . $module->code);

        return redirect()->route('assessment-components.index', $module)
            ->with('success', 'Assessment component deleted successfully.');
    }

    public function reorder(Request $request, Module $module)
    {
        $validated = $request->validate([
            'components' => 'required|array',
            'components.*.id' => 'required|exists:assessment_components,id',
            'components.*.sequence' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $module) {
            foreach ($validated['components'] as $component) {
                AssessmentComponent::where('id', $component['id'])
                    ->where('module_id', $module->id)
                    ->update(['sequence' => $component['sequence']]);
            }
        });

        return response()->json(['success' => true]);
    }
}