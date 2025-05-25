<?php
// app/Http/Controllers/ModuleController.php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Programme;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::with('programmes')
            ->orderBy('code')
            ->paginate(20);
            
        return view('modules.index', compact('modules'));
    }

    public function create()
    {
        $programmes = Programme::where('is_active', true)->get();
        return view('modules.create', compact('programmes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:modules',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:0',
            'hours' => 'nullable|integer|min:0',
            'programme_ids' => 'nullable|array',
            'programme_ids.*' => 'exists:programmes,id',
        ]);

        $module = Module::create($validated);

        // Attach to programmes
        if (!empty($validated['programme_ids'])) {
            foreach ($validated['programme_ids'] as $index => $programmeId) {
                $module->programmes()->attach($programmeId, [
                    'sequence' => $index + 1,
                    'is_mandatory' => true
                ]);
            }
        }

        return redirect()->route('modules.show', $module)
            ->with('success', 'Module created successfully.');
    }

    public function show(Module $module)
    {
        $module->load('programmes');
        return view('modules.show', compact('module'));
    }

    public function edit(Module $module)
    {
        $programmes = Programme::where('is_active', true)->get();
        $attachedProgrammes = $module->programmes->pluck('id')->toArray();
        
        return view('modules.edit', compact('module', 'programmes', 'attachedProgrammes'));
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:0',
            'hours' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'programme_ids' => 'nullable|array',
            'programme_ids.*' => 'exists:programmes,id',
        ]);

        $module->update($validated);

        // Sync programmes
        $module->programmes()->detach();
        if (!empty($validated['programme_ids'])) {
            foreach ($validated['programme_ids'] as $index => $programmeId) {
                $module->programmes()->attach($programmeId, [
                    'sequence' => $index + 1,
                    'is_mandatory' => true
                ]);
            }
        }

        return redirect()->route('modules.show', $module)
            ->with('success', 'Module updated successfully.');
    }
}