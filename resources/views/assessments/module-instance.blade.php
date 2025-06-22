{{-- resources/views/assessments/module-instance.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Grading: {{ $moduleInstance->instance_code }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $moduleInstance->module->title }} - 
                    @if($moduleInstance->programmeInstances->count() > 0)
                        {{ $moduleInstance->programmeInstances->pluck('label')->implode(', ') }}
                    @else
                        Standalone Module
                    @endif
                </p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('assessments.export', $moduleInstance) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Export Grades
                </a>
                <a href="{{ route('assessments.index') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Back to Dashboard
                </a>
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

            <!-- Module Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Module</h3>
                            <p class="text-lg font-semibold">{{ $moduleInstance->module->code }}</p>
                            <p class="text-sm text-gray-600">{{ $moduleInstance->module->credits }} Credits</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Period</h3>
                            <p class="text-lg font-semibold">{{ $moduleInstance->start_date->format('M Y') }} - {{ $moduleInstance->end_date->format('M Y') }}</p>
                            <p class="text-sm text-gray-600">{{ $moduleInstance->start_date->diffInWeeks($moduleInstance->end_date) }} weeks</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Students</h3>
                            <p class="text-lg font-semibold">{{ $moduleInstance->enrolments->count() }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $moduleInstance->enrolments->where('status', 'active')->count() }} active
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Teacher</h3>
                            <p class="text-lg font-semibold">{{ $moduleInstance->teacher?->name ?? 'Not Assigned' }}</p>
                            <p class="text-sm text-gray-600">{{ ucfirst($moduleInstance->status) }}</p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Assessment Components Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Assessment Components Detail</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($moduleInstance->module->assessment_strategy ?? [] as $component)
                            @php
                                $totalStudents = $moduleInstance->enrolments->count();
                                $gradedCount = \App\Models\StudentGradeRecord::where('module_instance_id', $moduleInstance->id)
                                    ->where('assessment_component_name', $component['component_name'])
                                    ->whereNotNull('grade')
                                    ->whereNotNull('grade')
                                    ->count();
                                $submittedCount = \App\Models\StudentGradeRecord::where('module_instance_id', $moduleInstance->id)
                                    ->where('assessment_component_name', $component['component_name'])
                                    ->whereNotNull('grade')
                                    ->whereNull('grade')
                                    ->count();
                                $averageGrade = \App\Models\StudentGradeRecord::where('module_instance_id', $moduleInstance->id)
                                    ->where('assessment_component_name', $component['component_name'])
                                    ->whereNotNull('grade')
                                    ->avg('grade');
                            @endphp
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium">{{ $component['component_name'] ?? 'Unknown' }}</h4>
                                    <span class="text-sm font-semibold text-blue-600">{{ $component['weighting'] ?? 0 }}%</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">Assessment</p>
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Graded:</span>
                                        <span class="font-medium">{{ $gradedCount }}/{{ $totalStudents }}</span>
                                    </div>
                                    @if($submittedCount > 0)
                                        <div class="flex justify-between text-sm text-orange-600">
                                            <span>Awaiting Grade:</span>
                                            <span class="font-medium">{{ $submittedCount }}</span>
                                        </div>
                                    @endif
                                    @if($averageGrade)
                                        <div class="flex justify-between text-sm">
                                            <span>Average:</span>
                                            <span class="font-medium {{ $averageGrade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($averageGrade, 1) }}%
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mt-3 space-y-2">
                                    <a href="{{ route('assessments.bulk-grade-form', [$moduleInstance, $component]) }}" 
                                       class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm text-center block flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        Bulk Grade (Recommended)
                                    </a>
                                    @if($submittedCount > 0)
                                        <div class="text-xs text-center text-orange-600 font-medium">
                                            {{ $submittedCount }} awaiting grades
                                        </div>
                                    @endif
                                    
                                    <!-- Bulk Visibility Controls -->
                                    @if($gradedCount > 0)
                                        @php
                                            $componentName = $component['component_name'];
                                            $visibleCount = \App\Models\StudentGradeRecord::where('module_instance_id', $moduleInstance->id)
                                                ->where('assessment_component_name', $componentName)
                                                ->where('is_visible_to_student', true)
                                                ->count();
                                        @endphp
                                        <div class="pt-2 border-t border-gray-200">
                                            <div class="text-xs text-gray-600 mb-2 text-center">
                                                Visibility: {{ $visibleCount }}/{{ $gradedCount }} visible to students
                                            </div>
                                            <div class="grid grid-cols-2 gap-1">
                                                <button onclick="bulkVisibilityAction({{ $moduleInstance->id }}, '{{ $component['component_name'] }}', 'show_all')" 
                                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-xs transition-colors flex items-center justify-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                    Show All
                                                </button>
                                                <button onclick="bulkVisibilityAction({{ $moduleInstance->id }}, '{{ $component['component_name'] }}', 'hide_all')" 
                                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs transition-colors flex items-center justify-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                                    </svg>
                                                    Hide All
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Student List with Assessments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Student Assessments Overview</h3>
                        <div class="text-sm text-gray-600">
                            Individual grading links below are for <strong>detailed feedback</strong> - use <strong>Bulk Grading</strong> above for efficiency
                        </div>
                    </div>
                    
                    @if($moduleInstance->enrolments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50">
                                            Student
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        @foreach($moduleInstance->module->assessmentComponents as $component)
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-32">
                                                {{ $component->name }}
                                                <br><span class="text-xs font-normal">({{ $component->weight }}%)</span>
                                            </th>
                                        @endforeach
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Final Grade
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($moduleInstance->studentEnrolments->sortBy('student.student_number') as $enrolment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap sticky left-0 bg-white">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $enrolment->student->full_name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $enrolment->student->student_number }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($enrolment->status === 'active') bg-green-100 text-green-800
                                                    @elseif($enrolment->status === 'completed') bg-blue-100 text-blue-800
                                                    @elseif($enrolment->status === 'failed') bg-red-100 text-red-800
                                                    @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($enrolment->status) }}
                                                </span>
                                                @if($enrolment->attempt_number > 1)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Attempt {{ $enrolment->attempt_number }}
                                                    </div>
                                                @endif
                                            </td>
                                            @foreach($moduleInstance->module->assessmentComponents as $component)
                                                @php
                                                    $assessment = $enrolment->studentAssessments
                                                        ->where('assessment_component_id', $component->id)
                                                        ->where('attempt_number', $enrolment->attempt_number)
                                                        ->first();
                                                @endphp
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if($assessment)
                                                        @if($assessment->grade !== null)
                                                            <div class="text-sm font-medium {{ $assessment->grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                                {{ number_format($assessment->grade, 1) }}%
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ $assessment->status === 'passed' ? 'Passed' : ($assessment->status === 'failed' ? 'Failed' : ucfirst($assessment->status)) }}
                                                            </div>
                                                            <!-- Visibility Toggle Icon -->
                                                            <div class="mt-1">
                                                                <button onclick="toggleAssessmentVisibility({{ $assessment->id }}, {{ $assessment->isVisibleToStudent() ? 'false' : 'true' }}, this)"
                                                                        class="p-1 rounded transition-colors duration-200 cursor-pointer {{ $assessment->isVisibleToStudent() ? 'text-green-600 hover:bg-green-50' : 'text-red-600 hover:bg-red-50' }}"
                                                                        title="{{ $assessment->isVisibleToStudent() ? 'Hide from student' : 'Show to student' }}">
                                                                    <i data-lucide="{{ $assessment->isVisibleToStudent() ? 'eye' : 'eye-off' }}" class="w-3 h-3"></i>
                                                                </button>
                                                            </div>
                                                        @elseif($assessment->status === 'submitted')
                                                            <div class="text-sm font-medium text-orange-600">
                                                                Submitted
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ $assessment->submission_date?->format('d M') }}
                                                            </div>
                                                            <a href="{{ route('assessments.grade', $assessment) }}" 
                                                               class="text-xs bg-orange-500 hover:bg-orange-700 text-white px-2 py-1 rounded mt-1 inline-flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                                </svg>
                                                                Grade Now
                                                            </a>
                                                        @elseif($assessment->due_date->isPast())
                                                            <div class="text-sm font-medium text-red-600">
                                                                Overdue
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                Due {{ $assessment->due_date->format('d M') }}
                                                            </div>
                                                        @else
                                                            <div class="text-sm text-gray-500">
                                                                Pending
                                                            </div>
                                                            <div class="text-xs text-gray-500">
                                                                Due {{ $assessment->due_date->format('d M') }}
                                                            </div>
                                                        @endif
                                                        
                                                        @if($assessment->status !== 'submitted' || $assessment->grade !== null)
                                                            <a href="{{ route('assessments.grade', $assessment) }}" 
                                                               class="text-xs bg-gray-500 hover:bg-gray-700 text-white px-2 py-1 rounded mt-1 inline-flex items-center">
                                                                @if($assessment->grade !== null)
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                                    </svg>
                                                                    Edit Details
                                                                @else
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                                                    </svg>
                                                                    Individual Grade
                                                                @endif
                                                            </a>
                                                        @endif
                                                    @else
                                                        <span class="text-xs text-gray-400">No Assessment</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($enrolment->final_grade !== null)
                                                    <div class="text-lg font-semibold {{ $enrolment->final_grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ number_format($enrolment->final_grade, 1) }}%
                                                    </div>
                                                    <div class="text-xs {{ $enrolment->final_grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $enrolment->final_grade >= 40 ? 'PASS' : 'FAIL' }}
                                                    </div>
                                                    <!-- Final Grade Visibility Toggle -->
                                                    <div class="mt-1">
                                                        <button onclick="toggleFinalGradeVisibility({{ $enrolment->id }}, {{ $enrolment->is_final_grade_visible ?? 'true' ? 'false' : 'true' }}, this)"
                                                                class="p-1 rounded transition-colors duration-200 cursor-pointer {{ $enrolment->is_final_grade_visible ?? true ? 'text-green-600 hover:bg-green-50' : 'text-red-600 hover:bg-red-50' }}"
                                                                title="{{ $enrolment->is_final_grade_visible ?? true ? 'Hide final grade from student' : 'Show final grade to student' }}">
                                                            <i data-lucide="{{ $enrolment->is_final_grade_visible ?? true ? 'eye' : 'eye-off' }}" class="w-3 h-3"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex flex-col space-y-1">
                                                    <a href="{{ route('students.progress', $enrolment->student) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900 text-xs">
                                                        View Progress
                                                    </a>
                                                    <a href="{{ route('students.show', $enrolment->student) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900 text-xs">
                                                        Student Details
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-4-4 4 4 0 014 4z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No students enrolled</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                There are no students enrolled in this module instance yet.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Grade Legend -->
            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Grade Legend</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-100 border border-green-300 rounded mr-2"></div>
                        <span>Pass (≥40%)</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-100 border border-red-300 rounded mr-2"></div>
                        <span>Fail (<40%)</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-orange-100 border border-orange-300 rounded mr-2"></div>
                        <span>Awaiting Grade</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-gray-100 border border-gray-300 rounded mr-2"></div>
                        <span>Not Submitted</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Load Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- Visibility Toggle JavaScript -->
    <script>
        // Initialize Lucide Icons
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });

        // Toggle assessment visibility
        async function toggleAssessmentVisibility(assessmentId, newVisibility, buttonElement) {
            try {
                const formData = new FormData();
                formData.append('action', newVisibility ? 'show' : 'hide');
                formData.append('notes', 'Quick visibility toggle');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'PATCH');
                
                const response = await fetch(`/assessments/${assessmentId}/quick-visibility`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                if (response.ok) {
                    const isVisible = newVisibility;
                    
                    // Update button innerHTML with new icon
                    buttonElement.innerHTML = `<i data-lucide="${isVisible ? 'eye' : 'eye-off'}" class="w-3 h-3"></i>`;
                    
                    // Update button classes and title
                    buttonElement.className = `p-1 rounded transition-colors duration-200 cursor-pointer ${isVisible ? 'text-green-600 hover:bg-green-50' : 'text-red-600 hover:bg-red-50'}`;
                    buttonElement.title = isVisible ? 'Hide from student' : 'Show to student';
                    
                    // Update onclick for next toggle
                    buttonElement.setAttribute('onclick', `toggleAssessmentVisibility(${assessmentId}, ${!isVisible}, this)`);
                    
                    // Reinitialize Lucide icons to render the new icon
                    lucide.createIcons();
                } else {
                    console.error('Request failed with status:', response.status);
                    alert(`Failed to update visibility. Status: ${response.status}`);
                }
            } catch (error) {
                console.error('Toggle visibility error:', error);
                alert('Failed to update visibility. Please try again.');
            }
        }

        // Toggle final grade visibility
        async function toggleFinalGradeVisibility(enrolmentId, newVisibility, buttonElement) {
            try {
                const formData = new FormData();
                formData.append('action', newVisibility ? 'show' : 'hide');
                formData.append('notes', 'Quick final grade visibility toggle');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'PATCH');
                
                const response = await fetch(`/student-module-enrolments/${enrolmentId}/final-grade-visibility`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                if (response.ok) {
                    const isVisible = newVisibility;
                    
                    // Update button innerHTML with new icon
                    buttonElement.innerHTML = `<i data-lucide="${isVisible ? 'eye' : 'eye-off'}" class="w-3 h-3"></i>`;
                    
                    // Update button classes and title
                    buttonElement.className = `p-1 rounded transition-colors duration-200 cursor-pointer ${isVisible ? 'text-green-600 hover:bg-green-50' : 'text-red-600 hover:bg-red-50'}`;
                    buttonElement.title = isVisible ? 'Hide final grade from student' : 'Show final grade to student';
                    
                    // Update onclick for next toggle
                    buttonElement.setAttribute('onclick', `toggleFinalGradeVisibility(${enrolmentId}, ${!isVisible}, this)`);
                    
                    // Reinitialize Lucide icons to render the new icon
                    lucide.createIcons();
                } else {
                    alert('Failed to update final grade visibility. Please try again.');
                }
            } catch (error) {
                console.error('Toggle final grade visibility error:', error);
                alert('Failed to update final grade visibility. Please try again.');
            }
        }

        // Bulk visibility action
        async function bulkVisibilityAction(moduleInstanceId, assessmentComponentId, action) {
            const actionText = action === 'show_all' ? 'show all grades' : 'hide all grades';
            if (!confirm(`Are you sure you want to ${actionText} for this assessment? This will affect all graded students.`)) {
                return;
            }

            // Find the button that was clicked and add loading state
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `
                <svg class="w-3 h-3 mr-1 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
                Processing...
            `;

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                             document.querySelector('input[name="_token"]')?.value;
            
            if (!csrfToken) {
                alert('Security token not found. Please refresh the page and try again.');
                button.disabled = false;
                button.innerHTML = originalContent;
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', action);
                formData.append('notes', `Bulk visibility action: ${action}`);
                formData.append('_token', csrfToken);
                
                const response = await fetch(`/assessments/module-instances/${moduleInstanceId}/components/${assessmentComponentId}/bulk-visibility`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success || response.status === 200) {
                        // Success - show temporary feedback then reload
                        button.innerHTML = `
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            Success!
                        `;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        throw new Error(result.message || 'Failed to update visibility');
                    }
                } else {
                    let errorMessage = 'Failed to update visibility. Please try again.';
                    try {
                        const errorResult = await response.json();
                        errorMessage = errorResult.message || errorMessage;
                    } catch (e) {
                        errorMessage = `Error ${response.status}: ${response.statusText}`;
                    }
                    throw new Error(errorMessage);
                }
            } catch (error) {
                console.error('Bulk visibility action error:', error);
                console.error('Request URL:', `/assessments/module-instances/${moduleInstanceId}/components/${assessmentComponentId}/bulk-visibility`);
                console.error('Assessment Component ID:', assessmentComponentId);
                alert(error.message || 'Network error occurred. Please check your connection and try again.');
                
                // Restore button state
                button.disabled = false;
                button.innerHTML = originalContent;
            }
        }

    </script>
</x-app-layout>