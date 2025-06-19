<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Programme Archetypes Management
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Programme Archetypes Management</h3>
                        <div>
                            <a href="{{ route('admin.archetypes.validation') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Validation Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div class="bg-blue-500 text-white rounded-lg p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-blue-100 text-sm">Total Archetypes</p>
                                    <p class="text-3xl font-bold">{{ $statistics['total_archetypes'] }}</p>
                                </div>
                                <div class="text-blue-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-500 text-white rounded-lg p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-green-100 text-sm">Programmes Using Archetypes</p>
                                    <p class="text-3xl font-bold">{{ $statistics['programmes_using_archetypes'] }}</p>
                                </div>
                                <div class="text-green-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="bg-indigo-500 text-white rounded-lg p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-indigo-100 text-sm">Total Programmes</p>
                                    <p class="text-3xl font-bold">{{ $statistics['total_programmes'] }}</p>
                                </div>
                                <div class="text-indigo-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="bg-yellow-500 text-white rounded-lg p-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-yellow-100 text-sm">Adoption Rate</p>
                                    <p class="text-3xl font-bold">{{ $statistics['total_programmes'] > 0 ? round(($statistics['programmes_using_archetypes'] / $statistics['total_programmes']) * 100, 1) : 0 }}%</p>
                                </div>
                                <div class="text-yellow-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 3v18h18"/>
                                        <path d="m19 9-5 5-4-4-3 3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archetypes Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Programme Archetypes</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archetype</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NFQ Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Awarding Body</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Configuration</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programme Usage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($archetypes as $archetype)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @switch($archetype->code)
                                                @case('QQI5')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-3">QQI5</span>
                                                    @break
                                                @case('QQI6')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-3">QQI6</span>
                                                    @break
                                                @case('DEGREE')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-3">DEGREE</span>
                                                    @break
                                                @case('FLEXIBLE')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-3">FLEXIBLE</span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-3">{{ $archetype->code }}</span>
                                            @endswitch
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $archetype->name }}</div>
                                                @if($archetype->description)
                                                    <div class="text-sm text-gray-500">{{ Str::limit($archetype->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $archetype->nfq_level }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $archetype->awarding_body }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="space-y-1">
                                            @if($archetype->defaultGradingScheme)
                                                <div><strong>G:</strong> {{ $archetype->defaultGradingScheme->name }}</div>
                                            @endif
                                            @if($archetype->defaultAssessmentStrategy)
                                                <div><strong>A:</strong> {{ $archetype->defaultAssessmentStrategy->name }}</div>
                                            @endif
                                            @if($archetype->defaultModuleProgressionRule)
                                                <div><strong>P:</strong> {{ $archetype->defaultModuleProgressionRule->name }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $archetype->programme_count }} total</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $archetype->active_programme_count }} active</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.archetypes.show', $archetype) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View
                                            </a>
                                            <a href="{{ route('admin.archetypes.edit', $archetype) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>