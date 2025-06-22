{{-- Programme Instance Curriculum Management --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Curriculum: {{ $programmeInstance->programme->title }} - {{ $programmeInstance->label }}
            </h2>
            <a href="{{ route('programme-instances.show', $programmeInstance) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                Back to Instance
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Instructions --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Curriculum Management</h3>
                        <p class="mt-1 text-sm text-blue-700">
                            Build your programme curriculum by adding module instances. Each module instance represents a live delivery of a module blueprint.
                            Students enrolled in this programme will automatically be enrolled in all curriculum modules.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Current Curriculum --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-green-50 border-b border-green-200">
                    <h3 class="text-lg font-semibold text-green-900">Current Curriculum Modules</h3>
                </div>
                <div class="p-6">
                    @if($programmeInstance->moduleInstances->count() > 0)
                        <div class="space-y-4">
                            @foreach($programmeInstance->moduleInstances as $moduleInstance)
                                <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900">{{ $moduleInstance->module->title }}</h4>
                                                <p class="text-sm text-gray-600">
                                                    {{ $moduleInstance->module->module_code }} • 
                                                    {{ $moduleInstance->module->credit_value }} credits • 
                                                    Start: {{ $moduleInstance->start_date->format('M d, Y') }}
                                                    @if($moduleInstance->target_end_date)
                                                        • End: {{ $moduleInstance->target_end_date->format('M d, Y') }}
                                                    @endif
                                                </p>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ ucfirst($moduleInstance->delivery_style) }}
                                                    </span>
                                                    @if($moduleInstance->tutor)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            Tutor: {{ $moduleInstance->tutor->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <form method="POST" action="{{ route('programme-instances.curriculum.detach', [$programmeInstance, $moduleInstance]) }}" 
                                              onsubmit="return confirm('Remove this module from the curriculum? Students enrolled in this programme will lose access to this module.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                                Remove
                                            </button>
                                        </form>
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
                            <p class="text-gray-500">Add module instances below to build the curriculum for this programme.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Add Modules to Curriculum --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-blue-50 border-b border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-900">Available Module Instances</h3>
                    <p class="text-sm text-blue-700 mt-1">Module instances that can be added to this programme's curriculum</p>
                </div>
                <div class="p-6">
                    @if($availableModules->count() > 0)
                        <div class="space-y-4">
                            @foreach($availableModules as $moduleInstance)
                                <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900">{{ $moduleInstance->module->title }}</h4>
                                                <p class="text-sm text-gray-600">
                                                    {{ $moduleInstance->module->module_code }} • 
                                                    {{ $moduleInstance->module->credit_value }} credits • 
                                                    Start: {{ $moduleInstance->start_date->format('M d, Y') }}
                                                    @if($moduleInstance->target_end_date)
                                                        • End: {{ $moduleInstance->target_end_date->format('M d, Y') }}
                                                    @endif
                                                </p>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ ucfirst($moduleInstance->delivery_style) }}
                                                    </span>
                                                    @if($moduleInstance->tutor)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            Tutor: {{ $moduleInstance->tutor->name }}
                                                        </span>
                                                    @endif
                                                    @if($moduleInstance->module->allows_standalone_enrolment)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Standalone Enabled
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <form method="POST" action="{{ route('programme-instances.curriculum.attach', $programmeInstance) }}">
                                            @csrf
                                            <input type="hidden" name="module_instance_id" value="{{ $moduleInstance->id }}">
                                            <button type="submit" 
                                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                                Add to Curriculum
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Available Module Instances</h3>
                            <p class="text-gray-500 mb-4">All existing module instances are already part of this programme's curriculum, or no module instances exist yet.</p>
                            <a href="{{ route('module-instances.create') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                Create Module Instance
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>