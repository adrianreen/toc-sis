{{-- resources/views/modules/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Module: {{ $module->module_code }} - {{ $module->title }}
            </h2>
            <div class="space-x-2">
                @if(Auth::user()->role === 'manager')
                    <a href="{{ route('modules.edit', $module) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Module
                    </a>
                    <form method="POST" action="{{ route('modules.destroy', $module) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('Are you sure you want to delete this module? This will affect all module instances and assessment records.')">
                            Delete Module
                        </button>
                    </form>
                @endif
                <a href="{{ route('modules.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Modules
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Module Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Module Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Module Code</p>
                            <p class="font-medium">{{ $module->module_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">NFQ Level</p>
                            <p class="font-medium">{{ $module->nfq_level ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Credit Value</p>
                            <p class="font-medium">{{ $module->credit_value }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Module Instances</p>
                            <p class="font-medium">{{ $module->moduleInstances->count() }}</p>
                        </div>
                    </div>
                    @if($module->description)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Description</p>
                            <p class="font-medium">{{ $module->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Module Instances -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Module Instances ({{ $module->moduleInstances->count() }})</h3>
                    @if($module->moduleInstances->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Instance Label
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Delivery Style
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Start Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tutor
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Enrolled Students
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($module->moduleInstances as $instance)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $instance->label ?? 'Default Instance' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($instance->delivery_style) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $instance->start_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $instance->tutor?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $instance->studentGradeRecords->pluck('student_id')->unique()->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('module-instances.show', $instance) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View Instance
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No module instances have been created yet.</p>
                    @endif
                </div>
            </div>


            <!-- Assessment Strategy -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Assessment Strategy</h3>
                        @if(Auth::user()->role === 'manager')
                            <a href="{{ route('modules.edit', $module) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Edit Strategy
                            </a>
                        @endif
                    </div>
                    @if($module->assessment_strategy && count($module->assessment_strategy) > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Component Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Weighting
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pass Mark
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Must Pass
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($module->assessment_strategy as $component)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $component['component_name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $component['weighting'] }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $component['component_pass_mark'] ?? 40 }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $component['is_must_pass'] ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $component['is_must_pass'] ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4 text-sm text-gray-600">
                            <p><strong>Total Weighting:</strong> {{ collect($module->assessment_strategy)->sum('weighting') }}%</p>
                        </div>
                    @else
                        <p class="text-gray-500">No assessment strategy defined for this module yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>