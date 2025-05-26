{{-- resources/views/module-instances/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Module Instance
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('module-instances.store') }}">
                        @csrf

                        <!-- Module -->
                        <div class="mb-6">
                            <label for="module_id" class="block text-sm font-medium text-gray-700">Module *</label>
                            <select name="module_id" id="module_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a module</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                        {{ $module->code }} - {{ $module->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('module_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cohort -->
                        <div class="mb-6">
                            <label for="cohort_id" class="block text-sm font-medium text-gray-700">Cohort *</label>
                            <select name="cohort_id" id="cohort_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a cohort</option>
                                @foreach($cohorts as $cohort)
                                    <option value="{{ $cohort->id }}" 
                                        data-programme="{{ $cohort->programme->code }}"
                                        data-start="{{ $cohort->start_date->format('Y-m-d') }}"
                                        data-end="{{ $cohort->end_date?->format('Y-m-d') }}"
                                        {{ old('cohort_id') == $cohort->id ? 'selected' : '' }}>
                                        {{ $cohort->programme->code }} - {{ $cohort->code }} - {{ $cohort->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cohort_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date *</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Teacher -->
                            <div>
                                <label for="teacher_id" class="block text-sm font-medium text-gray-700">Teacher</label>
                                <select name="teacher_id" id="teacher_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Not Assigned</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="planned" {{ old('status') === 'planned' ? 'selected' : '' }}>Planned</option>
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Instance Code Preview -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600">Instance Code will be generated as:</p>
                            <p class="text-lg font-medium text-gray-900" id="instance-code-preview">-</p>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('module-instances.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Module Instance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-generate instance code preview
        function updateInstanceCodePreview() {
            const moduleSelect = document.getElementById('module_id');
            const cohortSelect = document.getElementById('cohort_id');
            const preview = document.getElementById('instance-code-preview');
            
            if (moduleSelect.selectedIndex > 0 && cohortSelect.selectedIndex > 0) {
                const moduleCode = moduleSelect.options[moduleSelect.selectedIndex].text.split(' - ')[0];
                const cohortCode = cohortSelect.options[cohortSelect.selectedIndex].text.split(' - ')[1];
                preview.textContent = moduleCode + '-' + cohortCode;
            } else {
                preview.textContent = '-';
            }
        }

        // Auto-populate dates based on cohort selection
        document.getElementById('cohort_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const startDate = selected.getAttribute('data-start');
                const endDate = selected.getAttribute('data-end');
                
                if (startDate) {
                    document.getElementById('start_date').value = startDate;
                }
                if (endDate) {
                    document.getElementById('end_date').value = endDate;
                }
            }
            updateInstanceCodePreview();
        });

        document.getElementById('module_id').addEventListener('change', updateInstanceCodePreview);

        // Initial preview update
        updateInstanceCodePreview();
    </script>
</x-app-layout>