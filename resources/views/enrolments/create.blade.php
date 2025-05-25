{{-- resources/views/enrolments/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Enrol Student: {{ $student->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($existingEnrolments->count() > 0)
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <p class="text-blue-700">
                        <strong>Current Enrolments:</strong>
                        @foreach($existingEnrolments as $enrolment)
                            {{ $enrolment->programme->code }} ({{ ucfirst($enrolment->status) }}){{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('enrolments.store', $student) }}">
                        @csrf

                        <!-- Programme -->
                        <div class="mb-6">
                            <label for="programme_id" class="block text-sm font-medium text-gray-700">Programme *</label>
                            <select name="programme_id" id="programme_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a programme</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}" 
                                        data-type="{{ $programme->enrolment_type }}"
                                        {{ old('programme_id') == $programme->id ? 'selected' : '' }}>
                                        {{ $programme->code }} - {{ $programme->title }}
                                        ({{ ucfirst(str_replace('_', ' ', $programme->enrolment_type)) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('programme_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Cohort (shown only for cohort-based programmes) -->
                        <div class="mb-6" id="cohort-section" style="display: none;">
                            <label for="cohort_id" class="block text-sm font-medium text-gray-700">Cohort *</label>
                            <select name="cohort_id" id="cohort_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a cohort</option>
                            </select>
                            @error('cohort_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Enrolment Date -->
                        <div class="mb-6">
                            <label for="enrolment_date" class="block text-sm font-medium text-gray-700">Enrolment Date *</label>
                            <input type="date" name="enrolment_date" id="enrolment_date" 
                                value="{{ old('enrolment_date', date('Y-m-d')) }}" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('enrolment_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('students.show', $student) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Enrol Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const programmes = @json($programmes);
        const programmeSelect = document.getElementById('programme_id');
        const cohortSection = document.getElementById('cohort-section');
        const cohortSelect = document.getElementById('cohort_id');

        programmeSelect.addEventListener('change', function() {
            const selectedId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const enrolmentType = selectedOption.dataset.type;

            if (enrolmentType === 'cohort' && selectedId) {
                cohortSection.style.display = 'block';
                
                // Find the programme and populate cohorts
                const programme = programmes.find(p => p.id == selectedId);
                cohortSelect.innerHTML = '<option value="">Select a cohort</option>';
                
                if (programme && programme.cohorts) {
                    programme.cohorts.forEach(cohort => {
                        if (cohort.status !== 'completed') {
                            const option = document.createElement('option');
                            option.value = cohort.id;
                            option.textContent = `${cohort.code} - ${cohort.name} (${cohort.status})`;
                            cohortSelect.appendChild(option);
                        }
                    });
                }
            } else {
                cohortSection.style.display = 'none';
                cohortSelect.value = '';
            }
        });

        // Trigger change event on page load if programme is pre-selected
        if (programmeSelect.value) {
            programmeSelect.dispatchEvent(new Event('change'));
        }
    </script>
</x-app-layout>