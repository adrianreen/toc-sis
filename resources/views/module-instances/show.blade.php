{{-- resources/views/module-instances/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Module Instance: {{ $instance->instance_code }}
            </h2>
            <div class="space-x-2">
                @if(Auth::user()->role === 'manager')
                    <a href="{{ route('module-instances.edit', $instance) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Instance
                    </a>
                @endif
                <a href="{{ route('module-instances.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Instances
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

            <!-- Instance Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Instance Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Module</p>
                            <p class="font-medium">
                                <a href="{{ route('modules.show', $instance->module) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $instance->module->code }} - {{ $instance->module->title }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Cohort</p>
                            <p class="font-medium">
                                @if($instance->cohort)
                                    <a href="{{ route('cohorts.show', $instance->cohort) }}" class="text-indigo-600 hover:text-indigo-900">
                                        {{ $instance->cohort->code }} - {{ $instance->cohort->name }}
                                    </a>
                                @else
                                    Rolling Enrolment
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Teacher</p>
                            <p class="font-medium">
                                @if($instance->teacher)
                                    {{ $instance->teacher->name }}
                                    @if(Auth::user()->role === 'manager' && !$instance->teacher)
                                        <button class="ml-2 text-sm text-indigo-600 hover:text-indigo-900" onclick="showAssignTeacherModal()">
                                            Assign Teacher
                                        </button>
                                    @endif
                                @else
                                    <span class="text-gray-500">Not Assigned</span>
                                    @if(Auth::user()->role === 'manager')
                                        <button class="ml-2 text-sm text-indigo-600 hover:text-indigo-900" onclick="showAssignTeacherModal()">
                                            Assign Teacher
                                        </button>
                                    @endif
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($instance->status === 'active') bg-green-100 text-green-800
                                @elseif($instance->status === 'planned') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($instance->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Start Date</p>
                            <p class="font-medium">{{ $instance->start_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">End Date</p>
                            <p class="font-medium">{{ $instance->end_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Duration</p>
                            <p class="font-medium">{{ $instance->start_date->diffInWeeks($instance->end_date) }} weeks</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Students</p>
                            <p class="font-medium">{{ $instance->studentEnrolments->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Active Students</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $instance->studentEnrolments->where('status', 'active')->count() }}
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Completed</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $instance->studentEnrolments->where('status', 'completed')->count() }}
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Failed</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $instance->studentEnrolments->where('status', 'failed')->count() }}
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Average Grade</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            @php
                                $avgGrade = $instance->studentEnrolments
                                    ->where('final_grade', '>', 0)
                                    ->avg('final_grade');
                            @endphp
                            {{ $avgGrade ? number_format($avgGrade, 1) . '%' : '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolled Students -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Enrolled Students ({{ $instance->studentEnrolments->count() }})</h3>
                        @if(Auth::user()->role === 'teacher' && Auth::user()->id === $instance->teacher_id)
                            <div class="space-x-2">
                                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                    Grade Assessments
                                </button>
                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                    Export List
                                </button>
                            </div>
                        @endif
                    </div>
                    @if($instance->studentEnrolments->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Student Number
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Attempt
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Final Grade
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($instance->studentEnrolments as $enrolment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $enrolment->student->student_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <a href="{{ route('students.show', $enrolment->student) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                {{ $enrolment->student->full_name }}
                                            </a>
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
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->attempt_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($enrolment->final_grade)
                                                <span class="{{ $enrolment->final_grade >= 40 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                                    {{ number_format($enrolment->final_grade, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if(Auth::user()->role === 'teacher' && Auth::user()->id === $instance->teacher_id)
                                                <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                                    Grade
                                                </a>
                                            @endif
                                            <a href="{{ route('students.show', $enrolment->student) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No students enrolled in this module instance yet.</p>
                    @endif
                </div>
            </div>

            <!-- Assessment Components -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Assessment Components</h3>
                    @php
                        $components = \App\Models\AssessmentComponent::where('module_id', $instance->module_id)
                            ->where('is_active', true)
                            ->orderBy('sequence')
                            ->get();
                    @endphp
                    @if($components->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Component
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Weight
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Submissions
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Graded
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($components as $component)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $component->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ ucfirst($component->type) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $component->weight }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $submissions = \App\Models\StudentAssessment::whereHas('studentModuleEnrolment', function($q) use ($instance) {
                                                    $q->where('module_instance_id', $instance->id);
                                                })->where('assessment_component_id', $component->id)
                                                ->where('status', '!=', 'pending')
                                                ->count();
                                            @endphp
                                            {{ $submissions }} / {{ $instance->studentEnrolments->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $graded = \App\Models\StudentAssessment::whereHas('studentModuleEnrolment', function($q) use ($instance) {
                                                    $q->where('module_instance_id', $instance->id);
                                                })->where('assessment_component_id', $component->id)
                                                ->whereIn('status', ['graded', 'passed', 'failed'])
                                                ->count();
                                            @endphp
                                            {{ $graded }} / {{ $instance->studentEnrolments->count() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No assessment components defined for this module.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->role === 'manager')
    <!-- Assign Teacher Modal -->
    <div id="assignTeacherModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('module-instances.assign-teacher', $instance) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Assign Teacher</h3>
                        <div>
                            <label for="teacher_id" class="block text-sm font-medium text-gray-700">Select Teacher</label>
                            <select name="teacher_id" id="teacher_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Choose a teacher</option>
                                @php
                                    $teachers = \App\Models\User::where('role', 'teacher')->get();
                                @endphp
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Assign
                        </button>
                        <button type="button" onclick="closeAssignTeacherModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAssignTeacherModal() {
            document.getElementById('assignTeacherModal').classList.remove('hidden');
        }

        function closeAssignTeacherModal() {
            document.getElementById('assignTeacherModal').classList.add('hidden');
        }
    </script>
    @endif
</x-app-layout>