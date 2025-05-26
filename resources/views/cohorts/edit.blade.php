{{-- resources/views/cohorts/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Cohort: {{ $cohort->code }} - {{ $cohort->name }}
            </h2>
            <div class="space-x-2">
                @if(Auth::user()->role === 'manager')
                    <a href="{{ route('cohorts.edit', $cohort) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Cohort
                    </a>
                @endif
                <a href="{{ route('cohorts.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Cohorts
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

            <!-- Cohort Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Cohort Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Programme</p>
                            <p class="font-medium">
                                <a href="{{ route('programmes.show', $cohort->programme) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $cohort->programme->code }} - {{ $cohort->programme->title }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($cohort->status === 'active') bg-green-100 text-green-800
                                @elseif($cohort->status === 'planned') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($cohort->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Start Date</p>
                            <p class="font-medium">{{ $cohort->start_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">End Date</p>
                            <p class="font-medium">{{ $cohort->end_date ? $cohort->end_date->format('d M Y') : 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Duration</p>
                            <p class="font-medium">
                                @if($cohort->end_date)
                                    {{ $cohort->start_date->diffInMonths($cohort->end_date) }} months
                                @else
                                    Not determined
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Students</p>
                            <p class="font-medium">{{ $cohort->enrolments->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Enrolled Students ({{ $cohort->enrolments->count() }})</h3>
                        <div class="flex space-x-2">
                            @if(Auth::user()->role === 'manager' || Auth::user()->role === 'student_services')
                                <a href="{{ route('reports.cohort-list', $cohort) }}" 
                                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded text-sm">
                                    Export List
                                </a>
                            @endif
                        </div>
                    </div>
                    @if($cohort->enrolments->count() > 0)
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
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Enrolment Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($cohort->enrolments as $enrolment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $enrolment->student->student_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->student->full_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->student->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->enrolment_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($enrolment->status === 'active') bg-green-100 text-green-800
                                                @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800
                                                @elseif($enrolment->status === 'completed') bg-blue-100 text-blue-800
                                                @elseif($enrolment->status === 'withdrawn') bg-orange-100 text-orange-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($enrolment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('students.show', $enrolment->student) }}" 
                                               class="text-indigo-600 hover:text-indigo-900 mr-2">
                                                View
                                            </a>
                                            @if($enrolment->status === 'active')
                                                <a href="{{ route('deferrals.create', [$enrolment->student, $enrolment]) }}"
                                                   class="text-yellow-600 hover:text-yellow-900">
                                                    Defer
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No students enrolled in this cohort yet.</p>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Active Students</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $cohort->enrolments->where('status', 'active')->count() }}
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Deferred</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $cohort->enrolments->where('status', 'deferred')->count() }}
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Completed</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $cohort->enrolments->where('status', 'completed')->count() }}
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Withdrawn</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ $cohort->enrolments->where('status', 'withdrawn')->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Instances -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Module Instances</h3>
                        @if(Auth::user()->role === 'manager')
                            <a href="{{ route('module-instances.create') }}?cohort_id={{ $cohort->id }}" 
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Add Module Instance
                            </a>
                        @endif
                    </div>
                    @php
                        $moduleInstances = \App\Models\ModuleInstance::where('cohort_id', $cohort->id)
                            ->with(['module', 'teacher'])
                            ->get();
                    @endphp
                    @if($moduleInstances->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Instance Code
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Module
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Teacher
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Start Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($moduleInstances as $instance)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $instance->instance_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $instance->module->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $instance->teacher ? $instance->teacher->name : 'Not assigned' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $instance->start_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($instance->status === 'active') bg-green-100 text-green-800
                                                @elseif($instance->status === 'planned') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($instance->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No module instances created for this cohort yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>