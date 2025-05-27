{{-- resources/views/assessment-components/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Assessment Component: {{ $module->code }} - {{ $module->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('assessment-components.store', $module) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Component Name *</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
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
                                    <option value="assignment" {{ old('type') === 'assignment' ? 'selected' : '' }}>Assignment</option>
                                    <option value="exam" {{ old('type') === 'exam' ? 'selected' : '' }}>Exam</option>
                                    <option value="project" {{ old('type') === 'project' ? 'selected' : '' }}>Project</option>
                                    <option value="presentation" {{ old('type') === 'presentation' ? 'selected' : '' }}>Presentation</option>
                                    <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Weight -->
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700">Weight (%) *</label>
                                <input type="number" name="weight" id="weight" value="{{ old('weight') }}" required
                                    min="0" max="100" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('weight')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @php
                                    $currentWeight = $module->assessmentComponents()->sum('weight');
                                @endphp
                                <p class="mt-1 text-xs text-gray-500">
                                    Current total weight: {{ $currentWeight }}%. 
                                    Remaining: {{ 100 - $currentWeight }}%
                                </p>
                            </div>

                            <!-- Sequence -->
                            <div>
                                <label for="sequence" class="block text-sm font-medium text-gray-700">Sequence *</label>
                                <input type="number" name="sequence" id="sequence" 
                                    value="{{ old('sequence', $module->assessmentComponents()->max('sequence') + 1) }}" 
                                    required min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('sequence')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Order in which this component appears</p>
                            </div>
                        </div>

                        <!-- Current Components Summary -->
                        @if($module->assessmentComponents()->count() > 0)
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Existing Components</h4>
                                <div class="space-y-1">
                                    @foreach($module->assessmentComponents()->orderBy('sequence')->get() as $component)
                                        <div class="flex justify-between text-sm">
                                            <span>{{ $component->sequence }}. {{ $component->name }} ({{ ucfirst($component->type) }})</span>
                                            <span class="font-medium">{{ $component->weight }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="border-t mt-2 pt-2">
                                    <div class="flex justify-between text-sm font-medium">
                                        <span>Total Weight:</span>
                                        <span class="{{ $currentWeight > 100 ? 'text-red-600' : ($currentWeight == 100 ? 'text-green-600' : 'text-yellow-600') }}">
                                            {{ $currentWeight }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('assessment-components.index', $module) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Component
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
            const helpText = this.parentNode.querySelector('.text-xs');
            helpText.innerHTML = `Current total weight: ${currentWeight}%. New total would be: <span class="${totalWeight > 100 ? 'text-red-600' : (totalWeight == 100 ? 'text-green-600' : 'text-gray-500')}">${totalWeight}%</span>`;
            
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