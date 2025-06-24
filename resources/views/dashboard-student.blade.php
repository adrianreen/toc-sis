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
            
            {{-- Check for active enrollments --}}
            @if(!Auth::user()->student->hasActiveEnrollments())
                <div class="mb-8 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">No Active Enrollments</h3>
                            <div class="mt-2 text-sm text-amber-700">
                                <p>You currently do not have any active enrollments. Some features may not be available.</p>
                                <p class="mt-1">Please contact Student Services if you believe this is an error.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Primary Quick Actions -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-slate-900 mb-6">My Learning</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- View My Progress -->
                    <a href="{{ route('students.progress') }}" class="group bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-6 text-white hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl cursor-pointer">
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
                    <a href="{{ route('students.enrolments') }}" class="group bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl p-6 text-white hover:from-emerald-700 hover:to-emerald-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl cursor-pointer">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">My Courses</h3>
                                <p class="text-emerald-100 text-sm mt-1">Programmes & modules</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>

                    <!-- View My Grades -->
                    <a href="{{ route('students.grades') }}" class="group bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-xl p-6 text-white hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl cursor-pointer">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">My Grades</h3>
                                <p class="text-indigo-100 text-sm mt-1">Assessment results</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="award" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>

                    <!-- My Documents -->
                    <a href="{{ route('my-documents') }}" class="group bg-gradient-to-br from-teal-600 to-teal-700 rounded-xl p-6 text-white hover:from-teal-700 hover:to-teal-800 transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl cursor-pointer">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-lg text-white">My Documents</h3>
                                <p class="text-teal-100 text-sm mt-1">Upload & manage files</p>
                            </div>
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                <i data-lucide="file-text" class="w-6 h-6 text-white"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Learning Platform Launchpad -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Learning Platform</h3>
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900">Moodle Learning Management System</h4>
                                    <p class="text-slate-600 text-sm">Access your course materials, submit assignments, and participate in discussions</p>
                                </div>
                            </div>
                            <a href="https://moodle.theopencollege.com" 
                               target="_blank" 
                               class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2 cursor-pointer">
                                <span>Launch Moodle</span>
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Microsoft 365 Launchpad -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Microsoft 365 Tools</h3>
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Microsoft Word -->
                            <a href="https://office.com/launch/word" 
                               target="_blank"
                               class="group flex flex-col items-center p-4 rounded-lg hover:bg-blue-50 transition-colors duration-200 cursor-pointer">
                                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-700 transition-colors duration-200">
                                    <span class="text-white font-bold text-lg">W</span>
                                </div>
                                <span class="text-sm font-medium text-slate-700 group-hover:text-blue-700 transition-colors duration-200">Word</span>
                                <span class="text-xs text-slate-500">Documents</span>
                            </a>

                            <!-- Outlook -->
                            <a href="https://outlook.office365.com" 
                               target="_blank"
                               class="group flex flex-col items-center p-4 rounded-lg hover:bg-blue-50 transition-colors duration-200 cursor-pointer">
                                <div class="w-12 h-12 bg-blue-700 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-800 transition-colors duration-200">
                                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                        <path d="m22 6-10 6L2 6"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-slate-700 group-hover:text-blue-700 transition-colors duration-200">Outlook</span>
                                <span class="text-xs text-slate-500">College Email</span>
                            </a>

                            <!-- PowerPoint -->
                            <a href="https://office.com/launch/powerpoint" 
                               target="_blank"
                               class="group flex flex-col items-center p-4 rounded-lg hover:bg-orange-50 transition-colors duration-200 cursor-pointer">
                                <div class="w-12 h-12 bg-orange-600 rounded-lg flex items-center justify-center mb-2 group-hover:bg-orange-700 transition-colors duration-200">
                                    <span class="text-white font-bold text-lg">P</span>
                                </div>
                                <span class="text-sm font-medium text-slate-700 group-hover:text-orange-700 transition-colors duration-200">PowerPoint</span>
                                <span class="text-xs text-slate-500">Presentations</span>
                            </a>

                            <!-- OneDrive -->
                            <a href="https://onedrive.live.com" 
                               target="_blank"
                               class="group flex flex-col items-center p-4 rounded-lg hover:bg-blue-50 transition-colors duration-200 cursor-pointer">
                                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-600 transition-colors duration-200">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18.8 8.5c-.9-4.1-4.4-7.3-8.8-7.3-3.7 0-6.9 2.3-8.2 5.5-2.6.4-4.6 2.7-4.6 5.4 0 3 2.4 5.4 5.4 5.4h14.8c2.2 0 4-1.8 4-4 0-2.1-1.6-3.8-3.6-4z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-slate-700 group-hover:text-blue-700 transition-colors duration-200">OneDrive</span>
                                <span class="text-xs text-slate-500">Cloud Storage</span>
                            </a>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <p class="text-xs text-slate-500 text-center">
                                Access your free Microsoft 365 account with your college credentials
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Secondary Actions -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Support & Services</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Extension Requests -->
                        <a href="{{ route('extension-requests.index') }}" class="group bg-white border-2 border-orange-200 rounded-xl p-6 hover:border-orange-300 hover:shadow-md transition-all duration-200 cursor-pointer">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="calendar-plus" class="w-5 h-5 text-orange-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-slate-900 group-hover:text-orange-700 transition-colors duration-200">Request Extension</h4>
                                    <p class="text-slate-600 text-sm">Course extensions</p>
                                </div>
                            </div>
                        </a>

                        <!-- My Profile -->
                        <a href="{{ route('students.profile') }}" class="group bg-white border-2 border-purple-200 rounded-xl p-6 hover:border-purple-300 hover:shadow-md transition-all duration-200 cursor-pointer">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="user" class="w-5 h-5 text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-slate-900 group-hover:text-purple-700 transition-colors duration-200">My Profile</h4>
                                    <p class="text-slate-600 text-sm">Update details</p>
                                </div>
                            </div>
                        </a>

                        <!-- Help & Support -->
                        <a href="mailto:student.services@theopencollege.com" class="group bg-white border-2 border-slate-200 rounded-xl p-6 hover:border-slate-300 hover:shadow-md transition-all duration-200 cursor-pointer">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="help-circle" class="w-5 h-5 text-slate-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-slate-900 group-hover:text-slate-700 transition-colors duration-200">Get Help</h4>
                                    <p class="text-slate-600 text-sm">Contact support</p>
                                </div>
                            </div>
                        </a>
                    </div>
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
                                $recentGrades = Auth::user()->student->getCurrentGradeRecords()
                                    ->with(['moduleInstance.module'])
                                    ->whereNotNull('grade')
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
                    <!-- My Documents Summary -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div class="p-6 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-slate-900">My Documents</h3>
                                <a href="{{ route('my-documents') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer">
                                    View all →
                                </a>
                            </div>
                        </div>
                        <div class="p-6">
                            @php
                                $recentDocuments = Auth::user()->student->documents()
                                    ->latest('uploaded_at')
                                    ->limit(3)
                                    ->get();
                                $documentsCount = Auth::user()->student->documents()->count();
                                $pendingCount = Auth::user()->student->documents()->where('status', 'uploaded')->count();
                                $verifiedCount = Auth::user()->student->documents()->where('status', 'verified')->count();
                            @endphp
                            
                            <!-- Document Statistics -->
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-slate-900">{{ $documentsCount }}</div>
                                    <div class="text-xs text-slate-500">Total</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-amber-600">{{ $pendingCount }}</div>
                                    <div class="text-xs text-slate-500">Pending</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-semibold text-green-600">{{ $verifiedCount }}</div>
                                    <div class="text-xs text-slate-500">Verified</div>
                                </div>
                            </div>

                            @if($recentDocuments->count() > 0)
                                <div class="space-y-3">
                                    @foreach($recentDocuments as $document)
                                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-slate-900 truncate">{{ $document->title }}</p>
                                                <p class="text-xs text-slate-500">{{ $document->uploaded_at->diffForHumans() }}</p>
                                            </div>
                                            <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium {{ $document->status_color }}">
                                                {{ ucfirst($document->status) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-4 pt-3 border-t border-slate-200">
                                    <a href="{{ route('students.documents.create', Auth::user()->student) }}" 
                                       class="w-full flex items-center justify-center px-4 py-2 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700 transition-colors cursor-pointer">
                                        <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                                        Upload New Document
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i data-lucide="file-plus" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                                    <p class="text-slate-500 text-sm mb-3">No documents uploaded yet</p>
                                    <a href="{{ route('students.documents.create', Auth::user()->student) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm rounded-lg hover:bg-teal-700 transition-colors cursor-pointer">
                                        <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                                        Upload First Document
                                    </a>
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