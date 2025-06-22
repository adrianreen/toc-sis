<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Assessments</h1>
                <p class="mt-2 text-gray-600">Track your assessment submissions and results</p>
            </div>

            @php
                // Get all grade records for the student
                $allGradeRecords = $student->studentGradeRecords->load(['moduleInstance.module']);
                
                // Submitted awaiting grading (has submission_date but no grade)
                $awaitingGrading = $allGradeRecords->filter(function($record) {
                    return $record->submission_date && $record->percentage === null;
                })->sortByDesc('submission_date');
                
                // Recent results (has percentage and is visible)
                $recentResults = $allGradeRecords->filter(function($record) {
                    return $record->percentage !== null && $record->is_visible_to_student;
                })->sortByDesc('grading_date')->take(10);
                
                // Not yet submitted (no submission date)
                $notSubmitted = $allGradeRecords->filter(function($record) {
                    return !$record->submission_date;
                })->sortBy('component_name');
                
                // Hidden results (graded but not visible)
                $hiddenResults = $allGradeRecords->filter(function($record) {
                    return $record->percentage !== null && !$record->is_visible_to_student;
                })->sortBy('component_name');
            @endphp

            <!-- Alert for hidden results -->
            @if($hiddenResults->count() > 0)
            <div class="mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Results Pending Release</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>You have {{ $hiddenResults->count() }} graded assessment{{ $hiddenResults->count() !== 1 ? 's' : '' }} awaiting result release by your tutor.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Recent Results -->
                    <div class="bg-white shadow-soft rounded-xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Recent Results</h2>
                            @if($recentResults->count() > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $recentResults->count() }} results
                                </span>
                            @endif
                        </div>
                        
                        @if($recentResults->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentResults as $record)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $record->component_name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $record->moduleInstance->module->title }}</p>
                                            <p class="text-sm text-gray-600">{{ $record->moduleInstance->module->module_code }}</p>
                                            @if($record->grading_date)
                                                <p class="text-sm text-gray-500">Graded: {{ $record->grading_date->format('d M Y') }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-2xl font-bold {{ $record->percentage >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $record->percentage }}%
                                            </div>
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                                {{ $record->percentage >= 40 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $record->percentage >= 40 ? 'PASS' : 'FAIL' }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($record->feedback)
                                        <div class="mt-3 pt-3 border-t border-gray-100">
                                            <p class="text-sm text-gray-600"><strong>Feedback:</strong></p>
                                            <p class="text-sm text-gray-700 mt-1">{{ $record->feedback }}</p>
                                        </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-4">
                                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Results Yet</h3>
                                <p class="text-gray-500">Your assessment results will appear here once they are graded and released.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Awaiting Grading -->
                    @if($awaitingGrading->count() > 0)
                    <div class="bg-white shadow-soft rounded-xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Awaiting Grading</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ $awaitingGrading->count() }} submitted
                            </span>
                        </div>
                        
                        <div class="space-y-4">
                            @foreach($awaitingGrading as $record)
                            <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900">{{ $record->component_name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $record->moduleInstance->module->title }}</p>
                                        <p class="text-sm text-gray-600">{{ $record->moduleInstance->module->module_code }}</p>
                                        <p class="text-sm text-yellow-700">Submitted: {{ $record->submission_date->format('d M Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            Awaiting Grading
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Quick Stats -->
                    <div class="bg-white shadow-soft rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assessment Summary</h3>
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Assessments</span>
                                <span class="font-semibold">{{ $allGradeRecords->count() }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Results Available</span>
                                <span class="font-semibold text-green-600">{{ $recentResults->count() }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Awaiting Grading</span>
                                <span class="font-semibold text-yellow-600">{{ $awaitingGrading->count() }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Not Submitted</span>
                                <span class="font-semibold text-gray-600">{{ $notSubmitted->count() }}</span>
                            </div>
                            
                            @if($recentResults->count() > 0)
                            <div class="pt-4 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Average Grade</span>
                                    <span class="font-semibold text-lg {{ $recentResults->avg('percentage') >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($recentResults->avg('percentage'), 1) }}%
                                    </span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Not Submitted -->
                    @if($notSubmitted->count() > 0)
                    <div class="bg-white shadow-soft rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pending Submissions</h3>
                        
                        <div class="space-y-3">
                            @foreach($notSubmitted->take(5) as $record)
                            <div class="border border-gray-200 rounded-lg p-3">
                                <h4 class="font-medium text-gray-900 text-sm">{{ $record->component_name }}</h4>
                                <p class="text-xs text-gray-600">{{ $record->moduleInstance->module->module_code }}</p>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 mt-2">
                                    Not Submitted
                                </span>
                            </div>
                            @endforeach
                            
                            @if($notSubmitted->count() > 5)
                                <p class="text-xs text-gray-500 text-center">
                                    + {{ $notSubmitted->count() - 5 }} more
                                </p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>