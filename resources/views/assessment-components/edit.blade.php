{{-- resources/views/assessment-components/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Assessment Component: {{ $assessmentComponent->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('assessment-components.update', [$module, $assessmentComponent]) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Component Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $assessmentComponent->name) }}" required
                                    placeholder="e.g., Assignment 1, Final Exam"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Assessment Type *</label>
                                <select name="type" id="type" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a type</option>
                                    <option value="assignment" {{ old('type', $assessmentComponent->type) === 'assignment' ? 'selected' : '' }}>Assignment</option>
                                    <option value="exam" {{ old('type', $assessmentComponent->type) === 'exam' ? 'selected' : '' }}>Exam</option>
                                    <option value="project" {{ old('type', $assessmentComponent->type) === 'project' ? 'selected' : '' }}>Project</option>
                                    <option value="presentation" {{ old('type', $assessmentComponent->type) === 'presentation' ? 'selected' : '' }}>Presentation</option>
                                    <option value="other" {{ old('type', $assessmentComponent->type) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Weight -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700">Weight (%) *</label>
                                <input type="number" name="weight" id="weight" value="{{ old('weight', $assessmentComponent->weight) }}" required
                                    min="0" max="100" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('weight')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @php
                                    $currentWeight = $module->assessmentComponents()->where('id', '!=', $assessmentComponent->id)->sum('weight');
                                @endphp
                                <p class="mt-1 text-xs text-gray-500" id="weight-help">
                                    Current total weight (excluding this component): {{ $currentWeight }}%
                                </p>
                            </div>

                            <!-- Sequence -->
                            <div>
                                <label for="sequence" class="block text-sm font-medium text-gray-700">Sequence *</label>
                                <input type="number" name="sequence" id="sequence" 
                                    value="{{ old('sequence', $assessmentComponent->sequence) }}" 
                                    required min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('sequence')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Order in which this component appears</p>
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $assessmentComponent->is_active) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="is_active" class="ml-2 text-sm text-gray-600">
                                    Component is active
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Inactive components won't be used for new assessments</p>
                        </div>

                        <!-- Student Assessment Warning -->
                        @php
                            $studentAssessmentCount = $assessmentComponent->studentAssessments()->count();
                        @endphp
                        @if($studentAssessmentCount > 0)
                            <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            <strong>Warning:</strong> This component has {{ $studentAssessmentCount }} student assessment(s). 
                                            Changes to weight or type may affect existing grades and calculations.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Current Components Summary -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">All Components for {{ $module->code }}</h4>
                            <div class="space-y-1">
                                @foreach($module->assessmentComponents()->orderBy('sequence')->get() as $component)
                                    <div class="flex justify-between text-sm {{ $component->id === $assessmentComponent->id ? 'font-semibold text-indigo-600' : '' }}">
                                        <span>
                                            {{ $component->sequence }}. {{ $component->name }} ({{ ucfirst($component->type) }})
                                            {{ $component->id === $assessmentComponent->id ? ' ← Currently editing' : '' }}
                                        </span>
                                        <span class="font-medium">{{ $component->weight }}%</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="border-t mt-2 pt-2">
                                <div class="flex justify-between text-sm font-medium">
                                    <span>Total Weight:</span>
                                    @php
                                        $totalWeight = $module->assessmentComponents()->sum('weight');
                                    @endphp
                                    <span class="{{ $totalWeight > 100 ? 'text-red-600' : ($totalWeight == 100 ? 'text-green-600' : 'text-yellow-600') }}" id="total-weight">
                                        {{ $totalWeight }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Metadata -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Component Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Created:</span>
                                    <span class="font-medium ml-1">{{ $assessmentComponent->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Last Updated:</span>
                                    <span class="font-medium ml-1">{{ $assessmentComponent->updated_at->format('d M Y H:i') }}</span>
                                </div>
                                @if($studentAssessmentCount > 0)
                                    <div>
                                        <span class="text-gray-600">Student Assessments:</span>
                                        <span class="font-medium ml-1">{{ $studentAssessmentCount }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('assessment-components.index', $module) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Component
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time weight calculation
        document.getElementById('weight').addEventListener('input', function() {
            const currentWeight = {{ $currentWeight }};
            const newWeight = parseFloat(this.value) || 0;
            const totalWeight = currentWeight + newWeight;
            
            // Update the help text
            const helpText = document.getElementById('weight-help');
            helpText.innerHTML = `Current total weight (excluding this component): ${currentWeight}%. New total would be: <span class="${totalWeight > 100 ? 'text-red-600' : (totalWeight == 100 ? 'text-green-600' : 'text-gray-500')}">${totalWeight}%</span>`;
            
            // Update the total weight display
            const totalWeightDisplay = document.getElementById('total-weight');
            totalWeightDisplay.textContent = totalWeight + '%';
            totalWeightDisplay.className = totalWeight > 100 ? 'text-red-600' : (totalWeight == 100 ? 'text-green-600' : 'text-yellow-600');
            
            // Highlight the input if over 100%
            if (totalWeight > 100) {
                this.classList.add('border-red-300', 'focus:border-red-300', 'focus:ring-red-200');
                this.classList.remove('border-gray-300', 'focus:border-indigo-300', 'focus:ring-indigo-200');
            } else {
                this.classList.remove('border-red-300', 'focus:border-red-300', 'focus:ring-red-200');
                this.classList.add('border-gray-300', 'focus:border-indigo-300', 'focus:ring-indigo-200');
            }
        });
    </script>
</x-app-layout>