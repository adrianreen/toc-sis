{{-- resources/views/module-instances/copy.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Copy Module Instance
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Create a new instance based on: {{ $moduleInstance->module->module_code }} - {{ $moduleInstance->module->title }}
                </p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('module-instances.show', $moduleInstance) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Instance
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Template Information -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Copy Template Information</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p><strong>Module:</strong> {{ $moduleInstance->module->module_code }} - {{ $moduleInstance->module->title }}</p>
                            <p><strong>Current Tutor:</strong> {{ $moduleInstance->tutor?->name ?? 'Not Assigned' }}</p>
                            <p><strong>Current Delivery:</strong> {{ ucfirst($moduleInstance->delivery_style) }}</p>
                            <p><strong>Programme Links:</strong> {{ $moduleInstance->programmeInstances->count() }} programme instance(s)</p>
                            <p><strong>Current Enrolments:</strong> {{ $moduleInstance->enrolments->count() }} student(s)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('module-instances.store-copy', $moduleInstance) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">New Start Date *</label>
                                <input type="date" name="start_date" id="start_date" 
                                    value="{{ old('start_date', now()->addMonth()->format('Y-m-d')) }}" required
                                    min="{{ now()->addDay()->format('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Must be in the future</p>
                            </div>

                            <!-- Target End Date -->
                            <div>
                                <label for="target_end_date" class="block text-sm font-medium text-gray-700">Target End Date</label>
                                <input type="date" name="target_end_date" id="target_end_date" 
                                    value="{{ old('target_end_date') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('target_end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Optional - estimated completion date</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Tutor -->
                            <div>
                                <label for="tutor_id" class="block text-sm font-medium text-gray-700">Tutor</label>
                                <select name="tutor_id" id="tutor_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Not Assigned</option>
                                    @php
                                        $tutors = \App\Models\User::where('role', 'teacher')->orderBy('name')->get();
                                    @endphp
                                    @foreach($tutors as $tutor)
                                        <option value="{{ $tutor->id }}" 
                                            {{ old('tutor_id', $moduleInstance->tutor_id) == $tutor->id ? 'selected' : '' }}>
                                            {{ $tutor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tutor_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Defaults to current tutor: {{ $moduleInstance->tutor?->name ?? 'None' }}</p>
                            </div>

                            <!-- Delivery Style -->
                            <div>
                                <label for="delivery_style" class="block text-sm font-medium text-gray-700">Delivery Style *</label>
                                <select name="delivery_style" id="delivery_style" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="sync" {{ old('delivery_style', $moduleInstance->delivery_style) === 'sync' ? 'selected' : '' }}>
                                        Synchronous (Fixed schedule, cohort-based)
                                    </option>
                                    <option value="async" {{ old('delivery_style', $moduleInstance->delivery_style) === 'async' ? 'selected' : '' }}>
                                        Asynchronous (Self-paced, flexible)
                                    </option>
                                </select>
                                @error('delivery_style')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Copy Options -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Copy Options</h4>
                            
                            <div class="space-y-3">
                                <!-- Programme Instance Links -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" checked disabled
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm opacity-50">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700">Programme Instance Links</label>
                                        <p class="text-gray-500">Automatically copied ({{ $moduleInstance->programmeInstances->count() }} links)</p>
                                    </div>
                                </div>

                                <!-- Assessment Strategy -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" checked disabled
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm opacity-50">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-gray-700">Assessment Strategy</label>
                                        <p class="text-gray-500">Automatically copied from module blueprint</p>
                                    </div>
                                </div>

                                <!-- Student Enrolments -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="copy_enrolments" value="1" id="copy_enrolments"
                                            {{ old('copy_enrolments') ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="copy_enrolments" class="font-medium text-gray-700">Copy Student Enrolments</label>
                                        <p class="text-gray-500">Copy {{ $moduleInstance->enrolments->count() }} current student enrolments to new instance</p>
                                        @if($moduleInstance->enrolments->count() > 0)
                                            <p class="text-yellow-600 text-xs mt-1">⚠️ Usually not recommended - students will be enrolled in both instances</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assessment Strategy Preview -->
                        @if($moduleInstance->module->assessment_strategy)
                        <div class="mt-6 p-4 bg-green-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Assessment Strategy (Will Be Copied)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($moduleInstance->module->assessment_strategy as $component)
                                    <div class="bg-white p-3 rounded border">
                                        <p class="font-medium text-sm">{{ $component['component_name'] }}</p>
                                        <p class="text-xs text-gray-600">{{ $component['weighting'] }}% weight</p>
                                        @if($component['is_must_pass'])
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                                Must Pass
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="{{ route('module-instances.show', $moduleInstance) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Copy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate end date based on start date and cadence
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            const cadence = '{{ $moduleInstance->module->async_instance_cadence }}';
            
            if (startDate && cadence) {
                let endDate = new Date(startDate);
                
                switch (cadence) {
                    case 'monthly':
                        endDate.setMonth(endDate.getMonth() + 1);
                        break;
                    case 'quarterly':
                        endDate.setMonth(endDate.getMonth() + 3);
                        break;
                    case 'bi_annually':
                        endDate.setMonth(endDate.getMonth() + 6);
                        break;
                    case 'annually':
                        endDate.setFullYear(endDate.getFullYear() + 1);
                        break;
                }
                
                endDate.setDate(endDate.getDate() - 1); // End one day before next period
                document.getElementById('target_end_date').value = endDate.toISOString().split('T')[0];
            }
        });
    </script>
</x-app-layout>