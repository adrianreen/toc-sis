{{-- resources/views/student/enrolments.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    My Programme Enrolments
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $student->full_name }} • {{ $student->student_number }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <a href="{{ route('students.progress') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    📊 View My Progress
                </a>
                <a href="{{ route('dashboard') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    ← Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Add Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($enrolments->count() > 0)
                <!-- Enrolments Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i data-lucide="book-open" class="w-5 h-5 text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Programmes</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $enrolments->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i data-lucide="play-circle" class="w-5 h-5 text-green-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Active Programmes</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $enrolments->where('status', 'active')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i data-lucide="award" class="w-5 h-5 text-purple-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Completed</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ $enrolments->where('status', 'completed')->count() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrolments List -->
                <div class="space-y-6">
                    @foreach($enrolments->sortByDesc('enrolment_date') as $enrolment)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                    <!-- Programme Information -->
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-4">
                                            <div>
                                                <h3 class="text-xl font-semibold text-gray-900 mb-1">
                                                    {{ $enrolment->programme->title }}
                                                </h3>
                                                <p class="text-gray-600 mb-2">
                                                    Programme Code: <span class="font-medium">{{ $enrolment->programme->code }}</span>
                                                </p>
                                                
                                                @if($enrolment->cohort)
                                                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-2">
                                                        <span class="flex items-center">
                                                            <i data-lucide="users" class="w-4 h-4 mr-1"></i>
                                                            Cohort: {{ $enrolment->cohort->code }} - {{ $enrolment->cohort->name }}
                                                        </span>
                                                    </div>
                                                @endif

                                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                                    <span class="flex items-center">
                                                        <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                                                        Enrolled: {{ $enrolment->enrolment_date->format('d M Y') }}
                                                    </span>
                                                    @if($enrolment->expected_completion_date)
                                                        <span class="flex items-center">
                                                            <i data-lucide="calendar-check" class="w-4 h-4 mr-1"></i>
                                                            Expected Completion: {{ $enrolment->expected_completion_date->format('d M Y') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Status Badge -->
                                            <div class="flex-shrink-0">
                                                <span class="px-4 py-2 rounded-full text-sm font-medium
                                                    @if($enrolment->status === 'active') bg-green-100 text-green-800 border border-green-200
                                                    @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800 border border-yellow-200
                                                    @elseif($enrolment->status === 'completed') bg-blue-100 text-blue-800 border border-blue-200
                                                    @elseif($enrolment->status === 'withdrawn') bg-orange-100 text-orange-800 border border-orange-200
                                                    @else bg-red-100 text-red-800 border border-red-200
                                                    @endif">
                                                    @if($enrolment->status === 'active') ✅ Active
                                                    @elseif($enrolment->status === 'deferred') ⏸️ Deferred
                                                    @elseif($enrolment->status === 'completed') 🎓 Completed
                                                    @elseif($enrolment->status === 'withdrawn') 📤 Withdrawn
                                                    @else ❌ {{ ucfirst($enrolment->status) }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Programme Description -->
                                        @if($enrolment->programme->description)
                                            <div class="mb-4">
                                                <p class="text-gray-600 text-sm leading-relaxed">
                                                    {{ $enrolment->programme->description }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Progress Information -->
                                        @php
                                            $moduleEnrolments = $enrolment->studentModuleEnrolments ?? collect();
                                            $totalModules = $moduleEnrolments->count();
                                            $completedModules = $moduleEnrolments->where('status', 'completed')->count();
                                            $progressPercentage = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;
                                        @endphp

                                        @if($totalModules > 0)
                                            <div class="mb-4">
                                                <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                                                    <span>Module Progress</span>
                                                    <span>{{ $completedModules }}/{{ $totalModules }} modules completed</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">{{ $progressPercentage }}% complete</p>
                                            </div>
                                        @endif

                                        <!-- Completion Information -->
                                        @if($enrolment->status === 'completed' && $enrolment->actual_completion_date)
                                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                                                <div class="flex items-center">
                                                    <i data-lucide="award" class="w-5 h-5 text-green-600 mr-2"></i>
                                                    <span class="text-green-800 font-medium">
                                                        Programme completed on {{ $enrolment->actual_completion_date->format('d M Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Deferral Information -->
                                        @if($enrolment->status === 'deferred')
                                            @php
                                                $activeDeferral = $enrolment->deferrals()->where('status', 'approved')->latest()->first();
                                            @endphp
                                            @if($activeDeferral)
                                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                                    <div class="flex items-start">
                                                        <i data-lucide="pause-circle" class="w-5 h-5 text-yellow-600 mr-2 mt-0.5"></i>
                                                        <div>
                                                            <span class="text-yellow-800 font-medium block">Programme Deferred</span>
                                                            @if($activeDeferral->toCohort)
                                                                <span class="text-yellow-700 text-sm">
                                                                    Expected to return in {{ $activeDeferral->toCohort->name }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex-shrink-0 mt-4 lg:mt-0 lg:ml-6">
                                        <div class="flex flex-col space-y-2">
                                            <a href="{{ route('students.progress') }}" 
                                               class="inline-flex items-center justify-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-200">
                                                <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2"></i>
                                                View Progress
                                            </a>
                                            
                                            @if($enrolment->status === 'active')
                                                <button class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                                                    Download Transcript
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- No Enrolments State -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="book-open" class="w-12 h-12 text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Programme Enrolments</h3>
                        <p class="text-gray-600 mb-6 max-w-md mx-auto">
                            You are not currently enrolled in any programmes. Contact Student Services to discuss available programmes and enrolment options.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="mailto:student.services@theopencollege.com" 
                               class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                                <i data-lucide="mail" class="w-5 h-5 mr-2"></i>
                                Contact Student Services
                            </a>
                            <a href="{{ route('dashboard') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Help Section -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl shadow-sm">
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="help-circle" class="w-6 h-6 text-blue-600"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-medium text-blue-900 mb-2">Need Assistance?</h4>
                            <p class="text-blue-700 mb-4">
                                If you have questions about your programme enrolments, progress, or need to make changes to your studies, our Student Services team is here to help.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="mailto:student.services@theopencollege.com" 
                                   class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700">
                                    <i data-lucide="mail" class="w-4 h-4 mr-2"></i>
                                    student.services@theopencollege.com
                                </a>
                                <a href="tel:+353-1-234-5678" 
                                   class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700">
                                    <i data-lucide="phone" class="w-4 h-4 mr-2"></i>
                                    +353 (1) 234-5678
                                </a>
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