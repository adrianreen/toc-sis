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
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6">
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
                                <h3 class="font-semibold text-lg text-white">My Enrolments</h3>
                                <p class="text-emerald-100 text-sm mt-1">Programmes & modules</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>

                    <!-- View My Grades -->
                    <a href="{{ route('students.grades') }}" class="group bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-xl p-6 text-white hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">My Grades</h3>
                                <p class="text-indigo-100 text-sm mt-1">View assessment results</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="award" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Extension Requests -->
                    <a href="{{ route('extension-requests.index') }}" class="group bg-gradient-to-br from-orange-600 to-orange-700 rounded-xl p-6 text-white hover:from-orange-700 hover:to-orange-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">Extensions</h3>
                                <p class="text-orange-100 text-sm mt-1">Request course extension</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="calendar-plus" class="w-6 h-6 text-white"></i>
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
                                <h3 class="text-lg font-semibold text-slate-900">My Current Enrolments</h3>
                                <a href="{{ route('students.enrolments') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    View all →
                                </a>
                            </div>
                        </div>
                        <div class="p-6">
                            @php
                                $programmeEnrolments = Auth::user()->student->getCurrentProgrammeEnrolments()->limit(2)->get();
                                $moduleEnrolments = Auth::user()->student->getCurrentModuleEnrolments()->limit(2)->get();
                            @endphp
                            
                            @if($programmeEnrolments->count() > 0 || $moduleEnrolments->count() > 0)
                                <div class="space-y-4">
                                    {{-- Programme Enrolments --}}
                                    @foreach($programmeEnrolments as $enrolment)
                                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200">
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                    <h4 class="font-medium text-slate-900">{{ $enrolment->programmeInstance->programme->title }}</h4>
                                                </div>
                                                <p class="text-sm text-slate-600 ml-4">
                                                    Instance: {{ $enrolment->programmeInstance->label }} • 
                                                    {{ $enrolment->programmeInstance->programme->total_credits }} Credits •
                                                    NQF {{ $enrolment->programmeInstance->programme->nfq_level }}
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

                                    {{-- Module Enrolments --}}
                                    @foreach($moduleEnrolments as $enrolment)
                                        <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                    <h4 class="font-medium text-slate-900">{{ $enrolment->moduleInstance->module->title }}</h4>
                                                </div>
                                                <p class="text-sm text-slate-600 ml-4">
                                                    {{ $enrolment->moduleInstance->module->module_code }} • 
                                                    {{ $enrolment->moduleInstance->module->credit_value }} Credits •
                                                    {{ ucfirst($enrolment->moduleInstance->delivery_style) }}
                                                    @if($enrolment->moduleInstance->tutor)
                                                        • {{ $enrolment->moduleInstance->tutor->name }}
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
                                    <p class="text-slate-500">No active enrolments</p>
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
                                $recentGrades = Auth::user()->student->studentGradeRecords()
                                    ->with(['moduleInstance.module'])
                                    ->whereNotNull('grade')
                                    ->where(function ($query) {
                                        $query->where('is_visible_to_student', true)
                                              ->orWhere(function ($q) {
                                                  $q->whereNotNull('release_date')
                                                    ->where('release_date', '<=', now());
                                              });
                                    })
                                    ->latest('graded_date')
                                    ->limit(4)
                                    ->get();
                            @endphp
                            
                            @if($recentGrades->count() > 0)
                                <div class="space-y-4">
                                    @foreach($recentGrades as $gradeRecord)
                                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg">
                                            <div>
                                                <h4 class="font-medium text-slate-900">{{ $gradeRecord->assessment_component_name }}</h4>
                                                <p class="text-sm text-slate-600">
                                                    {{ $gradeRecord->moduleInstance->module->title }} ({{ $gradeRecord->moduleInstance->module->module_code }})
                                                </p>
                                                <p class="text-xs text-slate-500">
                                                    @if($gradeRecord->graded_date)
                                                        Graded {{ $gradeRecord->graded_date->diffForHumans() }}
                                                    @else
                                                        Recently graded
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-lg font-semibold {{ $gradeRecord->percentage >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($gradeRecord->percentage, 1) }}%
                                                </div>
                                                <div class="text-xs text-slate-500">
                                                    {{ $gradeRecord->grade }}/{{ $gradeRecord->max_grade }}
                                                </div>
                                                <div class="text-xs {{ $gradeRecord->percentage >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $gradeRecord->percentage >= 40 ? 'PASS' : 'FAIL' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i data-lucide="clipboard-check" class="w-12 h-12 text-slate-400 mx-auto mb-4"></i>
                                    <p class="text-slate-500">No recent grades available</p>
                                    <p class="text-sm text-slate-400 mt-1">Grades will appear here once released by tutors</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                  

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