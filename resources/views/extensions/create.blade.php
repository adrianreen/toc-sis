{{-- resources/views/extensions/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Extension Request
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold">Student Information</h3>
                        <p class="text-gray-600">
                            <strong>Name:</strong> {{ $student->full_name }}<br>
                            <strong>Student Number:</strong> {{ $student->student_number }}<br>
                            <strong>Email:</strong> {{ $student->email }}
                        </p>
                    </div>

                    @if($gradeRecords->isEmpty())
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <p class="text-yellow-800">
                                No assessment components available for extension requests. 
                                The student must have active enrolments with ungraded assessments.
                            </p>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('extensions.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Back to Extensions
                            </a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('extensions.store', $student) }}">
                            @csrf

                            <!-- Assessment Component Selection -->
                            <div class="mb-6">
                                <label for="student_grade_record_id" class="block text-sm font-medium text-gray-700">
                                    Assessment Component *
                                </label>
                                <select name="student_grade_record_id" id="student_grade_record_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select an assessment component</option>
                                    @foreach($gradeRecords as $record)
                                        <option value="{{ $record->id }}" {{ old('student_grade_record_id') == $record->id ? 'selected' : '' }}>
                                            {{ $record->moduleInstance->module->title }} - {{ $record->assessment_component_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_grade_record_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- New Due Date -->
                            <div class="mb-6">
                                <label for="new_due_date" class="block text-sm font-medium text-gray-700">
                                    Requested New Due Date *
                                </label>
                                <input type="date" name="new_due_date" id="new_due_date" 
                                       value="{{ old('new_due_date') }}" required
                                       min="{{ now()->addDay()->format('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('new_due_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Reason -->
                            <div class="mb-6">
                                <label for="reason" class="block text-sm font-medium text-gray-700">
                                    Reason for Extension *
                                </label>
                                <textarea name="reason" id="reason" rows="4" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    placeholder="Please provide a detailed reason for the extension request...">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('extensions.index') }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Submit Extension Request
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>