<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Module Completion Status
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $moduleInstance->module->title }} ({{ $moduleInstance->module->module_code }})
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('grade-records.module-grading', $moduleInstance) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                    Back to Grading
                </a>
                <button onclick="exportCompletionData()" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    Export Data
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Students</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['total_students'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                                    <dd class="text-lg font-medium text-green-600">{{ $stats['completed'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">In Progress</dt>
                                    <dd class="text-lg font-medium text-yellow-600">{{ $stats['in_progress'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Not Started</dt>
                                    <dd class="text-lg font-medium text-red-600">{{ $stats['not_started'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search Student</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Name or student number..."
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="completion_status" class="block text-sm font-medium text-gray-700">Completion Status</label>
                            <select id="completion_status" 
                                    name="completion_status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">All Students</option>
                                <option value="completed" {{ request('completion_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="in_progress" {{ request('completion_status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="not_started" {{ request('completion_status') === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                <option value="failed" {{ request('completion_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>

                        <div>
                            <label for="pass_fail" class="block text-sm font-medium text-gray-700">Pass/Fail Status</label>
                            <select id="pass_fail" 
                                    name="pass_fail"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">All</option>
                                <option value="pass" {{ request('pass_fail') === 'pass' ? 'selected' : '' }}>Pass</option>
                                <option value="fail" {{ request('pass_fail') === 'fail' ? 'selected' : '' }}>Fail</option>
                                <option value="pending" {{ request('pass_fail') === 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Student Completion Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Student Completion Details</h3>
                    
                    @if($completionData->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Progress
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Components Completed
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Overall Grade
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Last Activity
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($completionData as $completion)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $completion['student']->full_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $completion['student']->student_number }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $completion['progress_percentage'] }}%"></div>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $completion['progress_percentage'] }}% complete</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $completion['completed_components'] }} / {{ $completion['total_components'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($completion['overall_grade'] !== null)
                                                    <div class="text-sm font-medium {{ $completion['overall_grade'] >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $completion['overall_grade'] }}%
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-sm">Pending</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($completion['status'] === 'completed') bg-green-100 text-green-800
                                                    @elseif($completion['status'] === 'in_progress') bg-yellow-100 text-yellow-800
                                                    @elseif($completion['status'] === 'failed') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $completion['status'])) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($completion['last_activity'])
                                                    {{ $completion['last_activity']->diffForHumans() }}
                                                @else
                                                    No activity
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('students.show', $completion['student']) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">View</a>
                                                @if($completion['has_grades'])
                                                    <a href="{{ route('grade-records.module-grading', [$moduleInstance, 'student' => $completion['student']->id]) }}" 
                                                       class="text-blue-600 hover:text-blue-900">Grades</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(method_exists($completionData, 'withQueryString'))
                        <div class="mt-6">
                            {{ $completionData->withQueryString()->links() }}
                        </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 mb-4">
                                No students found for this module.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assessment Components Progress -->
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assessment Components Progress</h3>
                    
                    @if($componentStats && count($componentStats) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($componentStats as $componentName => $stats)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900 mb-3">{{ $componentName }}</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Completed:</span>
                                            <span class="font-medium text-green-600">{{ $stats['completed'] }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Pending:</span>
                                            <span class="font-medium text-yellow-600">{{ $stats['pending'] }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Average Grade:</span>
                                            <span class="font-medium text-gray-900">
                                                @if($stats['average_grade'])
                                                    {{ number_format($stats['average_grade'], 1) }}%
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['completion_rate'] }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 text-center">{{ $stats['completion_rate'] }}% completion</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No assessment components defined for this module.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportCompletionData() {
            const params = new URLSearchParams(window.location.search);
            params.set('export', 'true');
            window.location.href = '{{ route("grade-records.module-completion", $moduleInstance) }}?' + params.toString();
        }
    </script>
</x-app-layout>