{{-- resources/views/programmes/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Programmes</h2>
                <p class="mt-1 text-sm text-slate-600">Manage programme blueprints and qualification frameworks</p>
            </div>
            @if(Auth::user()->role === 'manager')
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('programmes.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Create Programme
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="alert-circle" class="h-5 w-5 text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filter Panel -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-slate-900">Search & Filter</h3>
                        <button type="button" id="toggle-filters" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="filter" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <div id="filter-panel" class="p-6">
                    <form method="GET" action="{{ route('programmes.index') }}" class="space-y-4">
                        <!-- Search and Quick Filters Row -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
                                    </div>
                                    <input type="text" name="search" id="search" 
                                           value="{{ request('search') }}"
                                           class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-md leading-5 bg-white placeholder-slate-500 focus:outline-none focus:placeholder-slate-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                                           placeholder="Search by programme title or awarding body...">
                                </div>
                            </div>

                            <!-- NFQ Level Filter -->
                            <div>
                                <label for="nfq_level" class="block text-sm font-medium text-slate-700 mb-1">NFQ Level</label>
                                <select name="nfq_level" id="nfq_level" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Levels</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ request('nfq_level') == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Awarding Body Filter -->
                            <div>
                                <label for="awarding_body" class="block text-sm font-medium text-slate-700 mb-1">Awarding Body</label>
                                <select name="awarding_body" id="awarding_body" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Bodies</option>
                                    @foreach($awardingBodies as $body)
                                        <option value="{{ $body }}" {{ request('awarding_body') === $body ? 'selected' : '' }}>{{ $body }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Advanced Filters Row -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Credit Range -->
                            <div>
                                <label for="credit_range" class="block text-sm font-medium text-slate-700 mb-1">Total Credits</label>
                                <select name="credit_range" id="credit_range" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Credits</option>
                                    <option value="60-120" {{ request('credit_range') === '60-120' ? 'selected' : '' }}>60-120 Credits</option>
                                    <option value="121-180" {{ request('credit_range') === '121-180' ? 'selected' : '' }}>121-180 Credits</option>
                                    <option value="181-240" {{ request('credit_range') === '181-240' ? 'selected' : '' }}>181-240 Credits</option>
                                    <option value="240+" {{ request('credit_range') === '240+' ? 'selected' : '' }}>240+ Credits</option>
                                </select>
                            </div>

                            <!-- Instance Count -->
                            <div>
                                <label for="instance_count" class="block text-sm font-medium text-slate-700 mb-1">Active Instances</label>
                                <select name="instance_count" id="instance_count" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Counts</option>
                                    <option value="none" {{ request('instance_count') === 'none' ? 'selected' : '' }}>No Instances</option>
                                    <option value="low" {{ request('instance_count') === 'low' ? 'selected' : '' }}>1-2 Instances</option>
                                    <option value="medium" {{ request('instance_count') === 'medium' ? 'selected' : '' }}>3-5 Instances</option>
                                    <option value="high" {{ request('instance_count') === 'high' ? 'selected' : '' }}>5+ Instances</option>
                                </select>
                            </div>

                            <!-- View Style -->
                            <div>
                                <label for="view_style" class="block text-sm font-medium text-slate-700 mb-1">View Style</label>
                                <select name="view_style" id="view_style" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="cards" {{ request('view_style') !== 'table' ? 'selected' : '' }}>Cards</option>
                                    <option value="table" {{ request('view_style') === 'table' ? 'selected' : '' }}>Table</option>
                                </select>
                            </div>

                            <!-- Sort Options -->
                            <div>
                                <label for="sort_by" class="block text-sm font-medium text-slate-700 mb-1">Sort By</label>
                                <select name="sort_by" id="sort_by" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="title" {{ request('sort_by') === 'title' ? 'selected' : '' }}>Title</option>
                                    <option value="nfq_level" {{ request('sort_by') === 'nfq_level' ? 'selected' : '' }}>NFQ Level</option>
                                    <option value="total_credits" {{ request('sort_by') === 'total_credits' ? 'selected' : '' }}>Total Credits</option>
                                    <option value="instances_count" {{ request('sort_by') === 'instances_count' ? 'selected' : '' }}>Instance Count</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                            <div class="flex space-x-3">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                                    Apply Filters
                                </button>
                                <a href="{{ route('programmes.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-300 border border-transparent rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-400 focus:outline-none focus:border-slate-400 focus:ring ring-slate-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                                    Clear
                                </a>
                            </div>
                            
                            <!-- Sort Direction -->
                            <div class="flex items-center space-x-2">
                                <label for="sort_direction" class="text-sm font-medium text-slate-700">Order:</label>
                                <select name="sort_direction" id="sort_direction" class="border border-slate-300 rounded text-sm py-1 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>Ascending</option>
                                    <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>Descending</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Summary -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                <div class="px-6 py-4 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <h3 class="text-lg font-medium text-slate-900">
                                {{ $programmes->total() }} Programme{{ $programmes->total() !== 1 ? 's' : '' }}
                            </h3>
                            @if(request()->anyFilled(['search', 'nfq_level', 'awarding_body', 'credit_range', 'instance_count']))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Filtered
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @if(request('view_style') === 'table')
                    <!-- Table View -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Programme
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Qualification
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Credits & Level
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Active Instances
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse ($programmes as $programme)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-slate-900">
                                                        <a href="{{ route('programmes.show', $programme) }}" class="text-blue-600 hover:text-blue-900">
                                                            {{ $programme->title }}
                                                        </a>
                                                    </div>
                                                    @if($programme->description)
                                                        <div class="text-sm text-slate-500">{{ Str::limit($programme->description, 60) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                            <div class="font-medium">{{ $programme->awarding_body }}</div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                            <div><strong>{{ $programme->total_credits }}</strong> credits</div>
                                            <div class="text-slate-500">NFQ Level {{ $programme->nfq_level }}</div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-2xl font-bold text-slate-900">{{ $programme->programme_instances_count ?? 0 }}</span>
                                                <span class="text-xs text-slate-500 ml-1">instances</span>
                                            </div>
                                            @if($programme->programme_instances_count > 0)
                                                <a href="{{ route('programme-instances.index', ['programme_id' => $programme->id]) }}" 
                                                   class="text-xs text-blue-600 hover:text-blue-900">View instances</a>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('programmes.show', $programme) }}" 
                                                   class="text-blue-600 hover:text-blue-900" title="View Details">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                                
                                                @if(Auth::user()->role === 'manager')
                                                    <a href="{{ route('programmes.edit', $programme) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                                    </a>
                                                    
                                                    <button type="button" 
                                                            onclick="confirmDelete('{{ $programme->id }}', '{{ $programme->title }}')"
                                                            class="text-red-600 hover:text-red-900" title="Delete">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <i data-lucide="graduation-cap" class="w-12 h-12 text-slate-400 mb-4"></i>
                                                <h3 class="text-lg font-medium text-slate-900 mb-2">No programmes found</h3>
                                                <p class="text-slate-500 mb-4">
                                                    @if(request()->anyFilled(['search', 'nfq_level', 'awarding_body', 'credit_range', 'instance_count']))
                                                        No programmes match your current filters.
                                                    @else
                                                        Create your first programme to start offering qualifications.
                                                    @endif
                                                </p>
                                                @if(!request()->anyFilled(['search', 'nfq_level', 'awarding_body', 'credit_range', 'instance_count']) && Auth::user()->role === 'manager')
                                                    <a href="{{ route('programmes.create') }}" 
                                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                        Create Programme
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Cards View -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse ($programmes as $programme)
                            <div class="bg-white overflow-hidden shadow-sm border border-slate-200 rounded-lg hover:shadow-md transition-shadow duration-200">
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-3">
                                        <h3 class="text-lg font-semibold text-slate-900 line-clamp-2">
                                            <a href="{{ route('programmes.show', $programme) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $programme->title }}
                                            </a>
                                        </h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2 flex-shrink-0">
                                            Level {{ $programme->nfq_level }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2 mb-4">
                                        <div class="flex items-center text-sm text-slate-600">
                                            <i data-lucide="award" class="w-4 h-4 mr-2 text-slate-400"></i>
                                            <span class="font-medium">{{ $programme->awarding_body }}</span>
                                        </div>
                                        <div class="flex items-center text-sm text-slate-600">
                                            <i data-lucide="credit-card" class="w-4 h-4 mr-2 text-slate-400"></i>
                                            <span><strong>{{ $programme->total_credits }}</strong> total credits</span>
                                        </div>
                                        <div class="flex items-center text-sm text-slate-600">
                                            <i data-lucide="calendar" class="w-4 h-4 mr-2 text-slate-400"></i>
                                            <span><strong>{{ $programme->programme_instances_count ?? 0 }}</strong> active instance{{ ($programme->programme_instances_count ?? 0) !== 1 ? 's' : '' }}</span>
                                        </div>
                                    </div>
                                    
                                    @if($programme->description)
                                        <p class="text-sm text-slate-500 mb-4 line-clamp-3">{{ $programme->description }}</p>
                                    @endif

                                    <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('programmes.show', $programme) }}" 
                                               class="inline-flex items-center text-xs font-medium text-blue-600 hover:text-blue-800">
                                                <i data-lucide="eye" class="w-3 h-3 mr-1"></i>
                                                View
                                            </a>
                                            @if($programme->programme_instances_count > 0)
                                                <a href="{{ route('programme-instances.index', ['programme_id' => $programme->id]) }}" 
                                                   class="inline-flex items-center text-xs font-medium text-green-600 hover:text-green-800">
                                                    <i data-lucide="calendar" class="w-3 h-3 mr-1"></i>
                                                    Instances
                                                </a>
                                            @endif
                                        </div>
                                        
                                        @if(Auth::user()->role === 'manager')
                                            <div class="flex space-x-1">
                                                <a href="{{ route('programmes.edit', $programme) }}" 
                                                   class="p-1 text-indigo-600 hover:text-indigo-900" title="Edit">
                                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                                </a>
                                                
                                                <button type="button" 
                                                        onclick="confirmDelete('{{ $programme->id }}', '{{ $programme->title }}')"
                                                        class="p-1 text-red-600 hover:text-red-900" title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <div class="flex flex-col items-center">
                                    <i data-lucide="graduation-cap" class="w-12 h-12 text-slate-400 mb-4"></i>
                                    <h3 class="text-lg font-medium text-slate-900 mb-2">No programmes found</h3>
                                    <p class="text-slate-500 mb-4">
                                        @if(request()->anyFilled(['search', 'nfq_level', 'awarding_body', 'credit_range', 'instance_count']))
                                            No programmes match your current filters.
                                        @else
                                            Create your first programme to start offering qualifications.
                                        @endif
                                    </p>
                                    @if(!request()->anyFilled(['search', 'nfq_level', 'awarding_body', 'credit_range', 'instance_count']) && Auth::user()->role === 'manager')
                                        <a href="{{ route('programmes.create') }}" 
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            Create Programme
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>
                @endif
                
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $programmes->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-slate-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600"></i>
                </div>
                <div class="mt-5 text-center">
                    <h3 class="text-lg font-medium text-slate-900">Delete Programme</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-slate-500">
                            Are you sure you want to delete programme "<span id="deleteProgrammeName"></span>"? 
                            This action cannot be undone.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-4 px-4 py-3">
                        <button id="cancelDelete" 
                                class="px-4 py-2 bg-slate-300 text-slate-800 text-base font-medium rounded-md shadow-sm hover:bg-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-300">
                            Cancel
                        </button>
                        <form id="deleteForm" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Toggle filter panel
        document.getElementById('toggle-filters').addEventListener('click', function() {
            const panel = document.getElementById('filter-panel');
            panel.classList.toggle('hidden');
        });

        // Delete confirmation
        function confirmDelete(programmeId, programmeName) {
            document.getElementById('deleteProgrammeName').textContent = programmeName;
            document.getElementById('deleteForm').action = `/programmes/${programmeId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        document.getElementById('cancelDelete').addEventListener('click', function() {
            document.getElementById('deleteModal').classList.add('hidden');
        });

        // Auto-submit form when sort options or view style change
        document.getElementById('sort_by').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('sort_direction').addEventListener('change', function() {
            this.form.submit();
        });

        document.getElementById('view_style').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</x-app-layout>