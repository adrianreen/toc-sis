{{-- resources/views/cohorts/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Cohorts
            </h2>
            @if(Auth::user()->role === 'manager')
                <a href="{{ route('cohorts.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Cohort
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12" x-data="cohortIndex()">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Live Search and Filter Section -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Enhanced Search Input -->
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search Cohorts</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="search"
                                    x-model="search"
                                    placeholder="Search by cohort name, code, or programme..."
                                    class="block w-full pl-11 pr-10 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <button 
                                    x-show="search.length > 0"
                                    @click="search = ''"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center hover:bg-gray-50 rounded-r-lg transition-colors duration-200"
                                >
                                    <svg class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Enhanced Dropdowns -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select 
                                id="statusFilter"
                                x-model="statusFilter"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent py-3 transition-all duration-200"
                            >
                                <option value="">All Statuses</option>
                                <option value="planned">📋 Planned</option>
                                <option value="active">✅ Active</option>
                                <option value="completed">🎓 Completed</option>
                            </select>
                        </div>

                        <div>
                            <label for="programmeFilter" class="block text-sm font-semibold text-gray-700 mb-2">Programme</label>
                            <select 
                                id="programmeFilter"
                                x-model="programmeFilter"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent py-3 transition-all duration-200"
                            >
                                <option value="">All Programmes</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}">{{ $programme->code }} - {{ $programme->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Enhanced Results Summary -->
                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="text-sm font-medium text-gray-900">
                                <span x-text="filteredCohorts.length"></span> of <span x-text="allCohorts.length"></span> cohorts
                            </div>
                            <span x-show="search || statusFilter || programmeFilter" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Filtered
                            </span>
                        </div>
                        <button 
                            x-show="search || statusFilter || programmeFilter"
                            @click="clearFilters()"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-gray-600 hover:text-gray-700 hover:bg-gray-50 transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cohorts Table -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span>Cohort</span>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Programme
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Start Date
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    End Date
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Students
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="relative px-6 py-4 w-32">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <template x-for="cohort in filteredCohorts" :key="cohort.id">
                                <tr class="hover:bg-blue-50 hover:shadow-sm transition-all duration-200 group cursor-pointer" 
                                    @click="window.location.href = `/cohorts/${cohort.id}`"
                                    title="Click to view cohort details">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <!-- Cohort Icon -->
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm"
                                                     x-text="cohort.code">
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="cohort.code"></div>
                                                <div class="text-sm text-gray-500" x-text="cohort.full_name"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span x-text="cohort.programme.code + ' - ' + cohort.programme.title"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span x-text="cohort.start_date"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span x-text="cohort.end_date || '-'"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span x-text="cohort.students_count"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ring-1 ring-inset"
                                            :class="{
                                                'bg-green-50 text-green-700 ring-green-600/20': cohort.status === 'active',
                                                'bg-yellow-50 text-yellow-700 ring-yellow-600/20': cohort.status === 'planned',
                                                'bg-gray-50 text-gray-700 ring-gray-600/20': cohort.status === 'completed'
                                            }"
                                        >
                                            <!-- Status dot -->
                                            <span 
                                                class="w-1.5 h-1.5 rounded-full mr-2"
                                                :class="{
                                                    'bg-green-500': cohort.status === 'active',
                                                    'bg-yellow-500': cohort.status === 'planned',
                                                    'bg-gray-500': cohort.status === 'completed'
                                                }"
                                            ></span>
                                            <span x-text="cohort.status.charAt(0).toUpperCase() + cohort.status.slice(1)"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                                        <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <a :href="`/cohorts/${cohort.id}`" 
                                               class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                                                View
                                            </a>
                                            @if(Auth::user()->role === 'manager')
                                                <a :href="`/cohorts/${cohort.id}/edit`" 
                                                   class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-200">
                                                    Edit
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- Enhanced Empty State -->
                            <tr x-show="filteredCohorts.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-medium text-gray-900 mb-1">No cohorts found</h3>
                                        <p class="text-sm text-gray-500" x-show="search || statusFilter || programmeFilter">
                                            Try adjusting your search criteria or clearing filters.
                                        </p>
                                        <p class="text-sm text-gray-500" x-show="!search && !statusFilter && !programmeFilter">
                                            Get started by adding your first cohort.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Loading indicator for future AJAX implementation -->
            <div x-show="loading" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
                <div class="bg-white p-4 rounded-lg">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    <p class="mt-2 text-sm text-gray-600">Loading cohorts...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cohortIndex() {
            return {
                // All cohorts data - loaded once from server
                allCohorts: @json($allCohorts ?? []),
                
                // Filter states
                search: '',
                statusFilter: '',
                programmeFilter: '',
                
                // Loading state for future AJAX implementation
                loading: false,
                
                // Debug initialization
                init() {
                    console.log('Alpine.js cohortIndex initialized');
                    console.log('Cohorts data:', this.allCohorts);
                    console.log('Total cohorts:', this.allCohorts.length);
                },
                
                // Computed filtered cohorts
                get filteredCohorts() {
                    console.log('Filtering cohorts...', {
                        search: this.search,
                        statusFilter: this.statusFilter,
                        programmeFilter: this.programmeFilter,
                        totalCohorts: this.allCohorts.length
                    });
                    
                    let filtered = [...this.allCohorts]; // Create a copy
                    
                    // Search filter (name, code, programme)
                    if (this.search && this.search.trim()) {
                        const searchTerm = this.search.toLowerCase().trim();
                        console.log('Applying search filter:', searchTerm);
                        filtered = filtered.filter(cohort => {
                            const matches = cohort.name.toLowerCase().includes(searchTerm) ||
                                cohort.code.toLowerCase().includes(searchTerm) ||
                                cohort.full_name.toLowerCase().includes(searchTerm) ||
                                cohort.programme.title.toLowerCase().includes(searchTerm) ||
                                cohort.programme.code.toLowerCase().includes(searchTerm);
                            return matches;
                        });
                        console.log('After search filter:', filtered.length);
                    }
                    
                    // Status filter
                    if (this.statusFilter) {
                        console.log('Applying status filter:', this.statusFilter);
                        filtered = filtered.filter(cohort => cohort.status === this.statusFilter);
                        console.log('After status filter:', filtered.length);
                    }
                    
                    // Programme filter
                    if (this.programmeFilter) {
                        console.log('Applying programme filter:', this.programmeFilter);
                        filtered = filtered.filter(cohort => 
                            cohort.programme.id.toString() === this.programmeFilter.toString()
                        );
                        console.log('After programme filter:', filtered.length);
                    }
                    
                    console.log('Final filtered results:', filtered.length);
                    return filtered;
                },
                
                // Clear all filters
                clearFilters() {
                    console.log('Clearing all filters');
                    this.search = '';
                    this.statusFilter = '';
                    this.programmeFilter = '';
                }
                
                // Note: If search performance becomes slow with large datasets (1000+ cohorts),
                // we should switch to server-side AJAX filtering using debounced requests
            }
        }
        
        // Debug: Check if Alpine.js is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            console.log('Alpine available:', typeof Alpine !== 'undefined');
        });
    </script>
</x-app-layout>