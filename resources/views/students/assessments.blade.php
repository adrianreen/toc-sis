<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Assessments</h1>
                <p class="mt-2 text-gray-600">Track your assessment deadlines, submissions, and results</p>
            </div>

            @php
                // Get current date for comparisons
                $now = now();
                $oneWeekFromNow = $now->copy()->addWeek();
                
                // Organize assessments
                $allAssessments = $student->studentModuleEnrolments->flatMap->studentAssessments;
                
                // Upcoming assessments (pending and due within next 30 days)
                $upcomingAssessments = $upcomingAssessments->filter(function($assessment) use ($now) {
                    return $assessment->status === 'pending' && $assessment->due_date >= $now;
                })->sortBy('due_date');
                
                // Overdue assessments
                $overdueAssessments = $allAssessments->filter(function($assessment) use ($now) {
                    return $assessment->status === 'pending' && $assessment->due_date < $now;
                })->sortBy('due_date');
                
                // Recent results (last 10 graded assessments)
                $recentResults = $recentAssessments->filter(function($assessment) {
                    return in_array($assessment->status, ['graded', 'passed', 'failed']);
                })->sortByDesc('graded_date');
                
                // Submitted awaiting grading
                $awaitingGrading = $allAssessments->filter(function($assessment) {
                    return $assessment->status === 'submitted';
                })->sortByDesc('submission_date');
            @endphp

            <!-- Alert for overdue assessments -->
            @if($overdueAssessments->count() > 0)
            <div class="mb-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Overdue Assessments</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>You have {{ $overdueAssessments->count() }} overdue assessment{{ $overdueAssessments->count() !== 1 ? 's' : '' }}. Please contact your tutor as soon as possible.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Upcoming Assessments -->
                    <div class="bg-white shadow-soft rounded-xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Upcoming Assessments</h2>
                            @if($upcomingAssessments->count() > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-toc-100 text-toc-800">
                                    {{ $upcomingAssessments->count() }} due
                                </span>
                            @endif
                        </div>
                        
                        @if($upcomingAssessments->count() > 0)
                            <div class="space-y-4">
                                @foreach($upcomingAssessments->take(5) as $assessment)
                                @php
                                    $daysUntilDue = $now->diffInDays($assessment->due_date, false);
                                    $isUrgent = $daysUntilDue <= 7;
                                    $isThisWeek = $daysUntilDue <= 7 && $daysUntilDue >= 0;
                                @endphp
                                
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors
                                    {{ $isUrgent ? 'bg-yellow-50 border-yellow-200' : '' }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $assessment->assessmentComponent->name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $assessment->studentModuleEnrolment->moduleInstance->module->title }}</p>
                                            <p class="text-sm text-gray-600">{{ $assessment->studentModuleEnrolment->moduleInstance->module->code }}</p>
                                            <p class="text-sm font-medium {{ $isUrgent ? 'text-yellow-700' : 'text-gray-700' }}">
                                                Due: {{ $assessment->due_date->format('D, d M Y') }}
                                                @if($daysUntilDue == 0)
                                                    (Today!)
                                                @elseif($daysUntilDue == 1)
                                                    (Tomorrow)
                                                @elseif($daysUntilDue > 0)
                                                    ({{ $daysUntilDue }} days)
                                                @endif
                                            </p>
                                        </div>
                                        
                                        <div class="ml-4 text-right">
                                            <p class="text-sm text-gray-500">Weight</p>
                                            <p class="text-lg font-semibold text-toc-600">{{ $assessment->assessmentComponent->weight }}%</p>
                                            
                                            @if($isUrgent)
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-2">
                                                    Due Soon
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            @if($upcomingAssessments->count() > 5)
                                <div class="mt-4 text-center">
                                    <p class="text-sm text-gray-500">And {{ $upcomingAssessments->count() - 5 }} more...</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-lg font-medium text-gray-900">All caught up!</h3>
                                <p class="mt-1 text-gray-500">No upcoming assessment deadlines.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Recent Results -->
                <!-- Recent Results -->
