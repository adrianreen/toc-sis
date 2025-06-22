{{-- Programme Instance Details --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $programmeInstance->programme->title }} - {{ $programmeInstance->label }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('programme-instances.curriculum', $programmeInstance) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                    Manage Curriculum
                </a>
                @if(Auth::user()->role === 'manager')
                    <a href="{{ route('programme-instances.edit', $programmeInstance) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        Edit Instance
                    </a>
                    <form method="POST" action="{{ route('programme-instances.destroy', $programmeInstance) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                onclick="return confirm('Are you sure you want to delete this programme instance? This will remove all curriculum links and enrolments.')">
                            Delete Instance
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Instance Overview --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-blue-50 border-b border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-900">Programme Instance Overview</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Programme</h4>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $programmeInstance->programme->title }}</p>
                            <p class="text-sm text-gray-600">{{ $programmeInstance->programme->awarding_body }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Instance Label</h4>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $programmeInstance->label }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Intake Period</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                <strong>Start:</strong> {{ $programmeInstance->intake_start_date->format('M d, Y') }}<br>
                                @if($programmeInstance->intake_end_date)
                                    <strong>End:</strong> {{ $programmeInstance->intake_end_date->format('M d, Y') }}
                                @else
                                    <span class="text-green-600">Rolling enrolments</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Delivery Style</h4>
                            <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                                @if($programmeInstance->delivery_style === 'blended') bg-blue-100 text-blue-800
                                @elseif($programmeInstance->delivery_style === 'online') bg-green-100 text-green-800
                                @else bg-purple-100 text-purple-800 @endif">
                                {{ ucfirst($programmeInstance->delivery_style) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Curriculum Modules --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-green-50 border-b border-green-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-green-900">Curriculum Modules</h3>
                        <a href="{{ route('programme-instances.curriculum', $programmeInstance) }}" 
                           class="text-sm text-green-700 hover:text-green-900 font-medium">
                            Manage Curriculum →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($programmeInstance->moduleInstances->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($programmeInstance->moduleInstances as $moduleInstance)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900">{{ $moduleInstance->module->title }}</h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $moduleInstance->module->module_code }} • 
                                                {{ $moduleInstance->module->credit_value }} credits
                                            </p>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ ucfirst($moduleInstance->delivery_style) }}
                                                </span>
                                                @if($moduleInstance->tutor)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $moduleInstance->tutor->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Curriculum Modules</h3>
                            <p class="text-gray-500 mb-4">Add module instances to build the curriculum for this programme.</p>
                            <a href="{{ route('programme-instances.curriculum', $programmeInstance) }}" 
                               class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                Build Curriculum
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Enrolled Students --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-purple-50 border-b border-purple-200">
                    <h3 class="text-lg font-semibold text-purple-900">Enrolled Students</h3>
                </div>
                <div class="p-6">
                    @if($programmeInstance->enrolments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student Number
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Enrolment Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($programmeInstance->enrolments as $enrolment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $enrolment->student->full_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $enrolment->student->email }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $enrolment->student->student_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $enrolment->enrolment_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($enrolment->status === 'active') bg-green-100 text-green-800
                                                    @elseif($enrolment->status === 'completed') bg-blue-100 text-blue-800
                                                    @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($enrolment->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('students.show', $enrolment->student) }}" 
                                                   class="text-blue-600 hover:text-blue-900">View Student</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Students Enrolled</h3>
                            <p class="text-gray-500">Students will appear here once they are enrolled in this programme instance.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>