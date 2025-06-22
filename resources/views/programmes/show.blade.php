{{-- resources/views/programmes/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Programme: {{ $programme->title }}
            </h2>
            <div class="space-x-2">
                @if(Auth::user()->role === 'manager')
                    <a href="{{ route('programmes.edit', $programme) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Programme
                    </a>
                    <form method="POST" action="{{ route('programmes.destroy', $programme) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('Are you sure you want to delete this programme? This action cannot be undone and will affect all programme instances.')">
                            Delete Programme
                        </button>
                    </form>
                @endif
                <a href="{{ route('programmes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Programmes
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

            <!-- Programme Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Programme Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Awarding Body</p>
                            <p class="font-medium">{{ $programme->awarding_body ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">NFQ Level</p>
                            <p class="font-medium">{{ $programme->nfq_level ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Credits</p>
                            <p class="font-medium">{{ $programme->total_credits ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Programme Instances</p>
                            <p class="font-medium">{{ $programme->programmeInstances->count() }}</p>
                        </div>
                    </div>
                    @if($programme->description)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Description</p>
                            <p class="font-medium">{{ $programme->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Programme Instances -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Programme Instances</h3>
                        @if(Auth::user()->role === 'manager')
                            <a href="{{ route('programme-instances.create') }}?programme_id={{ $programme->id }}" 
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Create Instance
                            </a>
                        @endif
                    </div>
                    @if($programme->programmeInstances->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Instance Label
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Intake Period
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Delivery Style
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Students
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($programme->programmeInstances->sortByDesc('intake_start_date') as $instance)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $instance->label }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($instance->intake_start_date)
                                                {{ $instance->intake_start_date->format('M Y') }}
                                                @if($instance->intake_end_date)
                                                    - {{ $instance->intake_end_date->format('M Y') }}
                                                @endif
                                            @else
                                                Rolling
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($instance->delivery_style) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $instance->enrolments->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('programme-instances.show', $instance) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                View
                                            </a>
                                            <a href="{{ route('programme-instances.curriculum', $instance) }}" class="text-blue-600 hover:text-blue-900">
                                                Curriculum
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No programme instances created yet.</p>
                    @endif
                </div>
            </div>

            @if($programme->description || $programme->learning_outcomes)
            <!-- Additional Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Additional Information</h3>
                    
                    @if($programme->learning_outcomes)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 font-medium mb-2">Learning Outcomes</p>
                            <div class="text-sm text-gray-700">{{ $programme->learning_outcomes }}</div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>