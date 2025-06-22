{{-- Step 2b: Module Enrolment --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Enrol in Standalone Module: {{ $student->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Navigation Breadcrumb --}}
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('students.show', $student) }}" class="text-gray-700 hover:text-gray-900">Student Profile</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <a href="{{ route('enrolments.create', $student) }}" class="ml-1 text-gray-700 hover:text-gray-900 md:ml-2">Enrolment Choice</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-gray-500 md:ml-2">Module Enrolment</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            {{-- Existing Module Enrolments Warning --}}
            @if($existingModuleEnrolments->count() > 0)
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-amber-700">
                                <strong>Existing Module Enrolments:</strong><br>
                                @foreach($existingModuleEnrolments as $enrolment)
                                    {{ $enrolment->moduleInstance->module->title }} 
                                    ({{ $enrolment->moduleInstance->module->module_code }}) - 
                                    <span class="font-semibold">{{ ucfirst($enrolment->status) }}</span>
                                    @if(!$loop->last)<br>@endif
                                @endforeach
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Standalone Module Instances</h3>
                    
                    @if($availableModules->count() > 0)
                        <form method="POST" action="{{ route('enrolments.store-module', $student) }}">
                            @csrf
                            
                            {{-- Module Instance Selection --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Select Module Instance *</label>
                                <div class="space-y-3">
                                    @foreach($availableModules as $moduleInstance)
                                        <div class="relative">
                                            <input type="radio" 
                                                   name="module_instance_id" 
                                                   value="{{ $moduleInstance->id }}"
                                                   id="module_{{ $moduleInstance->id }}"
                                                   class="sr-only"
                                                   onchange="updateSelection(this)"
                                                   {{ old('module_instance_id') == $moduleInstance->id ? 'checked' : '' }}>
                                            <label for="module_{{ $moduleInstance->id }}" 
                                                   class="relative flex p-4 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 hover:border-gray-300 transition-all duration-200"
                                                   data-radio-label>
                                                <!-- Selection indicator -->
                                                <div class="absolute top-2 right-2 hidden" data-check-icon>
                                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <div class="block w-full">
                                                    <div class="w-full text-lg font-semibold text-gray-900">
                                                        {{ $moduleInstance->module->title }}
                                                    </div>
                                                    <div class="w-full text-sm text-gray-500 mt-1">
                                                        <strong>Code:</strong> {{ $moduleInstance->module->module_code }} |
                                                        <strong>Start:</strong> {{ $moduleInstance->start_date->format('M d, Y') }} |
                                                        <strong>Delivery:</strong> {{ ucfirst($moduleInstance->delivery_style) }}
                                                        @if($moduleInstance->target_end_date)
                                                            | <strong>Target End:</strong> {{ $moduleInstance->target_end_date->format('M d, Y') }}
                                                        @endif
                                                    </div>
                                                    <div class="w-full text-sm text-gray-600 mt-2">
                                                        <strong>Credits:</strong> {{ $moduleInstance->module->credit_value }} |
                                                        @if($moduleInstance->tutor)
                                                            <strong>Tutor:</strong> {{ $moduleInstance->tutor->name }} |
                                                        @endif
                                                        <strong>Cadence:</strong> {{ ucfirst(str_replace('_', ' ', $moduleInstance->module->async_instance_cadence)) }}
                                                    </div>
                                                    @if($moduleInstance->module->assessment_strategy)
                                                        <div class="w-full text-xs text-gray-500 mt-2">
                                                            <strong>Assessments:</strong> 
                                                            @foreach($moduleInstance->module->assessment_strategy as $component)
                                                                {{ $component['component_name'] }} ({{ $component['weighting'] }}%){{ !$loop->last ? ', ' : '' }}
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('module_instance_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Enrolment Date --}}
                            <div class="mb-6">
                                <label for="enrolment_date" class="block text-sm font-medium text-gray-700">Enrolment Date *</label>
                                <input type="date" 
                                       name="enrolment_date" 
                                       id="enrolment_date" 
                                       value="{{ old('enrolment_date', date('Y-m-d')) }}" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('enrolment_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('enrolments.create', $student) }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Back
                                </a>
                                <button type="submit" 
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Enrol in Module
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Standalone Module Instances Available</h3>
                            <p class="text-gray-500">There are currently no standalone module instances available for enrolment.</p>
                            <div class="mt-4">
                                <a href="{{ route('enrolments.create', $student) }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Back to Enrolment Options
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateSelection(radio) {
            // Remove selection styling from all labels
            document.querySelectorAll('[data-radio-label]').forEach(label => {
                label.classList.remove('border-green-500', 'bg-green-50', 'ring-2', 'ring-green-200');
                label.classList.add('border-gray-200');
            });
            
            // Hide all check icons
            document.querySelectorAll('[data-check-icon]').forEach(icon => {
                icon.classList.add('hidden');
            });
            
            // Add selection styling to the selected label
            if (radio.checked) {
                const label = radio.nextElementSibling;
                const checkIcon = label.querySelector('[data-check-icon]');
                
                label.classList.remove('border-gray-200');
                label.classList.add('border-green-500', 'bg-green-50', 'ring-2', 'ring-green-200');
                checkIcon.classList.remove('hidden');
            }
        }
        
        // Initialize selection on page load
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRadio = document.querySelector('input[name="module_instance_id"]:checked');
            if (checkedRadio) {
                updateSelection(checkedRadio);
            }
        });
    </script>
</x-app-layout>