{{-- resources/views/assessments/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @if(Auth::user()->role === 'teacher')
                    My Assessments & Grading
                @else
                    Assessment Management
                @endif
            </h2>
            <div class="space-x-2">
                @if($stats['pending_grading'] > 0)
                    <span class="bg-orange-500 text-white font-bold py-2 px-4 rounded">
                        Pending Grading: {{ $stats['pending_grading'] }}
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Assessment Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Assessments</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['total_assessments']) }}</div>
                        <div class="mt-2 text-sm text-gray-600">
                            <span class="text-blue-600">{{ $stats['graded_today'] }} graded today</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Pending Grading</div>
                        <div class="mt-1 text-3xl font-semibold {{ $stats['pending_grading'] > 0 ? 'text-orange-600' : 'text-gray-900' }}">
                            {{ $stats['pending_grading'] }}
                        </div>
                        @if($stats['overdue_release'] > 0)
                            <div class="mt-2 text-sm text-red-600">
                                {{ $stats['overdue_release'] }} overdue for release
                            </div>
                        @else
                            <div class="mt-2 text-sm text-gray-600">All up to date</div>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Graded Today</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $stats['graded_today'] }}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            Recent activity
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Active Modules</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ Auth::user()->role === 'teacher' ? $moduleInstances->count() : $moduleInstances->total() }}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            @if(Auth::user()->role === 'teacher')
                                Your assigned modules
                            @else
                                System-wide modules
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Instances -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        @if(Auth::user()->role === 'teacher')
                            Your Assigned Modules
                        @else
                            All Module Instances
                        @endif
                    </h3>
                    
                    @if($moduleInstances->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Module
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Period
                                        </th>
                                        @if(Auth::user()->role !== 'teacher')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tutor
                                            </th>
                                        @endif
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Grade Records
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Progress
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($moduleInstances as $instance)
                                        @php
                                            $totalGradeRecords = $instance->studentGradeRecords->count();
                                            $gradedRecords = $instance->studentGradeRecords->whereNotNull('grade')->count();
                                            $pendingRecords = $instance->studentGradeRecords->whereNull('grade')->count();
                                            $visibleRecords = $instance->studentGradeRecords->where('is_visible_to_student', true)->count();
                                            $progressPercent = $totalGradeRecords > 0 ? round(($gradedRecords / $totalGradeRecords) * 100) : 0;
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $instance->module->module_code }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $instance->module->title }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $instance->start_date ? $instance->start_date->format('M Y') : 'TBD' }}
                                                    @if($instance->end_date)
                                                        - {{ $instance->end_date->format('M Y') }}
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ ucfirst($instance->delivery_style) }}
                                                </div>
                                            </td>
                                            @if(Auth::user()->role !== 'teacher')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $instance->tutor?->name ?? 'Not Assigned' }}
                                                </td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $totalGradeRecords }} total</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $gradedRecords }} graded, {{ $pendingRecords }} pending
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $progressPercent }}%"></div>
                                                    </div>
                                                    <span class="text-sm text-gray-700">{{ $progressPercent }}%</span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $visibleRecords }} visible to students
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('grade-records.module-grading', $instance) }}" 
                                                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                                        Grade Students
                                                    </a>
                                                    
                                                    @if($totalGradeRecords > 0)
                                                        <a href="{{ route('grade-records.export', $instance) }}" 
                                                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded text-xs">
                                                            Export
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(Auth::user()->role !== 'teacher' && method_exists($moduleInstances, 'links'))
                            <div class="mt-4">
                                {{ $moduleInstances->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No modules assigned</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(Auth::user()->role === 'teacher')
                                    You don't have any modules assigned for grading yet.
                                @else
                                    No module instances have been created yet.
                                @endif
                            </p>
                            @if(Auth::user()->role === 'manager')
                                <div class="mt-6">
                                    <a href="{{ route('module-instances.create') }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                        Create Module Instance
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @if($stats['pending_grading'] > 0 || $stats['overdue_release'] > 0)
                <div class="mt-6 bg-orange-50 border-l-4 border-orange-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-orange-700">
                                <strong>Action Required:</strong> 
                                @if($stats['pending_grading'] > 0)
                                    You have {{ $stats['pending_grading'] }} assessment(s) waiting for grading.
                                @endif
                                @if($stats['overdue_release'] > 0)
                                    {{ $stats['overdue_release'] }} graded assessment(s) are overdue for release to students.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Help Text for Teachers -->
            @if(Auth::user()->role === 'teacher')
                <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Quick Help:</strong> Click "Grade Students" to access the grading interface for each module. 
                                <strong>Use Bulk Grading (recommended)</strong> for efficient grading of multiple students at once, 
                                or use individual grading when you need detailed feedback. Students will automatically 
                                see their results and final grades once you complete grading.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>