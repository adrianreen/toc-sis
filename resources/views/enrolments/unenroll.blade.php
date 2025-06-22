{{-- Admin: Unenroll Student Confirmation --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Unenroll Student: {{ $enrolment->student->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Warning Banner --}}
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Warning: Permanent Action
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>This action will permanently remove the student's enrolment and cannot be undone. Use this only to correct admin errors.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Unenroll Confirmation</h3>
                    
                    {{-- Enrolment Details --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Enrolment Details</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Student</label>
                                <p class="mt-1 text-gray-900">{{ $enrolment->student->full_name }}</p>
                                <p class="text-sm text-gray-600">{{ $enrolment->student->student_number }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Enrolment Type</label>
                                <p class="mt-1 text-gray-900">{{ ucfirst($enrolment->enrolment_type) }}</p>
                            </div>
                            
                            @if($enrolment->isProgrammeEnrolment())
                                <div class="md:col-span-2">
                                    <label class="text-sm font-medium text-gray-500">Programme</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $enrolment->programmeInstance->programme->title }}</p>
                                    <p class="text-sm text-gray-600">Instance: {{ $enrolment->programmeInstance->label }}</p>
                                    <p class="text-sm text-gray-600">Started: {{ $enrolment->programmeInstance->intake_start_date->format('M d, Y') }}</p>
                                </div>
                            @else
                                <div class="md:col-span-2">
                                    <label class="text-sm font-medium text-gray-500">Standalone Module</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $enrolment->moduleInstance->module->title }}</p>
                                    <p class="text-sm text-gray-600">Code: {{ $enrolment->moduleInstance->module->module_code }}</p>
                                    <p class="text-sm text-gray-600">Started: {{ $enrolment->moduleInstance->start_date->format('M d, Y') }}</p>
                                </div>
                            @endif
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Enrolment Date</label>
                                <p class="mt-1 text-gray-900">{{ $enrolment->enrolment_date->format('M d, Y') }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($enrolment->status === 'active') bg-green-100 text-green-800
                                    @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($enrolment->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Unenroll Form --}}
                    <form method="POST" action="{{ route('enrolments.unenroll', $enrolment) }}" id="unenrollForm">
                        @csrf
                        @method('DELETE')
                        
                        {{-- Reason Field --}}
                        <div class="mb-6">
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Unenrollment *</label>
                            <textarea name="reason" 
                                      id="reason" 
                                      rows="3"
                                      required
                                      placeholder="Please provide a detailed reason for this unenrollment (e.g., Admin error - student enrolled in wrong programme)"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Confirmation Checkbox --}}
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="confirm_unenroll" 
                                           name="confirm_unenroll" 
                                           type="checkbox" 
                                           value="1"
                                           required
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="confirm_unenroll" class="font-medium text-gray-700">
                                        I understand this action is permanent and cannot be undone
                                    </label>
                                    <p class="text-gray-500">
                                        This will remove the student's enrolment record completely. Any associated grade records and progress will remain but may become orphaned.
                                    </p>
                                </div>
                            </div>
                            @error('confirm_unenroll')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('students.show', $enrolment->student) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                    onclick="return confirm('Are you absolutely sure you want to unenroll this student? This action cannot be undone!')">
                                Unenroll Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>