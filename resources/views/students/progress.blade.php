@php
    // Determine if this is an admin view or student view - MUST BE FIRST
    $isAdminView = Auth::user()->role !== 'student';
@endphp

<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                @if($isAdminView)
                    <h1 class="text-3xl font-bold text-gray-900">{{ $student->full_name }}'s Academic Progress</h1>
                    <p class="mt-2 text-gray-600">Student Number: {{ $student->student_number }} • Complete academic history</p>
                @else
                    <h1 class="text-3xl font-bold text-gray-900">My Academic Progress</h1>
                    <p class="mt-2 text-gray-600">Track your progress across all modules and assessments</p>
                @endif
            </div>

            @php
                // Get appropriate grade records based on context
                if ($isAdminView) {
                    // Admin view: show all historical grade records
                    $gradeRecords = $student->studentGradeRecords;
                } else {
                    // Student view: only show current enrollment grade records
                    $gradeRecords = $student->getCurrentGradeRecords()->get();
                }
                
                // Calculate overall statistics from appropriate grade records
                $totalGradeRecords = $gradeRecords->count();
                $completedGradeRecords = $gradeRecords->whereNotNull('percentage')->count();
                $passedGradeRecords = $gradeRecords->where('percentage', '>=', 40)->count();
                
                // For students, only include visible records; for admin, include all
                if ($isAdminView) {
                    $averageGradeRecords = $gradeRecords->whereNotNull('percentage');
                } else {
                    $averageGradeRecords = $gradeRecords->filter(function($gradeRecord) {
                        return $gradeRecord->percentage !== null && $gradeRecord->is_visible_to_student;
                    });
                }
                $overallAverage = $averageGradeRecords->count() > 0 ? $averageGradeRecords->avg('percentage') : null;
                
                $completionPercentage = $totalGradeRecords > 0 ? round(($completedGradeRecords / $totalGradeRecords) * 100, 1) : 0;
                $passRate = $completedGradeRecords > 0 ? round(($passedGradeRecords / $completedGradeRecords) * 100, 1) : 0;
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
                            <p class="text-3xl font-bold text-blue-600">{{ $totalGradeRecords }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ $completedGradeRecords }} completed</p>
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
            @php
                $moduleGroups = $gradeRecords->groupBy('module_instance_id');
            @endphp
            @if($moduleGroups->count() > 0)
            <div class="bg-white shadow-soft rounded-xl p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Module Progress</h2>
                
                <div class="space-y-6">
                    @foreach($moduleGroups as $moduleInstanceId => $gradeRecords)
                    @php
                        $moduleInstance = $gradeRecords->first()->moduleInstance;
                        $module = $moduleInstance->module;
                        $moduleTotal = $gradeRecords->count();
                        $moduleCompleted = $gradeRecords->whereNotNull('percentage')->count();
                        $modulePassRate = $moduleCompleted > 0 ? round(($gradeRecords->where('percentage', '>=', 40)->count() / $moduleCompleted) * 100, 1) : 0;
                        $moduleProgress = $moduleTotal > 0 ? round(($moduleCompleted / $moduleTotal) * 100, 1) : 0;

                        $finalGrade = null;
                        if ($moduleCompleted === $moduleTotal && $moduleTotal > 0) {
                            $weightedSum = 0;
                            $totalWeight = 0;
                            foreach ($gradeRecords as $gradeRecord) {
                                if ($gradeRecord->percentage !== null && isset($module->assessment_strategy)) {
                                    $component = collect($module->assessment_strategy)->firstWhere('component_name', $gradeRecord->assessment_component_name);
                                    if ($component) {
                                        $weight = $component['weighting'];
                                        $weightedSum += ($gradeRecord->percentage * $weight);
                                        $totalWeight += $weight;
                                    }
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
                                <p class="text-sm text-gray-600">{{ $module->module_code }}</p>
                                <p class="text-sm text-gray-600">{{ ucfirst($moduleInstance->delivery_style) }} delivery</p>
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
                                    {{ $moduleProgress === 100 ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $moduleProgress === 100 ? 'Completed' : 'In Progress' }}
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
                        @if($gradeRecords->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($gradeRecords->sortBy('assessment_component_name') as $gradeRecord)
                            @php
                                $component = collect($module->assessment_strategy)->firstWhere('component_name', $gradeRecord->assessment_component_name);
                            @endphp
                            <div class="border border-gray-100 rounded-lg p-4 hover:border-gray-200 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $gradeRecord->assessment_component_name }}</h4>
                                        @if($component)
                                            <p class="text-sm text-gray-500">{{ $component['weighting'] }}% of final grade</p>
                                        @endif
                                        @if($gradeRecord->submission_date)
                                            <p class="text-sm text-gray-500">Submitted: {{ $gradeRecord->submission_date->format('d M Y') }}</p>
                                        @endif
                                    </div>

                                    <div class="ml-3 text-right">
                                        @if($gradeRecord->percentage !== null && $gradeRecord->is_visible_to_student)
                                            <p class="text-lg font-bold {{ $gradeRecord->percentage >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $gradeRecord->percentage }}%
                                            </p>
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                                {{ $gradeRecord->percentage >= 40 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $gradeRecord->percentage >= 40 ? 'PASS' : 'FAIL' }}
                                            </span>
                                        @elseif($gradeRecord->percentage !== null && !$gradeRecord->is_visible_to_student)
                                            <p class="text-sm text-gray-500">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                                Graded
                                            </p>
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                Results Pending
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                Not Graded
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($gradeRecord->feedback && $gradeRecord->is_visible_to_student)
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <p class="text-sm text-gray-600"><strong>Feedback:</strong></p>
                                    <p class="text-sm text-gray-700 mt-1">{{ Str::limit($gradeRecord->feedback, 100) }}</p>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
