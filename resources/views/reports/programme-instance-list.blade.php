<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Programme Instance Students
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $programmeInstance->programme->title }} - {{ $programmeInstance->label }}
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('reports.dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                    Back to Reports
                </a>
                <button onclick="exportStudentList()" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    Export List
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Programme Instance Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Programme Instance Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Programme</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $programmeInstance->programme->title }}</p>
                            <p class="text-xs text-gray-500">{{ $programmeInstance->programme->code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Instance Label</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $programmeInstance->label }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Intake Period</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $programmeInstance->intake_start_date->format('d M Y') }} - 
                                {{ $programmeInstance->intake_end_date->format('d M Y') }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Delivery Style</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $programmeInstance->delivery_style === 'sync' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($programmeInstance->delivery_style) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
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
                                    <dd class="text-lg font-medium text-gray-900">{{ $students->count() }}</dd>
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Students</dt>
                                    <dd class="text-lg font-medium text-green-600">{{ $students->where('status', 'active')->count() }}</dd>
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Deferred Students</dt>
                                    <dd class="text-lg font-medium text-yellow-600">{{ $students->where('status', 'deferred')->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Completed Students</dt>
                                    <dd class="text-lg font-medium text-purple-600">{{ $students->where('status', 'completed')->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Enrolled Students</h3>
                    
                    @if($students->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student Number
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Enrolment Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($students as $student)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $student->full_name }}</div>
                                                @if($student->phone)
                                                    <div class="text-sm text-gray-500">{{ $student->phone }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $student->student_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $student->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $student->status === 'deferred' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $student->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ !in_array($student->status, ['active', 'deferred', 'completed']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                                    {{ ucfirst($student->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @php
                                                    $enrolment = $student->enrolments->where('programme_instance_id', $programmeInstance->id)->first();
                                                @endphp
                                                @if($enrolment)
                                                    {{ $enrolment->created_at->format('d M Y') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="mailto:{{ $student->email }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $student->email }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('students.show', $student) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">View</a>
                                                <a href="{{ route('students.progress', ['student' => $student->id]) }}" 
                                                   class="text-blue-600 hover:text-blue-900">Progress</a>
                                                <a href="{{ route('students.assessments', ['student' => $student->id]) }}" 
                                                   class="text-green-600 hover:text-green-900">Assessments</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-500 mb-4">
                                No students found for this programme instance.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportStudentList() {
            // Create a simple CSV export
            const students = @json($students);
            const programmeInstance = @json($programmeInstance);
            
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Programme,Instance,Student Name,Student Number,Status,Enrolment Date,Email,Phone\n";
            
            students.forEach(student => {
                const enrolment = student.enrolments.find(e => e.programme_instance_id === programmeInstance.id);
                const enrolmentDate = enrolment ? new Date(enrolment.created_at).toLocaleDateString() : 'N/A';
                
                csvContent += [
                    `"${programmeInstance.programme.title}"`,
                    `"${programmeInstance.label}"`,
                    `"${student.full_name}"`,
                    `"${student.student_number}"`,
                    `"${student.status}"`,
                    `"${enrolmentDate}"`,
                    `"${student.email}"`,
                    `"${student.phone || ''}"`
                ].join(",") + "\n";
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `${programmeInstance.label.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_students.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</x-app-layout>