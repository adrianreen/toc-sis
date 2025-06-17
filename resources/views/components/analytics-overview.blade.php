{{-- resources/views/components/analytics-overview.blade.php --}}
@props([
    'apiUrl' => '/api/analytics/system-overview',
    'refreshInterval' => 300 // 5 minutes
])

<div x-data="analyticsOverview('{{ $apiUrl }}', {{ $refreshInterval }})" 
     x-init="loadData()"
     class="space-y-6">
     
    <!-- Loading indicator -->
    <div x-show="loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
    
    <!-- Error message -->
    <div x-show="error" x-text="error" class="text-red-600 text-center py-8 bg-red-50 rounded-lg"></div>
    
    <!-- Metrics Grid -->
    <div x-show="data && !loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Students Overview -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500">Total Students</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900" x-text="data?.students?.total || 0"></div>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        <span x-text="data?.students?.active || 0"></span>
                        <span class="text-gray-500 ml-1">active</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        <span x-text="data?.students?.enrolled || 0"></span>
                        <span class="text-gray-500 ml-1">enrolled</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                        <span x-text="data?.students?.deferred || 0"></span>
                        <span class="text-gray-500 ml-1">deferred</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programmes Overview -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500">Programmes</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900" x-text="data?.programmes?.total || 0"></div>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-full">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        <span x-text="data?.programmes?.active || 0"></span>
                        <span class="text-gray-500 ml-1">active programmes</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessments Overview -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500">Assessments</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900" x-text="data?.assessments?.total || 0"></div>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-full">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                        <span x-text="data?.assessments?.pending || 0"></span>
                        <span class="text-gray-500 ml-1">pending</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        <span x-text="data?.assessments?.submitted || 0"></span>
                        <span class="text-gray-500 ml-1">submitted</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        <span x-text="data?.assessments?.graded || 0"></span>
                        <span class="text-gray-500 ml-1">graded</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollments Overview -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500">Enrollments</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900" x-text="data?.enrollments?.total || 0"></div>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-full">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                        <span x-text="data?.enrollments?.active || 0"></span>
                        <span class="text-gray-500 ml-1">active</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                        <span x-text="data?.enrollments?.completed || 0"></span>
                        <span class="text-gray-500 ml-1">completed</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                        <span x-text="data?.enrollments?.deferred || 0"></span>
                        <span class="text-gray-500 ml-1">deferred</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Last Updated -->
    <div x-show="lastUpdated && !loading" class="text-center text-sm text-gray-500">
        <span x-text="'Last updated: ' + lastUpdated"></span>
        <button @click="loadData()" class="ml-2 text-blue-600 hover:text-blue-800">
            Refresh
        </button>
    </div>
</div>

<script>
function analyticsOverview(apiUrl, refreshInterval) {
    return {
        apiUrl: apiUrl,
        refreshInterval: refreshInterval,
        data: null,
        loading: false,
        error: null,
        lastUpdated: null,
        intervalId: null,

        async loadData() {
            this.loading = true;
            this.error = null;

            try {
                const response = await fetch(this.apiUrl);
                
                if (!response.ok) {
                    throw new Error(`Failed to load data: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }

                this.data = result;
                this.lastUpdated = new Date().toLocaleString();
                
                // Set up auto-refresh
                if (this.refreshInterval && !this.intervalId) {
                    this.intervalId = setInterval(() => {
                        this.loadData();
                    }, this.refreshInterval * 1000);
                }
                
            } catch (err) {
                this.error = err.message || 'Failed to load analytics data';
                console.error('Analytics overview error:', err);
            } finally {
                this.loading = false;
            }
        },

        destroy() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
            }
        }
    }
}
</script>