{{-- Student: My Enrolments with New Architecture --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Enrolments
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Programme Enrolments Section --}}
            @if($programmeEnrolments->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                    <div class="p-6 bg-blue-50 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Programme Enrolments
                        </h3>
                        <p class="text-blue-700 text-sm mt-1">Complete programmes with full curriculum</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($programmeEnrolments as $enrolment)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-semibold text-gray-900">
                                                {{ $enrolment->programmeInstance->programme->title }}
                                            </h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <strong>Instance:</strong> {{ $enrolment->programmeInstance->label }} |
                                                <strong>Started:</strong> {{ $enrolment->programmeInstance->intake_start_date->format('M d, Y') }} |
                                                <strong>Enrolled:</strong> {{ $enrolment->enrolment_date->format('M d, Y') }}
                                            </p>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($enrolment->status === 'active') bg-green-100 text-green-800
                                                    @elseif($enrolment->status === 'completed') bg-blue-100 text-blue-800
                                                    @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($enrolment->status) }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $enrolment->programmeInstance->programme->total_credits }} Credits
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    NQF Level {{ $enrolment->programmeInstance->programme->nfq_level }}
                                                </span>
                                            </div>
                                            
                                            {{-- Show Module Instances in this Programme --}}
                                            @if($enrolment->programmeInstance->moduleInstances->count() > 0)
                                                <div class="mt-3">
                                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Programme Curriculum:</h5>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                        @foreach($enrolment->programmeInstance->moduleInstances as $moduleInstance)
                                                            <div class="text-xs bg-gray-50 border border-gray-200 rounded p-2">
                                                                <div class="font-medium">{{ $moduleInstance->module->title }}</div>
                                                                <div class="text-gray-600">
                                                                    {{ $moduleInstance->module->module_code }} | 
                                                                    {{ $moduleInstance->module->credit_value }} credits |
                                                                    {{ ucfirst($moduleInstance->delivery_style) }}
                                                                </div>
                                                                @if($moduleInstance->tutor)
                                                                    <div class="text-gray-500">Tutor: {{ $moduleInstance->tutor->name }}</div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Module Enrolments Section --}}
            @if($moduleEnrolments->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-green-50 border-b border-green-200">
                        <h3 class="text-lg font-semibold text-green-900 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            Standalone Module Enrolments
                        </h3>
                        <p class="text-green-700 text-sm mt-1">Individual modules taken outside of programme</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($moduleEnrolments as $enrolment)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-semibold text-gray-900">
                                                {{ $enrolment->moduleInstance->module->title }}
                                            </h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <strong>Code:</strong> {{ $enrolment->moduleInstance->module->module_code }} |
                                                <strong>Started:</strong> {{ $enrolment->moduleInstance->start_date->format('M d, Y') }} |
                                                <strong>Enrolled:</strong> {{ $enrolment->enrolment_date->format('M d, Y') }}
                                                @if($enrolment->moduleInstance->target_end_date)
                                                    | <strong>Target End:</strong> {{ $enrolment->moduleInstance->target_end_date->format('M d, Y') }}
                                                @endif
                                            </p>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($enrolment->status === 'active') bg-green-100 text-green-800
                                                    @elseif($enrolment->status === 'completed') bg-blue-100 text-blue-800
                                                    @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($enrolment->status) }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $enrolment->moduleInstance->module->credit_value }} Credits
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ ucfirst($enrolment->moduleInstance->delivery_style) }}
                                                </span>
                                                @if($enrolment->moduleInstance->tutor)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Tutor: {{ $enrolment->moduleInstance->tutor->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            {{-- Show Assessment Components --}}
                                            @if($enrolment->moduleInstance->module->assessment_strategy)
                                                <div class="mt-3">
                                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Assessment Components:</h5>
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($enrolment->moduleInstance->module->assessment_strategy as $component)
                                                            <span class="text-xs bg-blue-50 border border-blue-200 rounded px-2 py-1">
                                                                {{ $component['component_name'] }} ({{ $component['weighting'] }}%)
                                                                @if($component['is_must_pass'])
                                                                    <span class="text-red-600">*</span>
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">* Must pass component</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- No Enrolments State --}}
            @if($programmeEnrolments->count() === 0 && $moduleEnrolments->count() === 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Enrolments</h3>
                        <p class="text-gray-500">You are not currently enrolled in any programmes or modules.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>