<div class="bg-white shadow-soft rounded-xl p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Recent Results</h2>
    </div>
    
    @php
        // Filter recent results to only show visible grades
        $visibleResults = $recentResults->filter(function($assessment) {
            return $assessment->isVisibleToStudent();
        });
    @endphp
    
    @if($visibleResults->count() > 0)
        <div class="space-y-4">
            @foreach($visibleResults->take(5) as $assessment)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900">{{ $assessment->assessmentComponent->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $assessment->studentModuleEnrolment->moduleInstance->module->title }}</p>
                        <p class="text-sm text-gray-500">
                            Graded: {{ $assessment->graded_date ? $assessment->graded_date->format('d M Y') : 'Recently' }}
                        </p>
                        
                        @if($assessment->feedback)
                            <div class="mt-2 p-2 bg-gray-50 rounded text-sm">
                                <p class="font-medium text-gray-700">Feedback:</p>
                                <p class="text-gray-600">{{ Str::limit($assessment->feedback, 150) }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="ml-4 text-right">
                        @if($assessment->grade !== null)
                            <p class="text-2xl font-bold {{ $assessment->grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $assessment->grade }}%
                            </p>
                        @endif
                        
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $assessment->status === 'passed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $assessment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $assessment->status === 'graded' ? 'bg-blue-100 text-blue-800' : '' }}">
                            {{ $assessment->grade >= 40 ? 'PASS' : 'FAIL' }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No results available</h3>
            <p class="mt-1 text-gray-500">Your assessment results will appear here once they are released.</p>
        </div>
    @endif
</div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Quick Stats -->
                    <div class="bg-white shadow-soft rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assessment Overview</h3>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Upcoming</span>
                                <span class="font-semibold text-toc-600">{{ $upcomingAssessments->count() }}</span>
                            </div>
                            
                            @if($overdueAssessments->count() > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Overdue</span>
                                <span class="font-semibold text-red-600">{{ $overdueAssessments->count() }}</span>
                            </div>
                            @endif
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Awaiting Results</span>
                                <span class="font-semibold text-yellow-600">{{ $awaitingGrading->count() }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Completed</span>
                                <span class="font-semibold text-green-600">{{ $recentResults->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Overdue Items (if any) -->
                    @if($overdueAssessments->count() > 0)
                    <div class="bg-red-50 rounded-xl p-6 border border-red-200">
                        <h3 class="text-lg font-semibold text-red-900 mb-4">Overdue Assessments</h3>
                        
                        <div class="space-y-3">
                            @foreach($overdueAssessments->take(3) as $assessment)
                            <div class="text-sm">
                                <p class="font-medium text-red-800">{{ $assessment->assessmentComponent->name }}</p>
                                <p class="text-red-600">{{ $assessment->studentModuleEnrolment->moduleInstance->module->code }}</p>
                                <p class="text-red-600">Due: {{ $assessment->due_date->format('d M Y') }}</p>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 p-3 bg-red-100 rounded-lg">
                            <p class="text-xs text-red-800">
                                <strong>Action Required:</strong> Contact your tutor immediately to discuss these overdue assessments.
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Submitted Assessments -->
                    @if($awaitingGrading->count() > 0)
                    <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-4">Awaiting Results</h3>
                        
                        <div class="space-y-3">
                            @foreach($awaitingGrading->take(3) as $assessment)
                            <div class="text-sm">
                                <p class="font-medium text-yellow-800">{{ $assessment->assessmentComponent->name }}</p>
                                <p class="text-yellow-600">{{ $assessment->studentModuleEnrolment->moduleInstance->module->code }}</p>
                                @if($assessment->submission_date)
                                    <p class="text-yellow-600">Submitted: {{ $assessment->submission_date->format('d M Y') }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Help Section -->
                    <div class="bg-blue-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3">Need Help?</h3>
                        <div class="text-sm text-blue-700 space-y-2">
                            <p>Questions about your assessments? Contact your module tutor or Student Services.</p>
                            <div class="pt-2 border-t border-blue-200">
                                <p class="font-medium">Student Services</p>
                                <p>studentservices@theopencollege.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>