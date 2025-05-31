<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Academic Progress</h1>
                <p class="mt-2 text-gray-600">Track your progress across all modules and assessments</p>
            </div>

            @php
                // Calculate overall statistics
                $totalAssessments = $student->studentModuleEnrolments->flatMap->studentAssessments->count();
                $completedAssessments = $student->studentModuleEnrolments->flatMap->studentAssessments->whereIn('status', ['graded', 'passed', 'failed'])->count();
                $passedAssessments = $student->studentModuleEnrolments->flatMap->studentAssessments->where('status', 'passed')->count();
                $overallAverage = $student->studentModuleEnrolments->flatMap->studentAssessments->whereNotNull('grade')->avg('grade');
                $completionPercentage = $totalAssessments > 0 ? round(($completedAssessments / $totalAssessments) * 100, 1) : 0;
                $passRate = $completedAssessments > 0 ? round(($passedAssessments / $completedAssessments) * 100, 1) : 0;
            @endphp

            <!-- Overall Progress Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Overall Completion -->
                <div class="bg-white shadow-soft rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Overall Progress</p>
                            <p class="text-3xl font-bold text-toc-600">{{ $completionPercentage }}%</p>
                        </div>
                        <div class="p-3 bg-toc-100 rounded-full">
                            <svg class="w-6 h-6 text-toc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-toc-600 h-2 rounded-full transition-all duration-300" style="width: {{ $completionPercentage }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Average Grade -->
                <div class="bg-white shadow-soft rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Average Grade</p>
                            <p class="text-3xl font-bold text-green-600">
                                @if($overallAverage)
                                    {{ number_format($overallAverage, 1) }}%
                                @else
                                    --
                                @endif
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Assessments -->
                <div class="bg-white shadow-soft rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Assessments</p>
                            <p class="text-3xl font-bold text-blue-600">{{ $totalAssessments }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ $completedAssessments }} completed</p>
                </div>

                <!-- Pass Rate -->
                <div class="bg-white shadow-soft rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pass Rate</p>
                            <p class="text-3xl font-bold text-purple-600">{{ $passRate }}%</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Progress -->
            @if($student->studentModuleEnrolments->count() > 0)
            <div class="bg-white shadow-soft rounded-xl p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Module Progress</h2>
                
                <div class="space-y-6">
                    @foreach($student->studentModuleEnrolments as $moduleEnrolment)
                    @php
                        $module = $moduleEnrolment->moduleInstance->module;
                        $assessments = $moduleEnrolment->studentAssessments;
                        $moduleTotal = $assessments->count();
                        $moduleCompleted = $assessments->whereIn('status', ['graded', 'passed', 'failed'])->count();
                        $modulePassRate = $moduleCompleted > 0 ? round(($assessments->where('status', 'passed')->count() / $moduleCompleted) * 100, 1) : 0;
                        $moduleProgress = $moduleTotal > 0 ? round(($moduleCompleted / $moduleTotal) * 100, 1) : 0;
                        
                        // Calculate final grade if available
                        $finalGrade = null;
                        if ($moduleEnrolment->final_grade) {
                            $finalGrade = $moduleEnrolment->final_grade;
                        } elseif ($moduleCompleted === $moduleTotal && $moduleTotal > 0) {
                            $weightedSum = 0;
                            $totalWeight = 0;
                            foreach ($assessments as $assessment) {
                                if ($assessment->grade !== null) {
                                    $weight = $assessment->assessmentComponent->weight;
                                    $weightedSum += ($assessment->grade * $weight);
                                    $totalWeight += $weight;
                                }
                            }
                            if ($totalWeight > 0) {
                                $finalGrade = round($weightedSum / $totalWeight, 1);
                            }
                        }
                    @endphp
                    
                    <div class="border border-gray-200 rounded-lg p-6">
                        <!-- Module Header -->
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $module->title }}</h3>
                                <p class="text-sm text-gray-600">{{ $module->code }}</p>
                                @if($moduleEnrolment->moduleInstance->cohort)
                                    <p class="text-sm text-gray-600">Cohort: {{ $moduleEnrolment->moduleInstance->cohort->name }}</p>
                                @endif
                            </div>
                            
                            <div class="mt-3 lg:mt-0 flex items-center space-x-4">
                                @if($finalGrade)
                                    <div class="text-center">
                                        <p class="text-sm text-gray-500">Final Grade</p>
                                        <p class="text-2xl font-bold {{ $finalGrade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $finalGrade }}%
                                        </p>
                                    </div>
                                @endif
                                
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Progress</p>
                                    <p class="text-lg font-semibold text-toc-600">{{ $moduleProgress }}%</p>
                                </div>
                                
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $moduleEnrolment->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $moduleEnrolment->status === 'active' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $moduleEnrolment->status === 'enrolled' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $moduleEnrolment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $moduleEnrolment->status)) }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-toc-600 h-2 rounded-full transition-all duration-300" style="width: {{ $moduleProgress }}%"></div>
                            </div>
                        </div>
                        
                        <!-- Assessment Grid -->
                        @if($assessments->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($assessments->sortBy('assessmentComponent.sequence') as $assessment)
                            <div class="border border-gray-100 rounded-lg p-4 hover:border-gray-200 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $assessment->assessmentComponent->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $assessment->assessmentComponent->weight }}% of final grade</p>
                                        <p class="text-sm text-gray-500">Due: {{ $assessment->due_date->format('d M Y') }}</p>
                                    </div>
                                    
                                    <div class="ml-3 text-right">
                                        @if($assessment->grade !== null)
                                            <p class="text-lg font-bold {{ $assessment->grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $assessment->grade }}%
                                            </p>
                                        @endif
                                        
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                            {{ $assessment->status === 'passed' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $assessment->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $assessment->status === 'submitted' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $assessment->status === 'pending' ? 'bg-gray-100 text-gray-700' : '' }}
                                            {{ $assessment->status === 'graded' ? 'bg-blue-100 text-blue-700' : '' }}">
                                            {{ ucfirst($assessment->status) }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if($assessment->feedback)
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <p class="text-sm text-gray-600"><strong>Feedback:</strong></p>
                                    <p class="text-sm text-gray-700 mt-1">{{ Str::limit($assessment->feedback, 100) }}</p>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-gray-500 text-center py-4">No assessments available for this module yet.</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <!-- No Modules State -->
            <div class="bg-white shadow-soft rounded-xl p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">No modules yet</h3>
                <p class="mt-1 text-gray-500">You haven't been enrolled in any modules yet. Contact Student Services if you think this is an error.</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>