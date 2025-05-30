{{-- resources/views/assessments/student-progress.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Student Progress: {{ $student->full_name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $student->student_number }} • {{ $student->email }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <span class="px-3 py-1 rounded-full text-xs font-medium 
                    @if($student->status === 'active') bg-green-100 text-green-800
                    @elseif($student->status === 'deferred') bg-yellow-100 text-yellow-800
                    @elseif($student->status === 'completed') bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst($student->status) }}
                </span>
                <a href="{{ route('students.show', $student) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    View Profile
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Overall Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Overall Progress</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['completion_percentage'] }}%</p>
                                <p class="text-xs text-gray-500">{{ $stats['completed_assessments'] }}/{{ $stats['total_assessments'] }} assessments</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Average Grade</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    @if($stats['overall_average'])
                                        {{ $stats['overall_average'] }}%
                                    @else
                                        -
                                    @endif
                                </p>
                                <p class="text-xs {{ $stats['pass_rate'] >= 80 ? 'text-green-600' : ($stats['pass_rate'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $stats['pass_rate'] }}% pass rate
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending Work</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_assessments'] + $stats['submitted_assessments'] }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $stats['pending_assessments'] }} pending, {{ $stats['submitted_assessments'] }} submitted
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Programmes</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $programmeProgress->count() }}</p>
                                <p class="text-xs text-gray-500">Active enrolments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Programme Progress -->
            @if($programmeProgress->count() > 0)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Programme Progress</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @foreach($programmeProgress as $progress)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">
                                                {{ $progress['enrolment']->programme->code }} - {{ $progress['enrolment']->programme->title }}
                                            </h4>
                                            @if($progress['enrolment']->cohort)
                                                <p class="text-sm text-gray-500">
                                                    Cohort: {{ $progress['enrolment']->cohort->code }} - {{ $progress['enrolment']->cohort->name }}
                                                </p>
                                            @endif
                                        </div>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            @if($progress['enrolment']->status === 'active') bg-green-100 text-green-800
                                            @elseif($progress['enrolment']->status === 'deferred') bg-yellow-100 text-yellow-800
                                            @elseif($progress['enrolment']->status === 'completed') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($progress['enrolment']->status) }}
                                        </span>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mb-4">
                                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                                            <span>Module Completion</span>
                                            <span>{{ $progress['completion_percentage'] }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progress['completion_percentage'] }}%"></div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <p class="text-lg font-semibold text-gray-900">{{ $progress['completed_modules'] }}</p>
                                            <p class="text-xs text-gray-500">Completed</p>
                                        </div>
                                        <div>
                                            <p class="text-lg font-semibold text-gray-900">{{ $progress['active_modules'] }}</p>
                                            <p class="text-xs text-gray-500">Active</p>
                                        </div>
                                        <div>
                                            <p class="text-lg font-semibold text-gray-900">
                                                @if($progress['programme_average'])
                                                    {{ $progress['programme_average'] }}%
                                                @else
                                                    -
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">Average</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Module Progress -->
                <div class="lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Module Progress</h3>
                    @if($moduleProgress->count() > 0)
                        <div class="space-y-6">
                            @foreach($moduleProgress as $module)
                                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <div>
                                                <h4 class="text-lg font-medium text-gray-900">
                                                    {{ $module['module_enrolment']->moduleInstance->instance_code }}
                                                </h4>
                                                <p class="text-sm text-gray-500">
                                                    {{ $module['module_enrolment']->moduleInstance->module->title }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                @if($module['final_grade'])
                                                    <p class="text-lg font-semibold {{ $module['final_grade'] >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $module['final_grade'] }}%
                                                    </p>
                                                    <p class="text-xs {{ $module['final_grade'] >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $module['final_grade'] >= 40 ? 'PASS' : 'FAIL' }}
                                                    </p>
                                                @else
                                                    <p class="text-lg font-semibold text-gray-400">-</p>
                                                    <p class="text-xs text-gray-500">In Progress</p>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Assessment Progress -->
                                        <div class="mb-4">
                                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                                <span>Assessment Progress</span>
                                                <span>{{ $module['completion_percentage'] }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $module['completion_percentage'] }}%"></div>
                                            </div>
                                        </div>

                                        <!-- Individual Assessments -->
                                        <div class="space-y-3">
                                            @foreach($module['assessments'] as $assessment)
                                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                    <div class="flex-1">
                                                        <h5 class="font-medium text-gray-900">{{ $assessment->assessmentComponent->name }}</h5>
                                                        <p class="text-sm text-gray-500">
                                                            {{ ucfirst($assessment->assessmentComponent->type) }} • {{ $assessment->assessmentComponent->weight }}% weight
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            Due: {{ $assessment->due_date->format('d M Y') }}
                                                            @if($assessment->due_date->isPast() && $assessment->status === 'pending')
                                                                <span class="text-red-600 font-medium">(Overdue)</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        @if($assessment->grade !== null)
                                                            <p class="text-lg font-semibold {{ $assessment->grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                                {{ number_format($assessment->grade, 1) }}%
                                                            </p>
                                                        @endif
                                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                                            @if($assessment->status === 'passed') bg-green-100 text-green-800
                                                            @elseif($assessment->status === 'failed') bg-red-100 text-red-800
                                                            @elseif($assessment->status === 'submitted') bg-orange-100 text-orange-800
                                                            @elseif($assessment->status === 'graded') bg-blue-100 text-blue-800
                                                            @else bg-gray-100 text-gray-800
                                                            @endif">
                                                            {{ ucfirst($assessment->status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No module enrolments</h3>
                                <p class="mt-1 text-sm text-gray-500">This student is not enrolled in any modules yet.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Upcoming Deadlines -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Upcoming Deadlines</h4>
                            @if($upcomingDeadlines->count() > 0)
                                <div class="space-y-3">
                                    @foreach($upcomingDeadlines as $deadline)
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $deadline->assessmentComponent->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $deadline->studentModuleEnrolment->moduleInstance->instance_code }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium {{ $deadline->due_date->isToday() ? 'text-red-600' : ($deadline->due_date->diffInDays() <= 3 ? 'text-orange-600' : 'text-gray-900') }}">
                                                    {{ $deadline->due_date->format('M j') }}
                                                </p>
                                                <p class="text-xs text-gray-500">{{ $deadline->due_date->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No upcoming deadlines.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h4>
                            @if($recentActivity->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentActivity as $activity)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-900">
                                                    {{ $activity->description }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                    @if($activity->causer)
                                                        by {{ $activity->causer->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No recent activity.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>