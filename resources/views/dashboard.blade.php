{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
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
    </script>
</x-app-layout>