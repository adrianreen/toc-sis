<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Grade Record Details
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $gradeRecord->student->full_name }} - {{ $gradeRecord->moduleInstance->module->title }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('grade-records.module-grading', $gradeRecord->moduleInstance) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                    Back to Module Grading
                </a>
                @can('update', $gradeRecord)
                <a href="{{ route('grade-records.edit', $gradeRecord) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Edit Grade
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Grade Record Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assessment Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Student</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $gradeRecord->student->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $gradeRecord->student->student_number }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Module</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $gradeRecord->moduleInstance->module->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $gradeRecord->moduleInstance->module->module_code }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Assessment Component</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $gradeRecord->assessment_component_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Grade</label>
                                    <div class="mt-1">
                                        @if($gradeRecord->grade !== null)
                                            <span class="text-lg font-semibold {{ $gradeRecord->percentage >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $gradeRecord->grade }}
                                            </span>
                                            <span class="text-sm text-gray-500 ml-2">({{ $gradeRecord->percentage }}%)</span>
                                        @else
                                            <span class="text-gray-400">Not graded</span>
                                        @endif
                                    </div>
                                </div>
                                @if($gradeRecord->submission_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Submission Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $gradeRecord->submission_date->format('d M Y H:i') }}</p>
                                </div>
                                @endif
                                @if($gradeRecord->graded_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Graded Date</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $gradeRecord->graded_at->format('d M Y H:i') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Feedback -->
                    @if($gradeRecord->feedback)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Feedback</h3>
                            <div class="prose max-w-none">
                                <p class="text-gray-700">{{ $gradeRecord->feedback }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Grade History -->
                    @if($gradeHistory->count() > 1)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Grade History</h3>
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($gradeHistory as $index => $record)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full {{ $loop->first ? 'bg-green-500' : 'bg-gray-400' }} flex items-center justify-center ring-8 ring-white">
                                                        @if($loop->first)
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @else
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                            </svg>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            Grade: <span class="font-medium text-gray-900">{{ $record->grade ?? 'Ungraded' }}</span>
                                                            @if($record->percentage)
                                                                ({{ $record->percentage }}%)
                                                            @endif
                                                        </p>
                                                        @if($record->gradedBy)
                                                            <p class="text-sm text-gray-500">by {{ $record->gradedBy->name }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        @if($record->graded_at)
                                                            {{ $record->graded_at->format('d M Y H:i') }}
                                                        @else
                                                            {{ $record->created_at->format('d M Y H:i') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Visibility</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $gradeRecord->is_visible_to_student ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $gradeRecord->is_visible_to_student ? 'Visible' : 'Hidden' }}
                                    </span>
                                </div>
                                @if($gradeRecord->release_date)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Release Date</span>
                                    <span class="text-sm text-gray-900">{{ $gradeRecord->release_date->format('d M Y H:i') }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Pass Status</span>
                                    @if($gradeRecord->percentage !== null)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $gradeRecord->percentage >= 40 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $gradeRecord->percentage >= 40 ? 'Pass' : 'Fail' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Pending</span>
                                    @endif
                                </div>
                                @if($gradeRecord->attempts > 1)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Attempt</span>
                                    <span class="text-sm text-gray-900">{{ $gradeRecord->attempts }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Component Info -->
                    @if($assessmentComponent)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Component Details</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Weighting</span>
                                    <span class="text-gray-900 font-medium">{{ $assessmentComponent['weighting'] }}%</span>
                                </div>
                                @if(isset($assessmentComponent['component_pass_mark']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Pass Mark</span>
                                    <span class="text-gray-900">{{ $assessmentComponent['component_pass_mark'] }}%</span>
                                </div>
                                @endif
                                @if(isset($assessmentComponent['is_must_pass']) && $assessmentComponent['is_must_pass'])
                                <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-medium text-yellow-800">Must Pass Component</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    @can('update', $gradeRecord)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                            <div class="space-y-3">
                                <!-- Toggle Visibility -->
                                <form method="POST" action="{{ route('grade-records.toggle-visibility', $gradeRecord) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="w-full inline-flex justify-center items-center px-4 py-2 {{ $gradeRecord->is_visible_to_student ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500' }} border border-transparent rounded-md font-medium text-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition duration-150 ease-in-out">
                                        {{ $gradeRecord->is_visible_to_student ? 'Hide from Student' : 'Show to Student' }}
                                    </button>
                                </form>

                                <!-- Schedule Release -->
                                @if(!$gradeRecord->is_visible_to_student)
                                <button onclick="showScheduleModal()" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                    Schedule Release
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Release Modal (if needed) -->
    <div id="scheduleModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Schedule Grade Release</h3>
            <form method="POST" action="{{ route('grade-records.schedule-release', $gradeRecord) }}">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label for="release_date" class="block text-sm font-medium text-gray-700">Release Date & Time</label>
                    <input type="datetime-local" 
                           id="release_date" 
                           name="release_date" 
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="hideScheduleModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('hidden');
        }
        
        function hideScheduleModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
        }
    </script>
</x-app-layout>