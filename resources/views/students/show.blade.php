{{-- resources/views/students/show.blade.php --}}
<x-app-layout>
<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Student Details: {{ $student->full_name }}
        </h2>
        <div class="space-x-2">
            <a href="{{ route('admin.student-progress', $student) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                📊 View Progress
            </a>
            <a href="{{ route('students.edit', $student) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit Student
            </a>
            <a href="{{ route('enrolments.create', $student) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Enrol in Programme
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

            <!-- Student Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Student Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Student Number</p>
                            <p class="font-medium">{{ $student->student_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($student->status === 'active') bg-green-100 text-green-800
                                @elseif($student->status === 'deferred') bg-yellow-100 text-yellow-800
                                @elseif($student->status === 'completed') bg-blue-100 text-blue-800
                                @elseif($student->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($student->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium">{{ $student->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="font-medium">{{ $student->phone ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date of Birth</p>
                            <p class="font-medium">{{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">County</p>
                            <p class="font-medium">{{ $student->county ?? '-' }}</p>
                        </div>
                    </div>
                    @if($student->address || $student->city || $student->eircode)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Address</p>
                            <p class="font-medium">
                                {{ $student->address }}{{ $student->address && $student->city ? ', ' : '' }}
                                {{ $student->city }}{{ $student->city && $student->eircode ? ', ' : '' }}
                                {{ $student->eircode }}
                            </p>
                        </div>
                    @endif
                    @if($student->notes)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Notes</p>
                            <p class="font-medium">{{ $student->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Enrolments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Programme Enrolments</h3>
                    @if($student->enrolments->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Programme
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cohort
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Enrolment Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Expected Completion
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
                                @foreach($student->enrolments as $enrolment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $enrolment->programme->code }} - {{ $enrolment->programme->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->cohort ? $enrolment->cohort->code : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->enrolment_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->expected_completion_date ? $enrolment->expected_completion_date->format('d M Y') : '-' }}
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
                                        {{-- START: MODIFIED SECTION --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($enrolment->status === 'active' && $enrolment->programme->isCohortBased())
                                                <a href="{{ route('deferrals.create', [$student, $enrolment]) }}"
                                                   class="text-yellow-600 hover:text-yellow-900 mr-2">
                                                    Defer
                                                </a>
                                            @elseif($enrolment->status === 'deferred')
                                                <span class="text-gray-500">Deferred</span>
                                            @endif
                                            {{-- You might want to add other actions here, e.g., a "View Details" link for the enrolment itself --}}
                                            {{-- or an "Edit Enrolment" link if applicable --}}
                                        </td>
                                        {{-- END: MODIFIED SECTION --}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No programme enrolments yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>