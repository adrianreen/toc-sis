{{-- resources/views/modules/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New Module
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('modules.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Module Code -->
                            <div>
                                <label for="module_code" class="block text-sm font-medium text-gray-700">Module Code *</label>
                                <input type="text" name="module_code" id="module_code" value="{{ old('module_code') }}" required
                                    placeholder="e.g., ELC501, ELC502"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('module_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Module Title *</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                    placeholder="e.g., Child Development"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Credit Value -->
                            <div>
                                <label for="credit_value" class="block text-sm font-medium text-gray-700">Credit Value *</label>
                                <input type="number" name="credit_value" id="credit_value" value="{{ old('credit_value', 5) }}" required min="1"
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
                                        <option value="{{ $i }}" {{ old('nfq_level') == $i ? 'selected' : '' }}>
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Module Options -->
                        <div class="mt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Allows Standalone Enrolment -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Standalone Enrolment *</label>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="allows_standalone_enrolment" value="1" {{ old('allows_standalone_enrolment', '0') == '1' ? 'checked' : '' }}
                                                class="form-radio text-indigo-600">
                                            <span class="ml-2">Yes - Allow standalone enrolment</span>
                                        </label>
                                    </div>
                                    <div class="mt-1">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="allows_standalone_enrolment" value="0" {{ old('allows_standalone_enrolment', '0') == '0' ? 'checked' : '' }}
                                                class="form-radio text-indigo-600">
                                            <span class="ml-2">No - Programme only</span>
                                        </label>
                                    </div>
                                    @error('allows_standalone_enrolment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Async Instance Cadence -->
                                <div>
                                    <label for="async_instance_cadence" class="block text-sm font-medium text-gray-700">Async Instance Cadence *</label>
                                    <select name="async_instance_cadence" id="async_instance_cadence" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select Cadence</option>
                                        <option value="monthly" {{ old('async_instance_cadence') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="quarterly" {{ old('async_instance_cadence') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        <option value="bi_annually" {{ old('async_instance_cadence') == 'bi_annually' ? 'selected' : '' }}>Bi-Annually</option>
                                        <option value="annually" {{ old('async_instance_cadence') == 'annually' ? 'selected' : '' }}>Annually</option>
                                    </select>
                                    @error('async_instance_cadence')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Assessment Strategy -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assessment Strategy *</label>
                            <div class="border rounded-md p-4">
                                <div id="assessment-components">
                                    <div class="assessment-component grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">Component Name *</label>
                                            <input type="text" name="assessment_components[0][component_name]" required
                                                placeholder="e.g., Assignment 1" value="{{ old('assessment_components.0.component_name') }}"
                                                class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">Weighting (%) *</label>
                                            <input type="number" name="assessment_components[0][weighting]" min="1" max="100" required
                                                placeholder="40" value="{{ old('assessment_components.0.weighting') }}"
                                                class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600">Pass Mark (%)</label>
                                            <input type="number" name="assessment_components[0][component_pass_mark]" min="1" max="100"
                                                placeholder="40" value="{{ old('assessment_components.0.component_pass_mark', 40) }}"
                                                class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                                        </div>
                                        <div class="flex items-center">
                                            <label class="flex items-center text-sm">
                                                <input type="hidden" name="assessment_components[0][is_must_pass]" value="0">
                                                <input type="checkbox" name="assessment_components[0][is_must_pass]" value="1"
                                                    {{ old('assessment_components.0.is_must_pass') ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                                <span class="ml-2">Must Pass</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="add-component" class="text-sm bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded">
                                    Add Component
                                </button>
                                <button type="button" id="remove-component" class="text-sm bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded ml-2">
                                    Remove Last
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Total weighting must equal 100%.</p>
                                @error('assessment_components')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('modules.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Module
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let componentIndex = 1;

        document.getElementById('add-component').addEventListener('click', function() {
            const container = document.getElementById('assessment-components');
            const newComponent = document.createElement('div');
            newComponent.className = 'assessment-component grid grid-cols-1 md:grid-cols-4 gap-4 mb-4';
            newComponent.innerHTML = `
                <div>
                    <label class="block text-xs font-medium text-gray-600">Component Name *</label>
                    <input type="text" name="assessment_components[${componentIndex}][component_name]" required
                        placeholder="e.g., Assignment ${componentIndex + 1}"
                        class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Weighting (%) *</label>
                    <input type="number" name="assessment_components[${componentIndex}][weighting]" min="1" max="100" required
                        placeholder="40"
                        class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Pass Mark (%)</label>
                    <input type="number" name="assessment_components[${componentIndex}][component_pass_mark]" min="1" max="100"
                        placeholder="40" value="40"
                        class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex items-center">
                    <label class="flex items-center text-sm">
                        <input type="hidden" name="assessment_components[${componentIndex}][is_must_pass]" value="0">
                        <input type="checkbox" name="assessment_components[${componentIndex}][is_must_pass]" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm">
                        <span class="ml-2">Must Pass</span>
                    </label>
                </div>
            `;
            container.appendChild(newComponent);
            componentIndex++;
        });

        document.getElementById('remove-component').addEventListener('click', function() {
            const container = document.getElementById('assessment-components');
            const components = container.querySelectorAll('.assessment-component');
            if (components.length > 1) {
                container.removeChild(components[components.length - 1]);
                componentIndex--;
            }
        });
    </script>
</x-app-layout>