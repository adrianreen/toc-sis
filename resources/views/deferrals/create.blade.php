{{-- resources/views/deferrals/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Deferral Request
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold">Student Information</h3>
                        <p class="text-gray-600">
                            <strong>Name:</strong> {{ $student->full_name }}<br>
                            <strong>Student Number:</strong> {{ $student->student_number }}<br>
                            <strong>Programme:</strong> {{ $enrolment->programme->code }} - {{ $enrolment->programme->title }}<br>
                            <strong>Current Intake:</strong> {{ $enrolment->programmeInstance->label }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('deferrals.store', [$student, $enrolment]) }}">
                        @csrf

                        <!-- Target Programme Instance -->
                        <div class="mb-6">
                            <label for="to_programme_instance_id" class="block text-sm font-medium text-gray-700">
                                Select Return Programme Instance *
                            </label>
                            <select name="to_programme_instance_id" id="to_programme_instance_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a programme instance</option>
                                @foreach($futureProgrammeInstances as $instance)
                                    <option value="{{ $instance->id }}" {{ old('to_programme_instance_id') == $instance->id ? 'selected' : '' }}>
                                        {{ $instance->label }} 
                                        (Starts: {{ $instance->start_date->format('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('to_programme_instance_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if($futureProgrammeInstances->isEmpty())
                                <p class="mt-1 text-sm text-yellow-600">
                                    No future programme instances available. Please contact administration.
                                </p>
                            @endif
                        </div>

                        <!-- Expected Return Date -->
                        <div class="mb-6">
                            <label for="expected_return_date" class="block text-sm font-medium text-gray-700">
                                Expected Return Date
                            </label>
                            <input type="date" name="expected_return_date" id="expected_return_date" 
                                value="{{ old('expected_return_date') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('expected_return_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reason -->
                        <div class="mb-6">
                            <label for="reason" class="block text-sm font-medium text-gray-700">
                                Reason for Deferral *
                            </label>
                            <textarea name="reason" id="reason" rows="4" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('students.show', $student) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Submit Deferral Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>