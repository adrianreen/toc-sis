<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
                <p class="mt-2 text-gray-600">View your personal information and enrolment details</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Personal Information Card -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-soft rounded-xl p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Personal Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Student Number</label>
                                <p class="mt-1 text-lg font-semibold text-toc-600">{{ $student->student_number }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Full Name</label>
                                <p class="mt-1 text-lg text-gray-900">{{ $student->full_name }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Email Address</label>
                                <p class="mt-1 text-gray-900">{{ $student->email }}</p>
                            </div>
                            
                            @if($student->phone)
                            <div>
                                <label class="text-sm font-medium text-gray-500">Phone</label>
                                <p class="mt-1 text-gray-900">{{ $student->phone }}</p>
                            </div>
                            @endif
                            
                            @if($student->date_of_birth)
                            <div>
                                <label class="text-sm font-medium text-gray-500">Date of Birth</label>
                                <p class="mt-1 text-gray-900">{{ $student->date_of_birth->format('d M Y') }}</p>
                            </div>
                            @endif
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500">Status</label>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $student->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $student->status === 'deferred' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $student->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ !in_array($student->status, ['active', 'deferred', 'completed']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if($student->address || $student->city || $student->county)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="text-sm font-medium text-gray-500">Address</label>
                            <div class="mt-1 text-gray-900">
                                @if($student->address)
                                    <p>{{ $student->address }}</p>
                                @endif
                                <p>
                                    @if($student->city){{ $student->city }}@endif
                                    @if($student->city && $student->county), @endif
                                    @if($student->county){{ $student->county }}@endif
                                </p>
                                @if($student->eircode)
                                    <p>{{ $student->eircode }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="space-y-6">
                    <!-- Quick Links -->
                    <div class="bg-white shadow-soft rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
                        <div class="space-y-3">
                            <a href="{{ route('students.progress') }}" 
                               class="flex items-center justify-between p-3 text-sm bg-toc-50 hover:bg-toc-100 rounded-lg transition-colors group">
                                <span class="text-toc-700 font-medium">View My Progress</span>
                                <svg class="w-4 h-4 text-toc-500 group-hover:text-toc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            
                            <a href="{{ route('students.assessments') }}" 
                               class="flex items-center justify-between p-3 text-sm bg-toc-50 hover:bg-toc-100 rounded-lg transition-colors group">
                                <span class="text-toc-700 font-medium">My Assessments</span>
                                <svg class="w-4 h-4 text-toc-500 group-hover:text-toc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            
                            <a href="{{ route('students.enrolments') }}" 
                               class="flex items-center justify-between p-3 text-sm bg-toc-50 hover:bg-toc-100 rounded-lg transition-colors group">
                                <span class="text-toc-700 font-medium">My Enrolments</span>
                                <svg class="w-4 h-4 text-toc-500 group-hover:text-toc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Support Information -->
                    <div class="bg-blue-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3">Need Help?</h3>
                        <p class="text-sm text-blue-700 mb-4">If you notice any errors in your information or need to update your details, please contact Student Services.</p>
                        <div class="text-sm text-blue-600">
                            <p class="font-medium">Student Services</p>
                            <p>Email: studentservices@theopencollege.com</p>
                            <p>Phone: (01) 495 2028</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Enrolments -->
            @if($student->enrolments->count() > 0)
            <div class="mt-8">
                <div class="bg-white shadow-soft rounded-xl p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Current Enrolments</h2>
                    
                    <div class="space-y-4">
                        @foreach($student->enrolments as $enrolment)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $enrolment->programme->title }}</h3>
                                    <p class="text-sm text-gray-600">Programme Code: {{ $enrolment->programme->code }}</p>
                                    @if($enrolment->cohort)
                                        <p class="text-sm text-gray-600">Cohort: {{ $enrolment->cohort->name }} ({{ $enrolment->cohort->code }})</p>
                                    @endif
                                    <p class="text-sm text-gray-600">Enrolled: {{ $enrolment->enrolment_date->format('d M Y') }}</p>
                                </div>
                                
                                <div class="mt-3 sm:mt-0 sm:ml-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        {{ $enrolment->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $enrolment->status === 'deferred' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $enrolment->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ !in_array($enrolment->status, ['active', 'deferred', 'completed']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($enrolment->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>