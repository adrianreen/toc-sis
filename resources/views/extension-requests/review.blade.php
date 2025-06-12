{{-- resources/views/extension-requests/review.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Review Extension Request
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('extension-requests.show', $extensionRequest) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    View Details
                </a>
                <a href="{{ route('extension-requests.staff-index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to All Requests
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

            <!-- Student & Request Summary -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Request Summary</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Student Information</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="font-medium">Name:</span> {{ $extensionRequest->student->first_name }} {{ $extensionRequest->student->last_name }}</div>
                                <div><span class="font-medium">Student Number:</span> {{ $extensionRequest->student_number }}</div>
                                <div><span class="font-medium">Email:</span> {{ $extensionRequest->student->email }}</div>
                                <div><span class="font-medium">Contact:</span> {{ $extensionRequest->contact_number }}</div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Extension Details</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="font-medium">Course:</span> {{ $extensionRequest->course_name }}</div>
                                <div><span class="font-medium">Type:</span> {{ $extensionRequest->getExtensionTypeLabel() }}</div>
                                <div><span class="font-medium">Duration:</span> {{ $extensionRequest->getExtensionDuration() }}</div>
                                <div><span class="font-medium">Fee:</span> 
                                    @if($extensionRequest->extension_fee > 0)
                                        €{{ number_format($extensionRequest->extension_fee, 2) }}
                                    @else
                                        No fee
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Timeline -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Course Timeline</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-sm font-medium text-blue-900">Course Started</div>
                            <div class="text-lg font-semibold text-blue-800">{{ $extensionRequest->course_commencement_date->format('M j, Y') }}</div>
                        </div>
                        <div class="text-center p-4 bg-red-50 rounded-lg">
                            <div class="text-sm font-medium text-red-900">Original Deadline</div>
                            <div class="text-lg font-semibold text-red-800">{{ $extensionRequest->original_completion_date->format('M j, Y') }}</div>
                            <div class="text-xs text-red-600 mt-1">
                                @if($extensionRequest->original_completion_date->isPast())
                                    {{ $extensionRequest->original_completion_date->diffForHumans() }}
                                @else
                                    {{ $extensionRequest->original_completion_date->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                        @if($extensionRequest->requested_completion_date)
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-sm font-medium text-green-900">Proposed New Deadline</div>
                                <div class="text-lg font-semibold text-green-800">{{ $extensionRequest->requested_completion_date->format('M j, Y') }}</div>
                                <div class="text-xs text-green-600 mt-1">
                                    +{{ $extensionRequest->original_completion_date->diffInDays($extensionRequest->requested_completion_date) }} days
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Student's Additional Information</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $extensionRequest->additional_information }}</p>
                    </div>
                    <div class="mt-4 text-sm text-gray-600">
                        <strong>Assignments submitted to date:</strong> {{ $extensionRequest->assignments_submitted }}
                    </div>
                </div>
            </div>

            <!-- Medical Certificate (if applicable) -->
            @if($extensionRequest->medical_certificate_path)
                <div class="bg-white shadow-sm rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Medical Certificate</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-8 w-8 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-blue-900">Medical Certificate Uploaded</div>
                                    <div class="text-xs text-blue-700">Click to download and review the medical documentation</div>
                                </div>
                            </div>
                            <a href="{{ route('extension-requests.staff-medical-certificate', $extensionRequest) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Certificate
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Policy Check -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                <h4 class="text-lg font-medium text-yellow-900 mb-3">Review Checklist</h4>
                <div class="space-y-2 text-sm text-yellow-800">
                    <div class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 text-yellow-600 rounded border-yellow-300 mr-2" disabled>
                        <span>Request submitted within 5 days of original deadline: 
                            @if($extensionRequest->isWithinValidRequestWindow())
                                <span class="text-green-600 font-medium">✓ Yes</span>
                            @else
                                <span class="text-red-600 font-medium">✗ No ({{ $extensionRequest->created_at->diffInDays($extensionRequest->original_completion_date) }} days after deadline)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 text-yellow-600 rounded border-yellow-300 mr-2" disabled>
                        <span>Medical certificate provided (if required): 
                            @if($extensionRequest->hasValidMedicalCertificate())
                                <span class="text-green-600 font-medium">✓ Yes</span>
                            @else
                                <span class="text-red-600 font-medium">✗ Missing medical certificate</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 text-yellow-600 rounded border-yellow-300 mr-2" disabled>
                        <span>Student has active enrollment in requested course</span>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 text-yellow-600 rounded border-yellow-300 mr-2" disabled>
                        <span>Extension type appropriate for course level (minor/major awards)</span>
                    </div>
                </div>
            </div>

            <!-- Review Form -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Review Decision</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Please review the extension request and make your decision below.
                    </p>
                </div>

                <form method="POST" action="{{ route('extension-requests.update', $extensionRequest) }}" class="px-6 py-4 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Decision -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Decision *</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input id="approve" name="status" type="radio" value="approved" 
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300"
                                       {{ old('status') === 'approved' ? 'checked' : '' }}>
                                <label for="approve" class="ml-3 block text-sm font-medium text-gray-700">
                                    <span class="text-green-700">Approve Extension Request</span>
                                    <p class="text-xs text-gray-500 mt-1">Grant the extension and update the student's completion date</p>
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="reject" name="status" type="radio" value="rejected" 
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300"
                                       {{ old('status') === 'rejected' ? 'checked' : '' }}>
                                <label for="reject" class="ml-3 block text-sm font-medium text-gray-700">
                                    <span class="text-red-700">Reject Extension Request</span>
                                    <p class="text-xs text-gray-500 mt-1">Decline the extension request with explanation</p>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Completion Date (for medical extensions or adjustments) -->
                    <div id="completion_date_section" style="display: none;">
                        <label for="requested_completion_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Custom Completion Date (Optional)
                        </label>
                        <input type="date" id="requested_completion_date" name="requested_completion_date" 
                               value="{{ old('requested_completion_date', $extensionRequest->requested_completion_date?->format('Y-m-d')) }}"
                               min="{{ $extensionRequest->original_completion_date->format('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Leave blank to use the calculated date based on extension type. 
                            Required for medical extensions where the period varies.
                        </p>
                    </div>

                    <!-- Review Notes -->
                    <div>
                        <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Review Notes
                        </label>
                        <textarea id="review_notes" name="review_notes" rows="4" maxlength="1000"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Add any notes about your decision (visible to the student)...">{{ old('review_notes') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Optional notes that will be shared with the student. Explain your reasoning, especially for rejections.
                        </p>
                    </div>

                    <!-- Fee Information -->
                    @if($extensionRequest->extension_fee > 0)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">Fee Information</h4>
                            <p class="text-sm text-blue-800">
                                This extension request includes a fee of <strong>€{{ number_format($extensionRequest->extension_fee, 2) }}</strong>. 
                                If approved, the student will need to pay this fee before the extension becomes active.
                            </p>
                        </div>
                    @endif

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('extension-requests.show', $extensionRequest) }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for form interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const approveRadio = document.getElementById('approve');
            const rejectRadio = document.getElementById('reject');
            const completionDateSection = document.getElementById('completion_date_section');
            const extensionType = '{{ $extensionRequest->extension_type }}';

            function toggleCompletionDate() {
                // Show custom date field for medical extensions when approved, or always for other types when approved
                if (approveRadio.checked && (extensionType === 'medical' || true)) {
                    completionDateSection.style.display = 'block';
                } else {
                    completionDateSection.style.display = 'none';
                }
            }

            approveRadio.addEventListener('change', toggleCompletionDate);
            rejectRadio.addEventListener('change', toggleCompletionDate);

            // Initialize on page load
            toggleCompletionDate();
        });
    </script>
</x-app-layout>