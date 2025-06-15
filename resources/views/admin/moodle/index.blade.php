<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Moodle Integration
            </h2>
            <div class="flex space-x-2">
                <button id="test-connection" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Test Connection
                </button>
                <form method="POST" action="{{ route('moodle.sync-all-courses') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                            onclick="return confirm('This will create courses in Moodle for all module instances that don\'t have one yet. Continue?')">
                        Sync All Courses
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Connection Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Connection Status</h3>
                    <div id="connection-status">
                        @if($connectionTest['success'])
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                <div class="flex">
                                    <div class="py-1">
                                        <svg class="fill-current h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M10 1l3 6 6 1-4.5 4.5L16 19l-6-3-6 3 1.5-6.5L1 8l6-1z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold">Connection Successful</p>
                                        <p class="text-sm">Site: {{ $connectionTest['site_name'] ?? 'Unknown' }}</p>
                                        <p class="text-sm">Version: {{ $connectionTest['moodle_version'] ?? 'Unknown' }}</p>
                                        <p class="text-sm">Users: {{ $connectionTest['user_count'] ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <div class="flex">
                                    <div class="py-1">
                                        <svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M10 1l3 6 6 1-4.5 4.5L16 19l-6-3-6 3 1.5-6.5L1 8l6-1z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold">Connection Failed</p>
                                        <p class="text-sm">{{ $connectionTest['error'] ?? 'Unknown error' }}</p>
                                        <p class="text-sm mt-2">Please check your Moodle configuration in the .env file:</p>
                                        <ul class="text-sm mt-1 ml-4 list-disc">
                                            <li>MOODLE_URL</li>
                                            <li>MOODLE_TOKEN</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Courses in Moodle</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['courses_with_moodle'] }} / {{ $stats['total_courses'] }}</dd>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Students in Moodle</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['students_with_moodle'] }} / {{ $stats['total_students'] }}</dd>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Sync Progress</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $stats['total_courses'] > 0 ? round(($stats['courses_with_moodle'] / $stats['total_courses']) * 100) : 0 }}%
                                    </dd>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Courses</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['total_courses'] - $stats['courses_with_moodle'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Instances List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Module Instances</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Course Code
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Module Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cohort
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Students
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Moodle Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $moduleInstances = \App\Models\ModuleInstance::with(['module', 'cohort', 'studentEnrolments.student'])->get();
                                @endphp
                                @foreach($moduleInstances as $moduleInstance)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $moduleInstance->instance_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $moduleInstance->module->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $moduleInstance->cohort->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $moduleInstance->studentEnrolments->count() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($moduleInstance->moodle_course_id)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Synced (ID: {{ $moduleInstance->moodle_course_id }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Not Synced
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if(!$moduleInstance->moodle_course_id)
                                                <form method="POST" action="{{ route('moodle.create-course', $moduleInstance) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900">Create Course</button>
                                                </form>
                                            @else
                                                <a href="{{ route('moodle.show-course', $moduleInstance) }}" class="text-green-600 hover:text-green-900">View Course</a>
                                                @if($moduleInstance->studentEnrolments->count() > 0)
                                                    <form method="POST" action="{{ route('moodle.bulk-enroll', $moduleInstance) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-purple-600 hover:text-purple-900"
                                                                onclick="return confirm('Enroll all {{ $moduleInstance->studentEnrolments->count() }} students in Moodle?')">
                                                            Bulk Enroll
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.getElementById('test-connection').addEventListener('click', function() {
            fetch('{{ route("moodle.test-connection") }}')
                .then(response => response.json())
                .then(data => {
                    const statusDiv = document.getElementById('connection-status');
                    if (data.success) {
                        statusDiv.innerHTML = `
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                <div class="flex">
                                    <div class="py-1">
                                        <svg class="fill-current h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M10 1l3 6 6 1-4.5 4.5L16 19l-6-3-6 3 1.5-6.5L1 8l6-1z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold">Connection Successful</p>
                                        <p class="text-sm">Site: ${data.data.site_name || 'Unknown'}</p>
                                        <p class="text-sm">Version: ${data.data.moodle_version || 'Unknown'}</p>
                                        <p class="text-sm">Users: ${data.data.user_count || 0}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        statusDiv.innerHTML = `
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <div class="flex">
                                    <div class="py-1">
                                        <svg class="fill-current h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M10 1l3 6 6 1-4.5 4.5L16 19l-6-3-6 3 1.5-6.5L1 8l6-1z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold">Connection Failed</p>
                                        <p class="text-sm">${data.message}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('connection-status').innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <p class="font-bold">Connection Error</p>
                            <p class="text-sm">Failed to test connection</p>
                        </div>
                    `;
                });
        });
    </script>
</x-app-layout>