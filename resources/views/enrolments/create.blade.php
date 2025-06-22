{{-- Step 1: Two-Path Enrolment Choice --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Enrol Student: {{ $student->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-blue-50 border-b border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">New 4-Level Architecture Enrolment System</h3>
                    <p class="text-blue-700">
                        Choose how you want to enrol this student. You can enrol them in a full programme (which includes all module instances), 
                        or enrol them in individual standalone modules.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Programme Enrolment Option --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="p-3 bg-blue-100 rounded-full mr-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Enrol in Programme</h3>
                        </div>
                        
                        <p class="text-gray-600 mb-4">
                            Enrol the student in a complete programme instance. This automatically enrolls them in all module instances 
                            that are part of the programme curriculum.
                        </p>
                        
                        <ul class="text-sm text-gray-500 mb-6 space-y-1">
                            <li>• Student gets enrolled in Programme Instance</li>
                            <li>• Automatic enrolment in all curriculum modules</li>
                            <li>• Full programme tracking and progression</li>
                            <li>• Supports programme-level deferrals</li>
                        </ul>
                        
                        <a href="{{ route('enrolments.create-programme', $student) }}" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-center block transition-colors duration-200">
                            Choose Programme Enrolment
                        </a>
                    </div>
                </div>

                {{-- Module Enrolment Option --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="p-3 bg-green-100 rounded-full mr-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Enrol in Standalone Module</h3>
                        </div>
                        
                        <p class="text-gray-600 mb-4">
                            Enrol the student in individual module instances. Perfect for students taking single modules, 
                            continuing education, or custom learning paths.
                        </p>
                        
                        <ul class="text-sm text-gray-500 mb-6 space-y-1">
                            <li>• Student enrolled directly in Module Instance</li>
                            <li>• Individual module tracking</li>
                            <li>• Flexible learning options</li>
                            <li>• Perfect for CPD and standalone courses</li>
                        </ul>
                        
                        <a href="{{ route('enrolments.create-module', $student) }}" 
                           class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-center block transition-colors duration-200">
                            Choose Module Enrolment
                        </a>
                    </div>
                </div>
            </div>

            {{-- Back Button --}}
            <div class="flex justify-center mt-8">
                <a href="{{ route('students.show', $student) }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                    Back to Student Profile
                </a>
            </div>
        </div>
    </div>
</x-app-layout>