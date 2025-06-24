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


    <div class="py-8 min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Clean Quick Actions -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-slate-900">Quick Actions</h2>
                    <span class="text-sm text-slate-500 capitalize bg-slate-100 px-3 py-1 rounded-full">
                        {{ str_replace('_', ' ', Auth::user()->role) }} Access
                    </span>
                </div>
                
                <!-- Primary Actions - Most Important -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Students - Primary for all staff -->
                    <a href="{{ route('students.index') }}" class="group bg-white border-2 border-slate-200 rounded-xl p-6 hover:border-toc-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-15 h-15 bg-gradient-to-br from-toc-100 to-toc-200 rounded-xl flex items-center justify-center group-hover:from-toc-200 group-hover:to-toc-300 transition-all duration-200 shadow-sm">
                                <i data-lucide="users" class="w-7 h-7 text-toc-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 text-lg">Students</h3>
                                <p class="text-slate-500 text-sm">View & manage records</p>
                            </div>
                        </div>
                    </a>

                    @if(in_array(Auth::user()->role, ['manager', 'teacher', 'student_services']))
                        <!-- Assessments - Primary for teaching staff -->
                        <a href="{{ route('assessments.index') }}" class="group bg-white border-2 border-slate-200 rounded-xl p-6 hover:border-blue-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-15 h-15 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center group-hover:from-blue-200 group-hover:to-blue-300 transition-all duration-200 shadow-sm">
                                    <i data-lucide="clipboard-check" class="w-7 h-7 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-lg">Assessments</h3>
                                    <p class="text-slate-500 text-sm">Grading & results</p>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                        <!-- Add Student - Important for admin staff -->
                        <a href="{{ route('students.create') }}" class="group bg-white border-2 border-slate-200 rounded-xl p-6 hover:border-green-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-15 h-15 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center group-hover:from-green-200 group-hover:to-green-300 transition-all duration-200 shadow-sm">
                                    <i data-lucide="user-plus" class="w-7 h-7 text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-lg">Add Student</h3>
                                    <p class="text-slate-500 text-sm">Create new record</p>
                                </div>
                            </div>
                        </a>
                    @endif
                </div>

                <!-- Secondary Actions - Common Tasks -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                        <a href="{{ route('enquiries.index') }}" class="group bg-white border border-slate-200 rounded-lg p-4 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="text-center">
                                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-amber-100 transition-colors">
                                    <i data-lucide="mail" class="w-5 h-5 text-amber-600"></i>
                                </div>
                                <h3 class="font-medium text-slate-900 text-sm">Enquiries</h3>
                                <p class="text-slate-500 text-xs">Manage leads</p>
                            </div>
                        </a>
                    @endif

                    @if(in_array(Auth::user()->role, ['manager', 'teacher', 'student_services']))
                        <a href="{{ route('extension-requests.staff-index') }}" class="group bg-white border border-slate-200 rounded-lg p-4 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="text-center">
                                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-orange-100 transition-colors">
                                    <i data-lucide="clock" class="w-5 h-5 text-orange-600"></i>
                                </div>
                                <h3 class="font-medium text-slate-900 text-sm">Extensions</h3>
                                <p class="text-slate-500 text-xs">Review requests</p>
                            </div>
                        </a>
                    @endif

                    @if(Auth::user()->role === 'manager')
                        <a href="{{ route('reports.dashboard') }}" class="group bg-white border border-slate-200 rounded-lg p-4 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="text-center">
                                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-purple-100 transition-colors">
                                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-purple-600"></i>
                                </div>
                                <h3 class="font-medium text-slate-900 text-sm">Reports</h3>
                                <p class="text-slate-500 text-xs">Analytics</p>
                            </div>
                        </a>

                        <a href="{{ route('notifications.admin') }}" class="group bg-white border border-slate-200 rounded-lg p-4 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="text-center">
                                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-indigo-100 transition-colors">
                                    <i data-lucide="bell" class="w-5 h-5 text-indigo-600"></i>
                                </div>
                                <h3 class="font-medium text-slate-900 text-sm">Notifications</h3>
                                <p class="text-slate-500 text-xs">Admin panel</p>
                            </div>
                        </a>
                    @endif
                </div>
                
                <!-- Administrative Tools - Collapsed by default -->
                @if(Auth::user()->role === 'manager')
                    <div class="mt-6 pt-6 border-t border-slate-200" x-data="{ showAdmin: false }">
                        <button @click="showAdmin = !showAdmin" class="flex items-center text-sm font-medium text-slate-600 hover:text-slate-700 mb-3 transition-colors cursor-pointer">
                            <i data-lucide="chevron-right" class="w-4 h-4 mr-1 transform transition-transform duration-200" :class="showAdmin ? 'rotate-90' : ''"></i>
                            System Administration
                        </button>
                        <div x-show="showAdmin" x-transition class="flex flex-wrap gap-2">
                            <a href="{{ route('programmes.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer">
                                <i data-lucide="book" class="w-3 h-3 mr-1.5"></i>
                                Programmes
                            </a>
                            <a href="{{ route('modules.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer">
                                <i data-lucide="layers" class="w-3 h-3 mr-1.5"></i>
                                Modules
                            </a>
                            <a href="{{ route('programme-instances.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer">
                                <i data-lucide="users-2" class="w-3 h-3 mr-1.5"></i>
                                Programme Instances
                            </a>
                            <a href="{{ route('module-instances.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer">
                                <i data-lucide="calendar" class="w-3 h-3 mr-1.5"></i>
                                Module Instances
                            </a>
                            <a href="{{ route('students.recycle-bin') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer">
                                <i data-lucide="trash-2" class="w-3 h-3 mr-1.5"></i>
                                Recycle Bin
                            </a>
                        </div>
                    </div>
                @elseif(in_array(Auth::user()->role, ['student_services']))
                    <div class="mt-6 pt-6 border-t border-slate-200">
                        <h3 class="text-sm font-medium text-slate-600 mb-3">Administrative Tools</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('students.recycle-bin') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors cursor-pointer">
                                <i data-lucide="trash-2" class="w-3 h-3 mr-1.5"></i>
                                Recycle Bin
                            </a>
                        </div>
                    </div>
                @endif
            </div>
                <!-- Recent Activity -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 card-enhanced">
                    <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-slate-25 to-slate-50 rounded-t-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-toc-100 to-toc-200 rounded-lg flex items-center justify-center">
                                    <i data-lucide="activity" class="w-4 h-4 text-toc-600"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-900">Recent Activity</h3>
                            </div>
                            <a href="{{ route('reports.dashboard') }}" class="text-sm text-toc-600 hover:text-toc-700 font-medium transition-colors">
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
                            <div class="space-y-3">
                                @foreach($recentActivities as $activity)
                                    <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-slate-50 transition-colors">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-gradient-to-br from-slate-100 to-slate-200 rounded-full flex items-center justify-center shadow-sm">
                                                <i data-lucide="activity" class="w-4 h-4 text-slate-600"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-slate-900 leading-relaxed">
                                                <span class="font-semibold text-toc-700">{{ $activity->causer?->name ?? 'System' }}</span>
                                                {{ $activity->description }}
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1.5 flex items-center">
                                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                                                {{ $activity->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                    <i data-lucide="activity" class="w-8 h-8 text-slate-400"></i>
                                </div>
                                <h4 class="text-sm font-medium text-slate-900 mb-1">No recent activity</h4>
                                <p class="text-sm text-slate-500">Activity will appear here as users interact with the system</p>
                            </div>
                        @endif
                    </div>
                </div>
    <!-- Student search functionality for dashboard -->
    <script>
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

    <!-- Help Bubble -->
    <x-help-bubble title="Quick Help">
        <div class="space-y-4">
            <div>
                <h4 class="font-medium text-slate-900 mb-2">Getting Started</h4>
                <div class="space-y-3">
                    <a href="{{ route('students.create') }}" class="flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer">
                        <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i>
                        Add a New Student
                    </a>
                    <a href="{{ route('assessments.index') }}" class="flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer">
                        <i data-lucide="clipboard-check" class="w-4 h-4 mr-2"></i>
                        View Assessments
                    </a>
                    <a href="{{ route('extension-requests.staff-index') }}" class="flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer">
                        <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
                        Review Extensions
                    </a>
                </div>
            </div>
            
            <div class="border-t border-slate-200 pt-4">
                <h4 class="font-medium text-slate-900 mb-2">Moodle Tutorials</h4>
                <div class="space-y-2">
                    <a href="https://scribehow.com/shared/Granting_extensions_in_Moodle__NDUZcYS5Rh20B-nZsYM4dA?referrer=documents" target="_blank" class="flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer">
                        <i data-lucide="external-link" class="w-4 h-4 mr-2"></i>
                        Granting Extensions
                    </a>
                    <a href="https://scribehow.com/shared/Setting_up_a_repeat_resubmission__MZSCzNc8QNm_wcrE8VQuUA?referrer=documents" target="_blank" class="flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer">
                        <i data-lucide="external-link" class="w-4 h-4 mr-2"></i>
                        Repeat Submissions
                    </a>
                </div>
            </div>
            
            <div class="border-t border-slate-200 pt-4">
                <h4 class="font-medium text-slate-900 mb-2">Search Tips</h4>
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-700 font-medium">💡 Student Search</p>
                    <p class="text-xs text-blue-600 mt-1">Use the search bar above to find students by name, number, or email. Type at least 2 characters.</p>
                </div>
            </div>
        </div>
    </x-help-bubble>
</x-app-layout>