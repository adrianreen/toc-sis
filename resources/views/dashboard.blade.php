{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        @if(Auth::user()->role === 'student')
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">
                        Good {{ date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') }}, {{ Auth::user()->name }}
                    </h1>
                    <p class="mt-2 text-slate-600">
                        Welcome back to your Student Information System dashboard
                    </p>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    <div class="bg-white rounded-lg border border-slate-200 px-4 py-2 shadow-sm">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-slate-700">System Online</span>
                        </div>
                    </div>
                    <div class="text-sm text-slate-500">
                        {{ now()->format('l, F j, Y') }}
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-slate-900 mb-4">Student Search</h1>
                    <div x-data="studentSearch()" class="relative">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="search" class="h-5 w-5 text-slate-400"></i>
                            </div>
                            <input 
                                type="text" 
                                x-model="search"
                                placeholder="Search by name, student number, or email..."
                                class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-500 focus:outline-none focus:placeholder-slate-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                @input="searchStudents()"
                            />
                        </div>
                        
                        <!-- Search Results Dropdown -->
                        <div x-show="search.length > 0 && (filteredStudents.length > 0 || search.length >= 2)" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute z-10 mt-2 w-full bg-white shadow-lg max-h-64 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            
                            <template x-if="loading">
                                <div class="px-4 py-3 text-center text-slate-500">
                                    <i data-lucide="loader-circle" class="animate-spin h-4 w-4 inline-block mr-2"></i>
                                    Searching...
                                </div>
                            </template>
                            
                            <template x-if="!loading && filteredStudents.length === 0 && search.length >= 2">
                                <div class="px-4 py-3 text-center text-slate-500">
                                    <span>No students found matching "</span><span x-text="search"></span><span>"</span>
                                </div>
                            </template>
                            
                            <template x-for="student in filteredStudents.slice(0, 10)" :key="student.id">
                                <a :href="'/students/' + student.id" 
                                   class="flex items-center px-4 py-3 text-sm text-slate-900 cursor-pointer hover:bg-slate-50 border-b border-slate-100 last:border-b-0">
                                    <div>
                                        <div class="font-medium" x-text="student.full_name"></div>
                                        <div class="text-slate-500 text-xs">
                                            <span x-text="student.student_number"></span> • 
                                            <span x-text="student.email"></span> • 
                                            <span class="capitalize" x-text="student.status"></span>
                                        </div>
                                    </div>
                                </a>
                            </template>
                            
                            <template x-if="filteredStudents.length > 10">
                                <div class="px-4 py-2 text-xs text-slate-500 bg-slate-50 border-t">
                                    Showing first 10 of <span x-text="filteredStudents.length"></span> results. 
                                    <a href="/students" class="text-blue-600 hover:text-blue-700 font-medium">View all →</a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="text-sm text-slate-500">
                    {{ now()->format('l, F j, Y') }}
                </div>
            </div>
        @endif
    </x-slot>

    <!-- Add Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Simplified Quick Actions -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-slate-900">Quick Actions</h2>
                    <span class="text-sm text-slate-500 capitalize bg-slate-100 px-3 py-1 rounded-full">
                        {{ str_replace('_', ' ', Auth::user()->role) }} Access
                    </span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                        <!-- Add Student - Fixed darker background -->
                        <a href="{{ route('students.create') }}" class="group bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-6 text-white hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-lg text-white">Add Student</h3>
                                    <p class="text-blue-100 text-sm mt-1">Create new student record</p>
                                </div>
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i data-lucide="user-plus" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </a>

                        <!-- Recycle Bin -->
                        <a href="{{ route('students.recycle-bin') }}" class="group bg-gradient-to-br from-red-600 to-red-700 rounded-xl p-6 text-white hover:from-red-700 hover:to-red-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-lg text-white">Recycle Bin</h3>
                                    <p class="text-red-100 text-sm mt-1">Restore deleted students</p>
                                </div>
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i data-lucide="trash-2" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if(in_array(Auth::user()->role, ['manager', 'teacher', 'student_services']))
                        <!-- View Results/Assessments -->
                        <a href="{{ route('assessments.index') }}" class="group bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl p-6 text-white hover:from-emerald-700 hover:to-emerald-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-lg text-white">View Results</h3>
                                    <p class="text-emerald-100 text-sm mt-1">Assessment & grading</p>
                                </div>
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i data-lucide="clipboard-check" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if(Auth::user()->role === 'manager')
                        <!-- View Reports -->
                        <a href="{{ route('reports.dashboard') }}" class="group bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl p-6 text-white hover:from-purple-700 hover:to-purple-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-lg text-white">View Reports</h3>
                                    <p class="text-purple-100 text-sm mt-1">System analytics</p>
                                </div>
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i data-lucide="bar-chart-3" class="w-6 h-6 text-white"></i>
                                </div>
                            </div>
                        </a>
                    @endif

                    <!-- Common action for all roles -->
                    <a href="{{ route('students.index') }}" class="group bg-gradient-to-br from-slate-600 to-slate-700 rounded-xl p-6 text-white hover:from-slate-700 hover:to-slate-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">View Students</h3>
                                <p class="text-slate-100 text-sm mt-1">Browse all records</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="users" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
                <!-- Recent Activity -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900">Recent Activity</h3>
                            <a href="{{ route('reports.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                View all →
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        @php
                            $recentActivities = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
                                ->latest()
                                ->limit(5)
                                ->get();
                        @endphp
                        
                        @if($recentActivities->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentActivities as $activity)
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center">
                                                <i data-lucide="activity" class="w-4 h-4 text-slate-500"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-slate-900">
                                                <span class="font-medium">{{ $activity->causer?->name ?? 'System' }}</span>
                                                {{ $activity->description }}
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i data-lucide="activity" class="w-12 h-12 text-slate-400 mx-auto mb-4"></i>
                                <p class="text-slate-500">No recent activity</p>
                            </div>
                        @endif
                    </div>
                </div>
    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });

        // Student search functionality for dashboard
        function studentSearch() {
            return {
                search: '',
                filteredStudents: [],
                loading: false,
                debounceTimer: null,

                searchStudents() {
                    // Clear previous timer
                    if (this.debounceTimer) {
                        clearTimeout(this.debounceTimer);
                    }

                    // Don't search if less than 2 characters
                    if (this.search.length < 2) {
                        this.filteredStudents = [];
                        return;
                    }

                    // Debounce the search
                    this.debounceTimer = setTimeout(() => {
                        this.performSearch();
                    }, 300);
                },

                async performSearch() {
                    this.loading = true;
                    
                    try {
                        const response = await fetch(`/api/students/search?q=${encodeURIComponent(this.search)}`);
                        if (response.ok) {
                            const data = await response.json();
                            this.filteredStudents = data.students || [];
                        } else {
                            console.error('Search failed:', response.statusText);
                            this.filteredStudents = [];
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        this.filteredStudents = [];
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>