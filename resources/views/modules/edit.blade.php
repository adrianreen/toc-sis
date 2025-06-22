{{-- resources/views/modules/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Module: {{ $module->module_code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('modules.update', $module) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Module Code -->
                            <div>
                                <label for="module_code" class="block text-sm font-medium text-gray-700">Module Code *</label>
                                <input type="text" name="module_code" id="module_code" value="{{ old('module_code', $module->module_code) }}" required
                                    placeholder="e.g., ELC501, ELC502"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('module_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Module Title *</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $module->title) }}" required
                                    placeholder="e.g., Child Development"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Credit Value -->
                            <div>
                                <label for="credit_value" class="block text-sm font-medium text-gray-700">Credit Value *</label>
                                <input type="number" name="credit_value" id="credit_value" value="{{ old('credit_value', $module->credit_value) }}" required min="1"
                                    placeholder="e.g., 5, 10, 15"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('credit_value')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NFQ Level -->
                            <div>
                                <label for="nfq_level" class="block text-sm font-medium text-gray-700">NFQ Level</label>
                                <select name="nfq_level" id="nfq_level"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select NFQ Level</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('nfq_level', $module->nfq_level) == $i ? 'selected' : '' }}>
                                            Level {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @error('nfq_level')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                placeholder="Brief description of the module"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $module->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assessment Strategy -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assessment Strategy</label>
                            <div class="border rounded-md p-4">
                                <div id="assessment-components">
                                    @if($module->assessment_strategy && count($module->assessment_strategy) > 0)
                                        @foreach($module->assessment_strategy as $index => $component)
                                            <div class="assessment-component grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600">Component Name</label>
                                                    <input type="text" name="assessment_strategy[{{ $index }}][component_name]" 
                                                        value="{{ old('assessment_strategy.' . $index . '.component_name', $component['component_name']) }}"
                                                        placeholder="e.g., Assignment 1"
                                                        class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600">Weighting (%)</label>
                                                    <input type="number" name="assessment_strategy[{{ $index }}][weighting]" min="1" max="100"
                                                        value="{{ old('assessment_strategy.' . $index . '.weighting', $component['weighting']) }}"
                                                        placeholder="40"
                                                        class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600">Pass Mark (%)</label>
                                                    <input type="number" name="assessment_strategy[{{ $index }}][component_pass_mark]" min="1" max="100"
                                                        value="{{ old('assessment_strategy.' . $index . '.component_pass_mark', $component['component_pass_mark'] ?? 40) }}"
                                                        placeholder="40"
                                                        class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                                </div>
                                                <div class="flex items-center">
                                                    <label class="flex items-center text-sm">
                                                        <input type="checkbox" name="assessment_strategy[{{ $index }}][is_must_pass]" value="1"
                                                            {{ old('assessment_strategy.' . $index . '.is_must_pass', $component['is_must_pass'] ?? false) ? 'checked' : '' }}
                                                            class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                                        <span class="ml-2">Must Pass</span>
                                                    </label>
                                                </div>
                                                <div class="flex items-center">
                                                    <button type="button" class="remove-component text-red-600 hover:text-red-900 text-sm">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="assessment-component grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600">Component Name</label>
                                                <input type="text" name="assessment_strategy[0][component_name]" 
                                                    placeholder="e.g., Assignment 1"
                                                    class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600">Weighting (%)</label>
                                                <input type="number" name="assessment_strategy[0][weighting]" min="1" max="100"
                                                    placeholder="40"
                                                    class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600">Pass Mark (%)</label>
                                                <input type="number" name="assessment_strategy[0][component_pass_mark]" min="1" max="100"
                                                    placeholder="40" value="40"
                                                    class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                            </div>
                                            <div class="flex items-center">
                                                <label class="flex items-center text-sm">
                                                    <input type="checkbox" name="assessment_strategy[0][is_must_pass]" value="1"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                                    <span class="ml-2">Must Pass</span>
                                                </label>
                                            </div>
                                            <div></div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" id="add-component" class="text-sm bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded">
                                    Add Component
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Changes to assessment strategy may affect existing module instances.</p>
                            </div>
                        </div>

                        <!-- Module Settings -->
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-4">Module Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Standalone Enrolment -->
                                <div>
                                    <label for="allows_standalone_enrolment" class="block text-sm font-medium text-gray-700">Allows Standalone Enrolment</label>
                                    <select name="allows_standalone_enrolment" id="allows_standalone_enrolment" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="0" {{ old('allows_standalone_enrolment', $module->allows_standalone_enrolment) == 0 ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('allows_standalone_enrolment', $module->allows_standalone_enrolment) == 1 ? 'selected' : '' }}>Yes</option>
                                    </select>
                                    @error('allows_standalone_enrolment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Async Instance Cadence -->
                                <div>
                                    <label for="async_instance_cadence" class="block text-sm font-medium text-gray-700">Async Instance Cadence</label>
                                    <select name="async_instance_cadence" id="async_instance_cadence" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="monthly" {{ old('async_instance_cadence', $module->async_instance_cadence) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="quarterly" {{ old('async_instance_cadence', $module->async_instance_cadence) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        <option value="bi_annually" {{ old('async_instance_cadence', $module->async_instance_cadence) == 'bi_annually' ? 'selected' : '' }}>Bi-Annually</option>
                                        <option value="annually" {{ old('async_instance_cadence', $module->async_instance_cadence) == 'annually' ? 'selected' : '' }}>Annually</option>
                                    </select>
                                    @error('async_instance_cadence')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Current Statistics -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Current Module Statistics</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Module Instances:</span>
                                    <span class="font-medium ml-1">{{ $module->moduleInstances->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Total Students:</span>
                                    <span class="font-medium ml-1">{{ $module->moduleInstances->sum(function($instance) { return $instance->studentGradeRecords->pluck('student_id')->unique()->count(); }) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Grade Records:</span>
                                    <span class="font-medium ml-1">{{ $module->moduleInstances->sum(function($instance) { return $instance->studentGradeRecords->count(); }) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('modules.show', $module) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    let componentIndex = {{ $module->assessment_strategy ? count($module->assessment_strategy) : 1 }};

    document.getElementById('add-component').addEventListener('click', function() {
        const container = document.getElementById('assessment-components');
        const newComponent = document.createElement('div');
        newComponent.className = 'assessment-component grid grid-cols-1 md:grid-cols-5 gap-4 mb-4';
        newComponent.innerHTML = `
            <div>
                <label class="block text-xs font-medium text-gray-600">Component Name</label>
                <input type="text" name="assessment_strategy[${componentIndex}][component_name]" 
                    placeholder="e.g., Assignment ${componentIndex + 1}"
                    class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Weighting (%)</label>
                <input type="number" name="assessment_strategy[${componentIndex}][weighting]" min="1" max="100"
                    placeholder="40"
                    class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Pass Mark (%)</label>
                <input type="number" name="assessment_strategy[${componentIndex}][component_pass_mark]" min="1" max="100"
                    placeholder="40" value="40"
                    class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="flex items-center">
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="assessment_strategy[${componentIndex}][is_must_pass]" value="1"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm">
                    <span class="ml-2">Must Pass</span>
                </label>
            </div>
            <div class="flex items-center">
                <button type="button" class="remove-component text-red-600 hover:text-red-900 text-sm">
                    Remove
                </button>
            </div>
        `;
        container.appendChild(newComponent);
        componentIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-component')) {
            e.target.closest('.assessment-component').remove();
        }
    });
    </script>
</x-app-layout>