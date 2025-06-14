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
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Primary Action: View Students (Most Used) -->
                    <a href="{{ route('students.index') }}" class="group bg-white border border-slate-200 rounded-lg p-5 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                                <i data-lucide="users" class="w-5 h-5 text-slate-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 text-sm">Students</h3>
                                <p class="text-slate-500 text-xs">View & manage</p>
                            </div>
                        </div>
                    </a>

                    @if(in_array(Auth::user()->role, ['manager', 'teacher', 'student_services']))
                        <!-- Primary Action: Assessments/Grading -->
                        <a href="{{ route('assessments.index') }}" class="group bg-white border border-slate-200 rounded-lg p-5 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                    <i data-lucide="clipboard-check" class="w-5 h-5 text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Assessments</h3>
                                    <p class="text-slate-500 text-xs">Grading & results</p>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                        <!-- Secondary Action: Add Student -->
                        <a href="{{ route('students.create') }}" class="group bg-white border border-slate-200 rounded-lg p-5 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center group-hover:bg-green-100 transition-colors">
                                    <i data-lucide="user-plus" class="w-5 h-5 text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Add Student</h3>
                                    <p class="text-slate-500 text-xs">Create new record</p>
                                </div>
                            </div>
                        </a>

                        <!-- Secondary Action: Enquiries -->
                        <a href="{{ route('enquiries.index') }}" class="group bg-white border border-slate-200 rounded-lg p-5 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                                    <i data-lucide="mail" class="w-5 h-5 text-amber-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Enquiries</h3>
                                    <p class="text-slate-500 text-xs">Manage leads</p>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if(in_array(Auth::user()->role, ['manager', 'teacher', 'student_services']))
                        <!-- Extension Requests -->
                        <a href="{{ route('extension-requests.staff-index') }}" class="group bg-white border border-slate-200 rounded-lg p-5 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                    <i data-lucide="clock" class="w-5 h-5 text-orange-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Extensions</h3>
                                    <p class="text-slate-500 text-xs">Review requests</p>
                                </div>
                            </div>
                        </a>
                    @endif

                    @if(Auth::user()->role === 'manager')
                        <!-- Manager-Only: Reports -->
                        <a href="{{ route('reports.dashboard') }}" class="group bg-white border border-slate-200 rounded-lg p-5 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-purple-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Reports</h3>
                                    <p class="text-slate-500 text-xs">Analytics</p>
                                </div>
                            </div>
                        </a>

                        <!-- Manager-Only: Notifications -->
                        <a href="{{ route('notifications.admin') }}" class="group bg-white border border-slate-200 rounded-lg p-5 hover:border-slate-300 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                                    <i data-lucide="bell" class="w-5 h-5 text-indigo-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-sm">Notifications</h3>
                                    <p class="text-slate-500 text-xs">Admin panel</p>
                                </div>
                            </div>
                        </a>
                    @endif
                </div>
                
                <!-- Less Important Actions - Subtle Secondary Section -->
                @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                    <div class="mt-6 pt-6 border-t border-slate-200">
                        <h3 class="text-sm font-medium text-slate-600 mb-3">Administrative Tools</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('students.recycle-bin') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors">
                                <i data-lucide="trash-2" class="w-3 h-3 mr-1.5"></i>
                                Recycle Bin
                            </a>
                            @if(Auth::user()->role === 'manager')
                                <a href="{{ route('programmes.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors">
                                    <i data-lucide="book" class="w-3 h-3 mr-1.5"></i>
                                    Programmes
                                </a>
                                <a href="{{ route('modules.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors">
                                    <i data-lucide="layers" class="w-3 h-3 mr-1.5"></i>
                                    Modules
                                </a>
                                <a href="{{ route('cohorts.index') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 rounded-md hover:bg-slate-100 hover:text-slate-700 transition-colors">
                                    <i data-lucide="users-2" class="w-3 h-3 mr-1.5"></i>
                                    Cohorts
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
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

    <!-- Help Bubble -->
    <x-help-bubble title="Dashboard Help">
        <div class="space-y-4">
            <div>
                <h4 class="font-medium text-slate-900 mb-2">Quick Actions</h4>
                <p class="text-sm text-slate-600 mb-3">Access your most frequently used features from the dashboard.</p>
                
                <a href="{{ route('students.create') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                    <i data-lucide="user-plus" class="w-4 h-4 mr-1.5"></i>
                    How to Add a New Student
                </a>
            </div>
            
            <div class="border-t border-slate-200 pt-4">
                <h4 class="font-medium text-slate-900 mb-2">Moodle Integration</h4>
                <p class="text-sm text-slate-600 mb-3">Learn how to manage assessments and extensions in Moodle.</p>
                
                <div class="space-y-2">
                    <a href="https://scribehow.com/shared/Granting_extensions_in_Moodle__NDUZcYS5Rh20B-nZsYM4dA?referrer=documents" target="_blank" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                        <i data-lucide="play-circle" class="w-4 h-4 mr-1.5"></i>
                        Watch: Granting Extensions in Moodle
                    </a>
                    
                    <a href="https://scribehow.com/shared/Setting_up_a_repeat_resubmission__MZSCzNc8QNm_wcrE8VQuUA?referrer=documents" target="_blank" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                        <i data-lucide="play-circle" class="w-4 h-4 mr-1.5"></i>
                        Watch: Setting up Repeat Resubmission
                    </a>
                </div>
            </div>
            
            <div class="border-t border-slate-200 pt-4">
                <h4 class="font-medium text-slate-900 mb-2">Student Search</h4>
                <p class="text-sm text-slate-600 mb-3">Use the search bar above to quickly find students by name, number, or email.</p>
                
                <div class="bg-blue-50 rounded-lg p-3 mt-2">
                    <p class="text-xs text-blue-700 font-medium">💡 Tip</p>
                    <p class="text-xs text-blue-600 mt-1">Type at least 2 characters to start searching. Results update as you type!</p>
                </div>
            </div>
            
            <!-- ScribeHow Embed -->
            <div class="border-t border-slate-200 pt-4">
                <h4 class="font-medium text-slate-900 mb-2">Deleting Assignment Submissions</h4>
                <p class="text-sm text-slate-600 mb-3">Interactive tutorial for removing assignment submissions from Moodle.</p>
                
                <div class="bg-slate-50 rounded-lg p-2">
                    <iframe 
                        src="https://scribehow.com/embed/Deleting_an_assignment_submission_from_Moodle__KrFfAM2qTW2hfkf8a9Qx2g" 
                        width="100%" 
                        height="400" 
                        allow="fullscreen" 
                        style="aspect-ratio: 1 / 1; border: 0; min-height: 320px; max-height: 400px;" 
                        class="rounded-md"
                    ></iframe>
                </div>
            </div>
            
            <div class="border-t border-slate-200 pt-4">
                <h4 class="font-medium text-slate-900 mb-2">Assessment Management</h4>
                <p class="text-sm text-slate-600 mb-3">Learn about grading, result visibility, and student progress tracking.</p>
                
                <div class="space-y-2">
                    <a href="{{ route('assessments.index') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                        <i data-lucide="clipboard-check" class="w-4 h-4 mr-1.5"></i>
                        Go to Assessments
                    </a>
                    
                    <a href="{{ route('extension-requests.staff-index') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium">
                        <i data-lucide="clock" class="w-4 h-4 mr-1.5"></i>
                        Review Extension Requests
                    </a>
                </div>
            </div>
            
            <div class="border-t border-slate-200 pt-4">
                <h4 class="font-medium text-slate-900 mb-2">Need More Help?</h4>
                <div class="space-y-2">
                    <a href="#" class="block text-sm text-slate-600 hover:text-slate-900">
                        📧 Contact Support
                    </a>
                    <a href="#" class="block text-sm text-slate-600 hover:text-slate-900">
                        📚 Documentation
                    </a>
                    <a href="#" class="block text-sm text-slate-600 hover:text-slate-900">
                        💬 Live Chat
                    </a>
                </div>
            </div>
        </div>
    </x-help-bubble>
</x-app-layout>