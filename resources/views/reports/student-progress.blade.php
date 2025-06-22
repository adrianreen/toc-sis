<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Student Progress Report
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $student->full_name }} ({{ $student->student_number }})
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('students.show', $student) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                    Back to Student
                </a>
                <button onclick="exportProgressReport()" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    Export Report
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Student Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Student Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Student Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $student->full_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Student Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $student->student_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $student->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $student->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $student->status === 'deferred' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $student->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ !in_array($student->status, ['active', 'deferred', 'completed']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Enrolments -->
            @if($student->enrolments->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Enrolments</h3>
                    <div class="space-y-4">
                        @foreach($student->enrolments as $enrolment)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        @if($enrolment->enrolment_type === 'programme')
                                            <h4 class="font-medium text-gray-900">{{ $enrolment->programmeInstance->programme->title }}</h4>
                                            <p class="text-sm text-gray-600">{{ $enrolment->programmeInstance->label }}</p>
                                            <p class="text-sm text-gray-500">Code: {{ $enrolment->programmeInstance->programme->code }}</p>
                                        @else
                                            <h4 class="font-medium text-gray-900">{{ $enrolment->moduleInstance->module->title }}</h4>
                                            <p class="text-sm text-gray-600">Standalone Module</p>
                                            <p class="text-sm text-gray-500">Code: {{ $enrolment->moduleInstance->module->module_code }}</p>
                                        @endif
                                        <p class="text-sm text-gray-500">Enrolled: {{ $enrolment->created_at->format('d M Y') }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $enrolment->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $enrolment->status === 'deferred' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $enrolment->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ !in_array($enrolment->status, ['active', 'deferred', 'completed']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($enrolment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Module Progress -->
            @if($moduleProgress && $moduleProgress->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Module Progress</h3>
                    <div class="space-y-6">
                        @foreach($moduleProgress as $module)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $module->title }}</h4>
                                        <p class="text-sm text-gray-600">{{ $module->code }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $module->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $module->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $module->status === 'not_started' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst(str_replace('_', ' ', $module->status)) }}
                                        </span>
                                        @if($module->final_grade !== null)
                                            <div class="text-lg font-semibold mt-1 {{ $module->final_grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $module->final_grade }}%
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Progress</span>
                                        <span>{{ $module->progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $module->progress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Detailed Grade Records -->
            @if($student->studentGradeRecords->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Grade Records</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Module
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Assessment Component
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Grade
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Graded Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Visibility
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($student->studentGradeRecords->sortByDesc('graded_at') as $gradeRecord)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $gradeRecord->moduleInstance->module->title }}</div>
                                            <div class="text-sm text-gray-500">{{ $gradeRecord->moduleInstance->module->module_code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $gradeRecord->assessment_component_name }}
                                            @if($gradeRecord->attempts > 1)
                                                <span class="text-xs text-gray-500">(Attempt {{ $gradeRecord->attempts }})</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($gradeRecord->grade !== null)
                                                <div class="text-sm font-medium {{ $gradeRecord->percentage >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $gradeRecord->grade }}
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $gradeRecord->percentage }}%</div>
                                            @else
                                                <span class="text-gray-400 text-sm">Not graded</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($gradeRecord->percentage !== null)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $gradeRecord->percentage >= 40 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $gradeRecord->percentage >= 40 ? 'Pass' : 'Fail' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($gradeRecord->graded_at)
                                                {{ $gradeRecord->graded_at->format('d M Y') }}
                                            @else
                                                Not graded
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $gradeRecord->is_visible_to_student ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $gradeRecord->is_visible_to_student ? 'Visible' : 'Hidden' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="text-gray-500 mb-4">
                            No grade records found for this student.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function exportProgressReport() {
            window.print();
        }
    </script>

    <style>
        @media print {
            .py-12, .space-y-6, .gap-6 {
                margin: 0 !important;
                padding: 0 !important;
                gap: 0 !important;
            }
            
            .shadow-sm, .shadow {
                box-shadow: none !important;
            }
            
            .bg-gray-50 {
                background-color: #f9fafb !important;
            }
        }
    </style>
</x-app-layout>