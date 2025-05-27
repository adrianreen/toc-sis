{{-- resources/views/assessment-components/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Assessment Components: {{ $module->code }} - {{ $module->title }}
            </h2>
            <div class="space-x-2">
                @if(Auth::user()->role === 'manager')
                    <a href="{{ route('assessment-components.create', $module) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Component
                    </a>
                @endif
                <a href="{{ route('modules.show', $module) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Module
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

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Weight Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Total Weight</h3>
                            <p class="text-sm text-gray-600">Sum of all component weights</p>
                        </div>
                        <div class="text-3xl font-bold {{ $totalWeight == 100 ? 'text-green-600' : ($totalWeight > 100 ? 'text-red-600' : 'text-yellow-600') }}">
                            {{ $totalWeight }}%
                        </div>
                    </div>
                    @if($totalWeight != 100)
                        <div class="mt-4">
                            <p class="text-sm {{ $totalWeight > 100 ? 'text-red-600' : 'text-yellow-600' }}">
                                @if($totalWeight > 100)
                                    Warning: Total weight exceeds 100%. Please adjust component weights.
                                @else
                                    Note: Total weight is less than 100%. You need {{ 100 - $totalWeight }}% more.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Components List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($components->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200" id="components-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sequence
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Weight
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    @if(Auth::user()->role === 'manager')
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($components as $component)
                                    <tr data-id="{{ $component->id }}" class="component-row">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="sequence-display">{{ $component->sequence }}</span>
                                            @if(Auth::user()->role === 'manager' && $components->count() > 1)
                                                <span class="handle cursor-move ml-2 text-gray-400 hover:text-gray-600">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                                    </svg>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $component->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($component->type === 'exam') bg-red-100 text-red-800
                                                @elseif($component->type === 'assignment') bg-blue-100 text-blue-800
                                                @elseif($component->type === 'project') bg-green-100 text-green-800
                                                @elseif($component->type === 'presentation') bg-purple-100 text-purple-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($component->type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-semibold">{{ $component->weight }}%</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $component->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $component->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        @if(Auth::user()->role === 'manager')
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('assessment-components.edit', [$module, $component]) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    Edit
                                                </a>
                                                @if(!$component->studentAssessments()->exists())
                                                    <form action="{{ route('assessment-components.destroy', [$module, $component]) }}" 
                                                          method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                onclick="return confirm('Are you sure you want to delete this component?')"
                                                                class="text-red-600 hover:text-red-900">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400" title="Cannot delete - has student assessments">
                                                        Delete
                                                    </span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500 text-center py-8">
                            No assessment components defined for this module yet.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Usage Information -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Note:</strong> Assessment components define how students will be evaluated in this module. 
                            The total weight should equal 100%. Components with student assessments cannot be deleted.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->role === 'manager' && $components->count() > 1)
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            // Make the table sortable
            const tbody = document.querySelector('#components-table tbody');
            new Sortable(tbody, {
                handle: '.handle',
                animation: 150,
                onEnd: function(evt) {
                    const rows = tbody.querySelectorAll('tr');
                    const components = [];
                    
                    rows.forEach((row, index) => {
                        const sequence = index + 1;
                        row.querySelector('.sequence-display').textContent = sequence;
                        components.push({
                            id: row.dataset.id,
                            sequence: sequence
                        });
                    });
                    
                    // Send update to server
                    fetch(`{{ route('assessment-components.reorder', $module) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ components: components })
                    });
                }
            });
        </script>
    @endif
</x-app-layout>