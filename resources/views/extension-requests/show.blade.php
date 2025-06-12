{{-- resources/views/extension-requests/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Extension Request Details
            </h2>
            <div class="flex space-x-3">
                @if(Auth::user()->isStudent())
                    <a href="{{ route('extension-requests.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to My Extensions
                    </a>
                @else
                    <a href="{{ route('extension-requests.staff-index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Back to All Requests
                    </a>
                    @if($extensionRequest->isPending())
                        <a href="{{ route('extension-requests.review', $extensionRequest) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Review Request
                        </a>
                    @endif
                @endif
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

            <!-- Status Card -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Request Status</h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $extensionRequest->getStatusBadgeClass() }}">
                            {{ ucfirst($extensionRequest->status) }}
                        </span>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Submitted</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->created_at->format('M j, Y \a\t g:i A') }}</dd>
                        </div>
                        @if($extensionRequest->reviewed_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Reviewed</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->reviewed_at->format('M j, Y \a\t g:i A') }}</dd>
                            </div>
                        @endif
                        @if($extensionRequest->reviewer)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Reviewed by</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->reviewer->name }}</dd>
                            </div>
                        @endif
                    </div>
                    @if($extensionRequest->isApproved() && $extensionRequest->requested_completion_date)
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                            <p class="text-sm text-green-800">
                                <strong>New Completion Date:</strong> {{ $extensionRequest->requested_completion_date->format('l, F j, Y') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Student Information -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Student Information</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Student Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->student->first_name }} {{ $extensionRequest->student->last_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Student Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->student_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->student->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->contact_number }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extension Details -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Extension Details</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Course</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->course_name }}</dd>
                            @if($extensionRequest->enrolment && $extensionRequest->enrolment->programme)
                                <dd class="text-xs text-gray-500">Programme: {{ $extensionRequest->enrolment->programme->name }}</dd>
                            @endif
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Extension Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->getExtensionTypeLabel() }}</dd>
                            <dd class="text-xs text-gray-500">Duration: {{ $extensionRequest->getExtensionDuration() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Extension Fee</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($extensionRequest->extension_fee > 0)
                                    €{{ number_format($extensionRequest->extension_fee, 2) }}
                                    @if($extensionRequest->fee_paid)
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Paid</span>
                                    @else
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Payment Required</span>
                                    @endif
                                @else
                                    No fee required
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Assignments Submitted</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->assignments_submitted }}</dd>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Course Commencement Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->course_commencement_date->format('F j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Original Completion Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->original_completion_date->format('F j, Y') }}</dd>
                        </div>
                        @if($extensionRequest->requested_completion_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Requested New Completion Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $extensionRequest->requested_completion_date->format('F j, Y') }}</dd>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white shadow-sm rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Additional Information</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $extensionRequest->additional_information }}</p>
                </div>
            </div>

            <!-- Medical Certificate -->
            @if($extensionRequest->medical_certificate_path)
                <div class="bg-white shadow-sm rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Medical Certificate</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-sm text-gray-900 mr-4">Medical certificate uploaded</span>
                            <a href="{{ route('extension-requests.medical-certificate', $extensionRequest) }}" 
                               class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                Download Certificate
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Review Notes (for reviewed requests) -->
            @if($extensionRequest->review_notes)
                <div class="bg-white shadow-sm rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Review Notes</h3>
                    </div>
                    <div class="px-6 py-4">
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-md">
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $extensionRequest->review_notes }}</p>
                            @if($extensionRequest->reviewer)
                                <p class="text-xs text-gray-500 mt-2">
                                    - {{ $extensionRequest->reviewer->name }}, {{ $extensionRequest->reviewed_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Request History Timeline -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Request Timeline</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li>
                                <div class="relative pb-8">
                                    <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-900">Request submitted</p>
                                                <p class="text-sm text-gray-500">{{ $extensionRequest->created_at->format('M j, Y \a\t g:i A') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @if($extensionRequest->reviewed_at)
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full {{ $extensionRequest->isApproved() ? 'bg-green-500' : 'bg-red-500' }} flex items-center justify-center ring-8 ring-white">
                                                    @if($extensionRequest->isApproved())
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    @else
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-900">Request {{ $extensionRequest->status }}</p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $extensionRequest->reviewed_at->format('M j, Y \a\t g:i A') }}
                                                        @if($extensionRequest->reviewer)
                                                            by {{ $extensionRequest->reviewer->name }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>