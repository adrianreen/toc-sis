{{-- resources/views/extension-requests/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Request Course Extension
            </h2>
            <a href="{{ route('extension-requests.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to My Extensions
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Policy Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-medium text-blue-900 mb-4">Extension Policy & Information</h3>
                <div class="text-sm text-blue-800 space-y-2">
                    <p><strong>Please complete this form if you wish to request an extension to your course.</strong></p>
                    <p>We will endeavor to facilitate all requests, however, extensions are provided at the sole discretion of the College. This is because it may not be possible to offer an extension; for example, where a course is being retired, or where course content or assessments have changed.</p>
                    <p><strong>Important:</strong> Extension requests should be submitted to the college in writing within 5 days of the original completion deadline. Any requests received that do not fall in line with the criteria outlined here may be rejected and could result in a learner having to reregister with the college.</p>
                </div>
            </div>

            <!-- Extension Options -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                <h4 class="text-lg font-medium text-yellow-900 mb-4">Extension Options & Fees</h4>
                <div class="space-y-3 text-sm text-yellow-800">
                    <div class="flex items-start">
                        <span class="font-semibold mr-2">Option (1):</span>
                        <span>Two weeks (minor & major awards) and must be justified. Can only be applied for once. - <strong>No Additional Fees.</strong></span>
                    </div>
                    <div class="flex items-start">
                        <span class="font-semibold mr-2">Option (2):</span>
                        <span>8 Weeks (minor awards only) - <strong>€85.00 fee.</strong></span>
                    </div>
                    <div class="flex items-start">
                        <span class="font-semibold mr-2">Option (3):</span>
                        <span>24 Weeks (major awards & bundle courses only) - <strong>€165.00 fee.</strong></span>
                    </div>
                    <div class="flex items-start">
                        <span class="font-semibold mr-2">Option (4):</span>
                        <span>Medical (no additional fee) timelines will be extended in line with the period of illness which must be noted clearly on a medical report.</span>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Extension Request Form -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Extension Request Form</h3>
                    <p class="mt-1 text-sm text-gray-600">Please complete all fields below.</p>
                </div>

                <form method="POST" action="{{ route('extension-requests.store') }}" enctype="multipart/form-data" class="px-6 py-4 space-y-6">
                    @csrf

                    <!-- Personal Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                            <input type="text" value="{{ $student->first_name }} {{ $student->last_name }}" readonly
                                   class="w-full px-3 py-2 border border-gray-300 bg-gray-50 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Open College Student Number</label>
                            <input type="text" value="{{ $student->student_number }}" readonly
                                   class="w-full px-3 py-2 border border-gray-300 bg-gray-50 rounded-md">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" value="{{ $student->email }}" readonly
                                   class="w-full px-3 py-2 border border-gray-300 bg-gray-50 rounded-md">
                        </div>
                        <div>
                            <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                            <input type="tel" id="contact_number" name="contact_number" value="{{ old('contact_number', $student->phone) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Course Selection -->
                    <div>
                        <label for="enrolment_id" class="block text-sm font-medium text-gray-700 mb-2">Which Course did you enrol in? *</label>
                        <select id="enrolment_id" name="enrolment_id" required onchange="updateCourseDetails()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a course...</option>
                            @foreach($enrolments as $enrolment)
                                <option value="{{ $enrolment->id }}" 
                                        data-course-name="{{ $enrolment->programme->name }}"
                                        data-start-date="{{ $enrolment->start_date ? $enrolment->start_date->format('Y-m-d') : '' }}"
                                        data-completion-date="{{ $enrolment->completion_date ? $enrolment->completion_date->format('Y-m-d') : '' }}"
                                        {{ old('enrolment_id') == $enrolment->id ? 'selected' : '' }}>
                                    {{ $enrolment->programme->name }} 
                                    @if($enrolment->cohort)
                                        ({{ $enrolment->cohort->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Extension Type -->
                    <div>
                        <label for="extension_type" class="block text-sm font-medium text-gray-700 mb-2">Please select one extension option from the below list *</label>
                        <select id="extension_type" name="extension_type" required onchange="toggleMedicalUpload()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select extension option...</option>
                            <option value="two_weeks_free" {{ old('extension_type') === 'two_weeks_free' ? 'selected' : '' }}>
                                Option (1): Two weeks (minor & major awards) - No Additional Fees
                            </option>
                            <option value="eight_weeks_minor" {{ old('extension_type') === 'eight_weeks_minor' ? 'selected' : '' }}>
                                Option (2): 8 Weeks (minor awards only) - €85.00 fee
                            </option>
                            <option value="twenty_four_weeks_major" {{ old('extension_type') === 'twenty_four_weeks_major' ? 'selected' : '' }}>
                                Option (3): 24 Weeks (major awards & bundle courses only) - €165.00 fee
                            </option>
                            <option value="medical" {{ old('extension_type') === 'medical' ? 'selected' : '' }}>
                                Option (4): Medical (no additional fee)
                            </option>
                        </select>
                    </div>

                    <!-- Course & Background Details -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Course & Background Details</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">Course Name *</label>
                                <input type="text" id="course_name" name="course_name" value="{{ old('course_name') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Course name will auto-fill when you select above">
                            </div>
                            <div>
                                <label for="assignments_submitted" class="block text-sm font-medium text-gray-700 mb-2">How many assignments have you submitted to date? *</label>
                                <input type="number" id="assignments_submitted" name="assignments_submitted" value="{{ old('assignments_submitted', 0) }}" min="0" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label for="course_commencement_date" class="block text-sm font-medium text-gray-700 mb-2">What date did your course commence? *</label>
                                <input type="date" id="course_commencement_date" name="course_commencement_date" value="{{ old('course_commencement_date') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="original_completion_date" class="block text-sm font-medium text-gray-700 mb-2">What was your completion date? *</label>
                                <input type="date" id="original_completion_date" name="original_completion_date" value="{{ old('original_completion_date') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div>
                        <label for="additional_information" class="block text-sm font-medium text-gray-700 mb-2">Additional Information *</label>
                        <textarea id="additional_information" name="additional_information" rows="4" required maxlength="2000"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Please provide additional details about your extension request...">{{ old('additional_information') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Maximum 2000 characters</p>
                    </div>

                    <!-- Medical Certificate Upload -->
                    <div id="medical_upload_section" style="display: none;">
                        <label for="medical_certificate" class="block text-sm font-medium text-gray-700 mb-2">
                            Please attach a copy of your Doctors Report/Cert if Claiming an Extension on Medical Grounds
                        </label>
                        <input type="file" id="medical_certificate" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Accepted formats: PDF, JPG, PNG. Maximum size: 5MB</p>
                    </div>

                    <!-- Declaration -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-start">
                            <input type="checkbox" id="declaration_accepted" name="declaration_accepted" value="1" required
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="declaration_accepted" class="ml-2 text-sm text-gray-700">
                                <strong>Declaration:</strong> I confirm that all information in this form is true and accurate. *
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('extension-requests.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Submit Extension Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for form interactions -->
    <script>
        function updateCourseDetails() {
            const select = document.getElementById('enrolment_id');
            const option = select.options[select.selectedIndex];
            
            if (option.value) {
                document.getElementById('course_name').value = option.dataset.courseName;
                document.getElementById('course_commencement_date').value = option.dataset.startDate;
                document.getElementById('original_completion_date').value = option.dataset.completionDate;
            } else {
                document.getElementById('course_name').value = '';
                document.getElementById('course_commencement_date').value = '';
                document.getElementById('original_completion_date').value = '';
            }
        }

        function toggleMedicalUpload() {
            const extensionType = document.getElementById('extension_type').value;
            const medicalSection = document.getElementById('medical_upload_section');
            const medicalInput = document.getElementById('medical_certificate');
            
            if (extensionType === 'medical') {
                medicalSection.style.display = 'block';
                medicalInput.required = true;
            } else {
                medicalSection.style.display = 'none';
                medicalInput.required = false;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCourseDetails();
            toggleMedicalUpload();
        });
    </script>
</x-app-layout>