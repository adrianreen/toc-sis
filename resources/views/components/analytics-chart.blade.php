{{-- resources/views/components/analytics-chart.blade.php --}}
@props([
    'chartId' => 'chart-' . uniqid(),
    'type' => 'line',
    'title' => '',
    'apiUrl' => '',
    'height' => '400',
    'options' => '{}',
    'refreshInterval' => null
])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" 
     x-data="analyticsChart({
         chartId: '{{ $chartId }}',
         type: '{{ $type }}',
         apiUrl: '{{ $apiUrl }}',
         options: {{ $options }},
         refreshInterval: {{ $refreshInterval ?? 'null' }}
     })"
     x-init="initChart()">
     
    @if($title)
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        </div>
    @endif
    
    <div class="p-6">
        <div class="relative">
            <!-- Loading indicator -->
            <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-10">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
            
            <!-- Error message -->
            <div x-show="error" x-text="error" class="text-red-600 text-center py-8"></div>
            
            <!-- Chart canvas -->
            <div class="relative" style="height: {{ $height }}px">
                <canvas :id="chartId" x-show="!error"></canvas>
            </div>
        </div>
        
        <!-- Chart controls -->
        <div class="mt-4 flex justify-between items-center text-sm text-gray-500">
            <div>
                <span x-show="lastUpdated" x-text="'Last updated: ' + lastUpdated"></span>
            </div>
            <div class="flex space-x-2">
                <button @click="refreshChart()" 
                        :disabled="loading"
                        class="px-3 py-1 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 disabled:opacity-50">
                    <span x-show="!loading">Refresh</span>
                    <span x-show="loading">...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function analyticsChart(config) {
    return {
        chartId: config.chartId,
        type: config.type,
        apiUrl: config.apiUrl,
        options: config.options || {},
        refreshInterval: config.refreshInterval,
        chart: null,
        loading: false,
        error: null,
        lastUpdated: null,
        intervalId: null,

        async initChart() {
            await this.loadChart();
            
            // Set up auto-refresh if specified
            if (this.refreshInterval) {
                this.intervalId = setInterval(() => {
                    this.refreshChart();
                }, this.refreshInterval * 1000);
            }
        },

        async loadChart() {
            if (!this.apiUrl) {
                this.error = 'No API URL provided';
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const response = await fetch(this.apiUrl);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }

                this.renderChart(data);
                this.lastUpdated = new Date().toLocaleString();
                
            } catch (err) {
                this.error = err.message || 'Failed to load chart data';
                console.error('Chart loading error:', err);
            } finally {
                this.loading = false;
            }
        },

        renderChart(data) {
            const canvas = document.getElementById(this.chartId);
            
            if (!canvas) {
                this.error = 'Chart canvas not found';
                return;
            }

            // Destroy existing chart if it exists
            if (this.chart) {
                this.chart.destroy();
            }

            // Merge default options with provided options
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                ...this.options,
                ...data.options
            };

            // Create new chart
            this.chart = new Chart(canvas.getContext('2d'), {
                type: data.type || this.type,
                data: data.data,
                options: chartOptions
            });
        },

        async refreshChart() {
            await this.loadChart();
        },

        destroy() {
            if (this.chart) {
                this.chart.destroy();
            }
            if (this.intervalId) {
                clearInterval(this.intervalId);
            }
        }
    }
}
</script>