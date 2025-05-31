{{-- resources/views/dashboard-student.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Welcome back, {{ Auth::user()->student->first_name }}!
                </h1>
                <p class="mt-2 text-slate-600">
                    Student Number: {{ Auth::user()->student->student_number }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <div class="bg-white rounded-lg border border-slate-200 px-4 py-2 shadow-sm">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-slate-700">Active Student</span>
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
            
            <!-- Quick Actions for Students -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-slate-900 mb-6">Quick Actions</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- View My Progress -->
                    <a href="{{ route('students.progress') }}" class="group bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-6 text-white hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">My Progress</h3>
                                <p class="text-blue-100 text-sm mt-1">View grades & completion</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="trending-up" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>

                    <!-- View My Enrolments -->
                    <a href="{{ route('students.enrolments') }}" class="group bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl p-6 text-white hover:from-emerald-700 hover:to-emerald-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">My Programmes</h3>
                                <p class="text-emerald-100 text-sm mt-1">View enrolments</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>

                    <!-- My Profile -->
                    <a href="{{ route('students.profile') }}" class="group bg-gradient-to-br from-purple-600 to-purple-700 rounded-xl p-6 text-white hover:from-purple-700 hover:to-purple-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">My Profile</h3>
                                <p class="text-purple-100 text-sm mt-1">View & update details</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="user" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Help & Support -->
                    <a href="mailto:student.services@theopencollege.com" class="group bg-gradient-to-br from-slate-600 to-slate-700 rounded-xl p-6 text-white hover:from-slate-700 hover:to-slate-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">Get Help</h3>
                                <p class="text-slate-100 text-sm mt-1">Contact support</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="help-circle" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Current Enrolments Overview -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div class="p-6 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900">My Current Programmes</h3>
                                <a href="{{ route('students.enrolments') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    View all →
                                </a>
                            </div>
                        </div>
                        <div class="p-6">
                            @php
                                $currentEnrolments = Auth::user()->student->enrolments()
                                    ->with(['programme', 'cohort'])
                                    ->whereIn('status', ['active', 'deferred'])
                                    ->latest()
                                    ->limit(3)
                                    ->get();
                            @endphp
                            
                            @if($currentEnrolments->count() > 0)
                                <div class="space-y-4">
                                    @foreach($currentEnrolments as $enrolment)
                                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-slate-900">{{ $enrolment->programme->title }}</h4>
                                                <p class="text-sm text-slate-600">
                                                    {{ $enrolment->programme->code }}
                                                    @if($enrolment->cohort)
                                                        • Cohort {{ $enrolment->cohort->code }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="px-3 py-1 rounded-full text-xs font-medium 
                                                @if($enrolment->status === 'active') bg-green-100 text-green-800
                                                @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($enrolment->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i data-lucide="book-open" class="w-12 h-12 text-slate-400 mx-auto mb-4"></i>
                                    <p class="text-slate-500">No active programme enrolments</p>
                                    <p class="text-sm text-slate-400 mt-1">Contact Student Services if you need assistance</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Progress -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div class="p-6 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900">Recent Progress</h3>
                                <a href="{{ route('students.progress') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    View detailed progress →
                                </a>
                            </div>
                        </div>
                        <div class="p-6">
                            @php
                                $recentGrades = \App\Models\StudentAssessment::whereHas('studentModuleEnrolment', function($q) {
                                    $q->where('student_id', Auth::user()->student->id);
                                })
                                ->whereNotNull('grade')
                                ->with(['assessmentComponent.module', 'studentModuleEnrolment.moduleInstance'])
                                ->latest('graded_date')
                                ->limit(3)
                                ->get();
                            @endphp
                            
                            @if($recentGrades->count() > 0)
                                <div class="space-y-4">
                                    @foreach($recentGrades as $grade)
                                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-slate-900">{{ $grade->assessmentComponent->name }}</h4>
                                                <p class="text-sm text-slate-600">
                                                    {{ $grade->studentModuleEnrolment->moduleInstance->instance_code }}
                                                </p>
                                                <p class="text-xs text-slate-500">
                                                    Graded {{ $grade->graded_date ? $grade->graded_date->diffForHumans() : 'recently' }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-semibold {{ $grade->grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($grade->grade, 1) }}%
                                                </div>
                                                <div class="text-xs {{ $grade->grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $grade->grade >= 40 ? 'PASS' : 'FAIL' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i data-lucide="clipboard-check" class="w-12 h-12 text-slate-400 mx-auto mb-4"></i>
                                    <p class="text-slate-500">No recent grades available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Upcoming Deadlines -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div class="p-6 border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-900">Upcoming Deadlines</h3>
                        </div>
                        <div class="p-6">
                            @php
                                $upcomingDeadlines = \App\Models\StudentAssessment::whereHas('studentModuleEnrolment', function($q) {
                                    $q->where('student_id', Auth::user()->student->id);
                                })
                                ->where('status', 'pending')
                                ->where('due_date', '>=', now())
                                ->with(['assessmentComponent', 'studentModuleEnrolment.moduleInstance'])
                                ->orderBy('due_date')
                                ->limit(5)
                                ->get();
                            @endphp
                            
                            @if($upcomingDeadlines->count() > 0)
                                <div class="space-y-3">
                                    @foreach($upcomingDeadlines as $deadline)
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-slate-900">{{ $deadline->assessmentComponent->name }}</p>
                                                <p class="text-xs text-slate-500">{{ $deadline->studentModuleEnrolment->moduleInstance->instance_code }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium {{ $deadline->due_date->isToday() ? 'text-red-600' : ($deadline->due_date->diffInDays() <= 3 ? 'text-orange-600' : 'text-slate-900') }}">
                                                    {{ $deadline->due_date->format('M j') }}
                                                </p>
                                                <p class="text-xs text-slate-500">{{ $deadline->due_date->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i data-lucide="calendar-check" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                                    <p class="text-sm text-slate-500">No upcoming deadlines</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Student Information -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div class="p-6 border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-900">Student Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-slate-500">Student Number</p>
                                    <p class="font-medium text-slate-900">{{ Auth::user()->student->student_number }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Email</p>
                                    <p class="font-medium text-slate-900">{{ Auth::user()->student->email }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Status</p>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                                        @if(Auth::user()->student->status === 'active') bg-green-100 text-green-800
                                        @elseif(Auth::user()->student->status === 'deferred') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(Auth::user()->student->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Help -->
                    <div class="bg-blue-50 rounded-xl border border-blue-200 shadow-sm">
                        <div class="p-6">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-900 mb-2">Need Help?</h4>
                                    <p class="text-sm text-blue-700 mb-3">
                                        Contact Student Services for any questions about your programme, assessments, or technical issues.
                                    </p>
                                    <a href="mailto:student.services@theopencollege.com" 
                                       class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700">
                                        <i data-lucide="mail" class="w-4 h-4 mr-1"></i>
                                        Email Support
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
</x-app-layout>