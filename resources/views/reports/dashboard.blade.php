{{-- resources/views/reports/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Analytics Dashboard
            </h2>
            <div class="flex space-x-2">
                <button onclick="refreshAllAnalytics()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Refresh Analytics
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Real-time Analytics Overview -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">System Overview</h3>
                <x-analytics-overview />
            </div>

            <!-- Legacy Statistics Grid (kept for comparison/fallback) -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Statistics</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Total Students</div>
                            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_students'] }}</div>
                            <div class="mt-2 text-sm text-gray-600">
                                <span class="text-green-600">{{ $stats['active_students'] }} active</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Deferred Students</div>
                            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['deferred_students'] }}</div>
                            <div class="mt-2 text-sm text-gray-600">
                                <span class="text-yellow-600">{{ $stats['pending_deferrals'] }} pending</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Active Programmes</div>
                            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_programmes'] }}</div>
                            <div class="mt-2 text-sm text-gray-600">
                                <span class="text-blue-600">{{ $stats['active_programme_instances'] }} active instances</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Module Instances</div>
                            <div class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ $stats['total_module_instances'] }}
                            </div>
                            <div class="mt-2 text-sm text-gray-600">
                                <span class="text-orange-600">{{ $stats['pending_deferrals'] }} pending deferrals</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Student Performance Trends -->
                <x-analytics-chart 
                    title="Student Performance Trends"
                    type="line"
                    api-url="/api/analytics/chart-data/student_performance"
                    :refresh-interval="900"
                    height="350" />

                <!-- Programme Effectiveness -->
                <x-analytics-chart 
                    title="Programme Effectiveness"
                    type="bar"
                    api-url="/api/analytics/chart-data/programme_effectiveness"
                    :refresh-interval="1800"
                    height="350" />

                <!-- Assessment Completion Rates -->
                <x-analytics-chart 
                    title="Assessment Completion Rates"
                    type="line"
                    api-url="/api/analytics/chart-data/assessment_completion"
                    :refresh-interval="600"
                    height="350" />

                <!-- Student Engagement -->
                <x-analytics-chart 
                    title="Student Engagement by Day"
                    type="doughnut"
                    api-url="/api/analytics/chart-data/student_engagement"
                    :refresh-interval="1800"
                    height="350" />
            </div>

            <!-- Programme Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Programme Enrolments</h3>
                    <div class="space-y-4">
                        @foreach($programmeStats as $programme)
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-medium">{{ $programme->code }} - {{ $programme->title }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ ucfirst(str_replace('_', ' ', $programme->enrolment_type)) }})</span>
                                </div>
                                <div class="flex items-center">
                                    @php
                                        $totalEnrolments = $programme->programmeInstances->sum('enrolments_count');
                                    @endphp
                                    <span class="text-2xl font-semibold text-gray-900 mr-2">{{ $totalEnrolments }}</span>
                                    <span class="text-sm text-gray-500">active students</span>
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div class="border-b"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Activities</h3>
                    <div class="space-y-3">
                        @foreach($recentActivities as $activity)
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $activity->causer?->name ?? 'System' }}</span>
                                        {{ $activity->description }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics JavaScript -->
    <script>
        async function refreshAllAnalytics() {
            // Show loading state
            const button = document.querySelector('button[onclick="refreshAllAnalytics()"]');
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Refreshing...';
            button.className = button.className.replace('bg-blue-600 hover:bg-blue-700', 'bg-gray-400 cursor-not-allowed');

            try {
                // First, clear the server-side cache
                const response = await fetch('/api/analytics/refresh-cache', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                    successMessage.textContent = 'Analytics cache cleared! Reloading...';
                    document.body.appendChild(successMessage);

                    // Wait a moment then reload the page to get fresh data
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error('Failed to refresh cache');
                }
            } catch (error) {
                console.error('Error refreshing analytics:', error);
                
                // Show error message
                const errorMessage = document.createElement('div');
                errorMessage.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                errorMessage.textContent = 'Failed to refresh cache. Trying component refresh...';
                document.body.appendChild(errorMessage);

                setTimeout(() => {
                    errorMessage.remove();
                }, 3000);

                // Fallback: try to refresh components without cache clear
                refreshAnalyticsComponents();
                
                // Reset button
                button.disabled = false;
                button.textContent = originalText;
                button.className = button.className.replace('bg-gray-400 cursor-not-allowed', 'bg-blue-600 hover:bg-blue-700');
            }
        }

        function refreshAnalyticsComponents() {
            // Refresh system overview
            const overviewComponent = document.querySelector('[x-data*="analyticsOverview"]');
            if (overviewComponent && overviewComponent._x_dataStack?.[0]?.loadData) {
                overviewComponent._x_dataStack[0].loadData();
            }

            // Refresh all charts
            const chartComponents = document.querySelectorAll('[x-data*="analyticsChart"]');
            chartComponents.forEach(component => {
                if (component._x_dataStack?.[0]?.refreshChart) {
                    component._x_dataStack[0].refreshChart();
                }
            });
        }

        // Auto-refresh every 10 minutes
        setInterval(() => {
            refreshAllAnalytics();
        }, 600000);
    </script>
</x-app-layout>