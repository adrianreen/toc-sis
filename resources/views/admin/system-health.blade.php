@php
    $overallHealth = 'excellent';
    $healthScore = 100;
    
    // Calculate overall health score based on warnings
    if (count($system_warnings) > 0) {
        $criticalWarnings = collect($system_warnings)->where('type', 'critical')->count();
        $securityWarnings = collect($system_warnings)->where('type', 'security')->count();
        $otherWarnings = count($system_warnings) - $criticalWarnings - $securityWarnings;
        
        $healthScore -= ($criticalWarnings * 30) + ($securityWarnings * 20) + ($otherWarnings * 10);
        
        if ($healthScore >= 90) $overallHealth = 'excellent';
        elseif ($healthScore >= 75) $overallHealth = 'good'; 
        elseif ($healthScore >= 60) $overallHealth = 'fair';
        else $overallHealth = 'poor';
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">System Health Dashboard</h1>
                <p class="text-gray-600 mt-1">Comprehensive monitoring of TOC-SIS system status</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm border">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full {{ $overallHealth === 'excellent' ? 'bg-green-500' : ($overallHealth === 'good' ? 'bg-blue-500' : ($overallHealth === 'fair' ? 'bg-yellow-500' : 'bg-red-500')) }}"></div>
                        <span class="text-sm font-medium">{{ ucfirst($overallHealth) }} ({{ $healthScore }}%)</span>
                    </div>
                </div>
                <button onclick="refreshDashboard()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                    Refresh
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Include Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- System Warnings (if any) -->
            @if(count($system_warnings) > 0)
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">System Warnings ({{ count($system_warnings) }})</h3>
                            <div class="mt-2 text-sm text-red-700">
                                @foreach($system_warnings as $warning)
                                    <div class="mb-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            {{ strtoupper($warning['type']) }}
                                        </span>
                                        {{ $warning['message'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Students -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600">Active Students</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $academic_health['students']['active'] }}</p>
                                <p class="text-xs text-gray-500">of {{ $academic_health['students']['total'] }} total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrollments -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="graduation-cap" class="w-5 h-5 text-green-600"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600">Active Enrollments</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $academic_health['enrollments']['active'] }}</p>
                                <p class="text-xs text-gray-500">of {{ $academic_health['enrollments']['total'] }} total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assessments -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="clipboard-check" class="w-5 h-5 text-purple-600"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600">Graded Assessments</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $academic_health['assessments']['graded'] }}</p>
                                <p class="text-xs text-gray-500">{{ $academic_health['assessments']['pending_grading'] }} pending</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Database Health -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="database" class="w-5 h-5 text-orange-600"></i>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-600">Database Status</p>
                                <p class="text-lg font-bold {{ $database_health['connection_status'] === 'Connected' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $database_health['connection_status'] }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $database_health['connection_time_ms'] }}ms response</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                
                <!-- Academic System Health -->
                <div class="bg-white shadow-sm rounded-lg border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Academic System Health</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            <!-- Students Health -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Student Health Score</span>
                                    <span class="text-sm text-gray-600">{{ $academic_health['students']['health_score'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $academic_health['students']['health_score'] }}%"></div>
                                </div>
                                <div class="mt-2 grid grid-cols-3 gap-2 text-xs text-gray-600">
                                    <div>Active: {{ $academic_health['students']['active'] }}</div>
                                    <div>Inactive: {{ $academic_health['students']['inactive'] }}</div>
                                    <div>Total: {{ $academic_health['students']['total'] }}</div>
                                </div>
                            </div>

                            <!-- Enrollments Breakdown -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Enrollment Distribution</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Programme Enrollments:</span>
                                        <span class="font-medium">{{ $academic_health['enrollments']['programme_enrollments'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Module Enrollments:</span>
                                        <span class="font-medium">{{ $academic_health['enrollments']['module_enrollments'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Active Enrollments:</span>
                                        <span class="font-medium text-green-600">{{ $academic_health['enrollments']['active'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Module System -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Module System</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Total Modules:</span>
                                        <span class="font-medium">{{ $academic_health['modules']['total'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Module Instances:</span>
                                        <span class="font-medium">{{ $academic_health['modules']['instances'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>With Assigned Tutors:</span>
                                        <span class="font-medium">{{ $academic_health['modules']['with_tutors'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Performance -->
                <div class="bg-white shadow-sm rounded-lg border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">System Performance</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $performance_metrics['average_response_time'] }}</div>
                                    <div class="text-xs text-gray-600">Avg Response Time</div>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ $performance_metrics['cache_hit_ratio'] }}</div>
                                    <div class="text-xs text-gray-600">Cache Hit Ratio</div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Database Performance</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Student Query Time:</span>
                                        <span class="font-medium">{{ $database_health['query_performance']['student_count_query_ms'] }}ms</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Grade Query Time:</span>
                                        <span class="font-medium">{{ $database_health['query_performance']['grade_count_query_ms'] }}ms</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Average Query Time:</span>
                                        <span class="font-medium">{{ $database_health['query_performance']['average_query_time_ms'] }}ms</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Memory Usage</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span>Current:</span>
                                        <span class="font-medium">{{ $system_overview['memory_usage']['current'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Peak:</span>
                                        <span class="font-medium">{{ $system_overview['memory_usage']['peak'] }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Efficiency:</span>
                                        <span class="font-medium">{{ $performance_metrics['memory_efficiency'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification & Communication Health -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                
                <!-- Notification System -->
                <div class="bg-white shadow-sm rounded-lg border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Notification System</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $notification_health['notifications']['read_rate'] }}%</div>
                                    <div class="text-xs text-gray-600">Read Rate</div>
                                </div>
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ $notification_health['emails']['delivery_rate'] }}</div>
                                    <div class="text-xs text-gray-600">Email Delivery</div>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Total Notifications:</span>
                                    <span class="font-medium">{{ $notification_health['notifications']['total'] }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Unread:</span>
                                    <span class="font-medium text-orange-600">{{ $notification_health['notifications']['unread'] }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Emails Sent Today:</span>
                                    <span class="font-medium">{{ $notification_health['emails']['sent_today'] }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Total Emails:</span>
                                    <span class="font-medium">{{ $notification_health['emails']['total_sent'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Status -->
                <div class="bg-white shadow-sm rounded-lg border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Security Status</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <div class="text-lg font-bold {{ $security_status['https_enabled'] ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $security_status['https_enabled'] ? 'ENABLED' : 'DISABLED' }}
                                    </div>
                                    <div class="text-xs text-gray-600">HTTPS</div>
                                </div>
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <div class="text-lg font-bold {{ $security_status['csrf_protection'] ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $security_status['csrf_protection'] ? 'ACTIVE' : 'INACTIVE' }}
                                    </div>
                                    <div class="text-xs text-gray-600">CSRF Protection</div>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Admin Accounts:</span>
                                    <span class="font-medium">{{ $security_status['admin_accounts'] }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Student Accounts:</span>
                                    <span class="font-medium">{{ $security_status['student_accounts'] }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Secure Cookies:</span>
                                    <span class="font-medium {{ $security_status['session_security']['secure_cookies'] ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $security_status['session_security']['secure_cookies'] ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>HTTP Only:</span>
                                    <span class="font-medium {{ $security_status['session_security']['http_only'] ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $security_status['session_security']['http_only'] ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white shadow-sm rounded-lg border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent System Activity</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- Recent Enrollments -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-4">Recent Enrollments</h4>
                            <div class="space-y-3">
                                @forelse($recent_activity['recent_enrollments'] as $enrollment)
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $enrollment->student->full_name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $enrollment->enrolment_type === 'programme' ? $enrollment->programmeInstance->programme->title : 'Module Enrollment' }}
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $enrollment->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No recent enrollments</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Recent Grade Records -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-4">Recent Grades</h4>
                            <div class="space-y-3">
                                @forelse($recent_activity['recent_grades'] as $grade)
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $grade->student->full_name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $grade->moduleInstance->module->title }} - {{ $grade->assessment_component_name }}
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{ $grade->graded_date ? $grade->graded_date->diffForHumans() : 'Not graded' }}
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No recent grades</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information Footer -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">System Environment</h4>
                        <div class="space-y-1 text-gray-600">
                            <div>Laravel: {{ $system_overview['laravel_version'] }}</div>
                            <div>PHP: {{ $system_overview['php_version'] }}</div>
                            <div>Environment: {{ ucfirst($system_overview['environment']) }}</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Database Information</h4>
                        <div class="space-y-1 text-gray-600">
                            <div>Tables: {{ $database_health['total_tables'] }}</div>
                            <div>Migrations: {{ $database_health['migrations_status']['total_migrations'] ?? 'N/A' }}</div>
                            <div>Cache: {{ $dashboard_metrics['cache_status'] }}</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Dashboard Metrics</h4>
                        <div class="space-y-1 text-gray-600">
                            <div>Generated: {{ $dashboard_metrics['generated_at']->format('H:i:s') }}</div>
                            <div>Load Time: {{ $dashboard_metrics['execution_time_ms'] }}ms</div>
                            <div>Disk Usage: {{ $system_overview['disk_usage']['percentage'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Auto-refresh functionality
        function refreshDashboard() {
            window.location.reload();
        }
        
        // Auto-refresh every 5 minutes
        setInterval(refreshDashboard, 300000);
        
        // Real-time updates (placeholder for future WebSocket implementation)
        function updateRealTimeMetrics() {
            fetch('{{ route("admin.system-health.api") }}')
                .then(response => response.json())
                .then(data => {
                    // Update real-time metrics here
                    console.log('Health data updated:', data);
                })
                .catch(error => console.error('Error fetching health data:', error));
        }
    </script>
</x-app-layout>