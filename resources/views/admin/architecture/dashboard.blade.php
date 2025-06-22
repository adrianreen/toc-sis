{{-- resources/views/admin/architecture/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    System Health Dashboard
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Monitor data integrity, architecture health, and system performance
                </p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('admin.system-health.validation') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Detailed Validation
                </a>
                <button onclick="refreshStats()" 
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Refresh Stats
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Health Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($validation['valid'])
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Architecture Health</p>
                            <p class="text-2xl font-semibold {{ $validation['valid'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $validation['valid'] ? 'HEALTHY' : 'ISSUES' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Critical Errors</div>
                    <div class="mt-1 text-3xl font-semibold {{ count($validation['errors']) > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ count($validation['errors']) }}
                    </div>
                    @if(count($validation['errors']) > 0)
                        <p class="text-xs text-red-500 mt-1">Requires immediate attention</p>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Warnings</div>
                    <div class="mt-1 text-3xl font-semibold {{ count($validation['warnings']) > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                        {{ count($validation['warnings']) }}
                    </div>
                    @if(count($validation['warnings']) > 0)
                        <p class="text-xs text-yellow-600 mt-1">Recommended improvements</p>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Components</div>
                    <div class="mt-1 text-3xl font-semibold text-blue-600">
                        {{ $validation['stats']['programmes'] + $validation['stats']['programme_instances'] + $validation['stats']['modules'] + $validation['stats']['module_instances'] }}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Across all architecture levels</p>
                </div>
            </div>

            <!-- Architecture Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">4-Level Architecture Statistics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Level 1: Programmes -->
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $validation['stats']['programmes'] }}</div>
                            <div class="text-sm text-blue-800">Programmes</div>
                            <div class="text-xs text-gray-600">Static Blueprints</div>
                        </div>
                        
                        <!-- Level 2: Programme Instances -->
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $validation['stats']['programme_instances'] }}</div>
                            <div class="text-sm text-green-800">Programme Instances</div>
                            <div class="text-xs text-gray-600">Live Containers</div>
                        </div>
                        
                        <!-- Level 3: Modules -->
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ $validation['stats']['modules'] }}</div>
                            <div class="text-sm text-purple-800">Modules</div>
                            <div class="text-xs text-gray-600">Study Unit Blueprints</div>
                        </div>
                        
                        <!-- Level 4: Module Instances -->
                        <div class="text-center p-4 bg-orange-50 rounded-lg">
                            <div class="text-2xl font-bold text-orange-600">{{ $validation['stats']['module_instances'] }}</div>
                            <div class="text-sm text-orange-800">Module Instances</div>
                            <div class="text-xs text-gray-600">Live Classes</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolment Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Enrolment Overview</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Enrolments:</span>
                                <span class="font-semibold">{{ $validation['stats']['total_enrolments'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Enrolments:</span>
                                <span class="font-semibold text-green-600">{{ $validation['stats']['active_enrolments'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Programme Enrolments:</span>
                                <span class="font-semibold text-blue-600">{{ $validation['stats']['programme_enrolments'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Module Enrolments:</span>
                                <span class="font-semibold text-purple-600">{{ $validation['stats']['module_enrolments'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Assessment Data</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Grade Records:</span>
                                <span class="font-semibold">{{ $validation['stats']['grade_records'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Graded Records:</span>
                                <span class="font-semibold text-green-600">{{ $validation['stats']['graded_records'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Curriculum Links:</span>
                                <span class="font-semibold text-blue-600">{{ $validation['stats']['curriculum_links'] }}</span>
                            </div>
                            @php
                                $gradingRate = $validation['stats']['grade_records'] > 0 
                                    ? round(($validation['stats']['graded_records'] / $validation['stats']['grade_records']) * 100, 1)
                                    : 0;
                            @endphp
                            <div class="flex justify-between">
                                <span class="text-gray-600">Grading Progress:</span>
                                <span class="font-semibold">{{ $gradingRate }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Recent Programme Activity</h3>
                        @if($recentProgrammes->count() > 0)
                            <div class="space-y-2">
                                @foreach($recentProgrammes as $programme)
                                    <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                        <div>
                                            <a href="{{ route('programmes.show', $programme) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $programme->title }}
                                            </a>
                                            <p class="text-xs text-gray-500">NFQ Level {{ $programme->nfq_level }}</p>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $programme->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No programmes created yet.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Recent Instance Activity</h3>
                        @if($recentInstances->count() > 0)
                            <div class="space-y-2">
                                @foreach($recentInstances as $instance)
                                    <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                        <div>
                                            <a href="{{ route('programme-instances.show', $instance) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $instance->label }}
                                            </a>
                                            <p class="text-xs text-gray-500">{{ $instance->programme->title }}</p>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $instance->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No programme instances created yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            @if(!$validation['valid'] || count($validation['warnings']) > 0)
            <!-- Quick Actions -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Architecture Issues Detected</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>{{ count($validation['errors']) }} critical errors and {{ count($validation['warnings']) }} warnings found.</p>
                        </div>
                        <div class="mt-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.system-health.validation') }}" 
                                   class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-medium py-2 px-4 rounded">
                                    View Details
                                </a>
                                @if(count($validation['errors']) > 0)
                                <form method="POST" action="{{ route('admin.system-health.auto-fix') }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium py-2 px-4 rounded">
                                        Auto-Fix Issues
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        function refreshStats() {
            fetch('{{ route("admin.system-health.statistics") }}')
                .then(response => response.json())
                .then(data => {
                    location.reload(); // Simple refresh for now
                })
                .catch(error => {
                    console.error('Error refreshing stats:', error);
                });
        }
        
        // Auto-refresh every 5 minutes
        setInterval(refreshStats, 300000);
    </script>
</x-app-layout>