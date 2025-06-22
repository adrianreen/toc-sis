{{-- resources/views/programme-instances/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Programme Instances</h2>
                <p class="mt-1 text-sm text-slate-600">Manage live programme deliveries and student intakes</p>
            </div>
            @if(Auth::user()->role === 'manager')
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('programme-instances.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Create Programme Instance
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

            <!-- Filter Panel -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-slate-900">Filters & Search</h3>
                        <button type="button" id="toggle-filters" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="filter" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <div id="filter-panel" class="p-6">
                    <form method="GET" action="{{ route('programme-instances.index') }}" class="space-y-4">
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
                                           placeholder="Search by programme title or instance label...">
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                <select name="status" id="status" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Statuses</option>
                                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Intake</option>
                                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Intake Closed</option>
                                </select>
                            </div>

                            <!-- Delivery Style Filter -->
                            <div>
                                <label for="delivery_style" class="block text-sm font-medium text-slate-700 mb-1">Delivery Style</label>
                                <select name="delivery_style" id="delivery_style" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Styles</option>
                                    <option value="sync" {{ request('delivery_style') === 'sync' ? 'selected' : '' }}>Synchronous</option>
                                    <option value="async" {{ request('delivery_style') === 'async' ? 'selected' : '' }}>Asynchronous</option>
                                </select>
                            </div>
                        </div>

                        <!-- Advanced Filters Row -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Programme Filter -->
                            <div>
                                <label for="programme_id" class="block text-sm font-medium text-slate-700 mb-1">Programme</label>
                                <select name="programme_id" id="programme_id" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Programmes</option>
                                    @foreach($programmes as $programme)
                                        <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                            {{ $programme->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Enrolment Level Filter -->
                            <div>
                                <label for="enrolment_level" class="block text-sm font-medium text-slate-700 mb-1">Enrolment Level</label>
                                <select name="enrolment_level" id="enrolment_level" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Levels</option>
                                    <option value="high" {{ request('enrolment_level') === 'high' ? 'selected' : '' }}>High Enrolments (20+)</option>
                                    <option value="medium" {{ request('enrolment_level') === 'medium' ? 'selected' : '' }}>Medium Enrolments (5-19)</option>
                                    <option value="low" {{ request('enrolment_level') === 'low' ? 'selected' : '' }}>Low Enrolments (1-4)</option>
                                    <option value="none" {{ request('enrolment_level') === 'none' ? 'selected' : '' }}>No Enrolments</option>
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div>
                                <label for="intake_start_from" class="block text-sm font-medium text-slate-700 mb-1">Intake Start From</label>
                                <input type="date" name="intake_start_from" id="intake_start_from" 
                                       value="{{ request('intake_start_from') }}"
                                       class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="intake_start_to" class="block text-sm font-medium text-slate-700 mb-1">Intake Start To</label>
                                <input type="date" name="intake_start_to" id="intake_start_to" 
                                       value="{{ request('intake_start_to') }}"
                                       class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Filter Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                            <div class="flex space-x-3">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                                    Apply Filters
                                </button>
                                <a href="{{ route('programme-instances.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-300 border border-transparent rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-400 focus:outline-none focus:border-slate-400 focus:ring ring-slate-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                                    Clear
                                </a>
                            </div>
                            
                            <!-- Sorting -->
                            <div class="flex items-center space-x-2">
                                <label for="sort_by" class="text-sm font-medium text-slate-700">Sort by:</label>
                                <select name="sort_by" id="sort_by" class="border border-slate-300 rounded text-sm py-1 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="intake_start_date" {{ request('sort_by') === 'intake_start_date' ? 'selected' : '' }}>Intake Start</option>
                                    <option value="programme_title" {{ request('sort_by') === 'programme_title' ? 'selected' : '' }}>Programme Title</option>
                                    <option value="label" {{ request('sort_by') === 'label' ? 'selected' : '' }}>Instance Label</option>
                                    <option value="enrolments_count" {{ request('sort_by') === 'enrolments_count' ? 'selected' : '' }}>Enrolment Count</option>
                                </select>
                                <select name="sort_direction" class="border border-slate-300 rounded text-sm py-1 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>Descending</option>
                                    <option value="asc" {{ request('sort_direction') === 'asc' ? 'selected' : '' }}>Ascending</option>
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
                                {{ $instances->total() }} Programme Instance{{ $instances->total() !== 1 ? 's' : '' }}
                            </h3>
                            @if(request()->anyFilled(['search', 'status', 'delivery_style', 'programme_id', 'enrolment_level', 'intake_start_from', 'intake_start_to']))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Filtered
                                </span>
                            @endif
                        </div>
                        
                        <!-- View Toggle -->
                        <div class="flex items-center space-x-2">
                            <button type="button" id="grid-view" class="p-1 text-slate-400 hover:text-slate-600">
                                <i data-lucide="grid" class="w-5 h-5"></i>
                            </button>
                            <button type="button" id="table-view" class="p-1 text-slate-600 hover:text-slate-800">
                                <i data-lucide="list" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div id="table-content" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Programme
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Instance Label
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Intake Period
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Delivery Style
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Enrolments
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse ($instances as $instance)
                                @php
                                    $now = now();
                                    $status = 'upcoming';
                                    $statusColor = 'blue';
                                    
                                    if ($instance->intake_start_date <= $now) {
                                        if (!$instance->intake_end_date || $instance->intake_end_date >= $now) {
                                            $status = 'active';
                                            $statusColor = 'green';
                                        } else {
                                            $status = 'closed';
                                            $statusColor = 'slate';
                                        }
                                    }
                                @endphp
                                
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-slate-900">
                                                    <a href="{{ route('programmes.show', $instance->programme) }}" class="text-blue-600 hover:text-blue-900">
                                                        {{ $instance->programme->title }}
                                                    </a>
                                                </div>
                                                <div class="text-sm text-slate-500">
                                                    {{ $instance->programme->awarding_body }} • NQF {{ $instance->programme->nfq_level }}
                                                </div>
                                                <div class="text-xs text-slate-400">{{ $instance->programme->total_credits }} credits</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900">{{ $instance->label }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        <div><strong>Start:</strong> {{ $instance->intake_start_date->format('d M Y') }}</div>
                                        @if($instance->intake_end_date)
                                            <div><strong>End:</strong> {{ $instance->intake_end_date->format('d M Y') }}</div>
                                        @else
                                            <div class="text-slate-500 italic">Open intake</div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($instance->default_delivery_style === 'sync') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800 @endif">
                                            @if($instance->default_delivery_style === 'sync') 
                                                <i data-lucide="users" class="w-3 h-3 mr-1"></i>Synchronous
                                            @else 
                                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i>Asynchronous
                                            @endif
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-2xl font-bold text-slate-900">{{ $instance->enrolments_count ?? 0 }}</span>
                                            <span class="text-xs text-slate-500 ml-1">students</span>
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $instance->moduleInstances->count() ?? 0 }} modules</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('programme-instances.show', $instance) }}" 
                                               class="text-blue-600 hover:text-blue-900" title="View Details">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            
                                            <a href="{{ route('programme-instances.curriculum', $instance) }}" 
                                               class="text-green-600 hover:text-green-900" title="Manage Curriculum">
                                                <i data-lucide="book-open" class="w-4 h-4"></i>
                                            </a>
                                            
                                            @if(Auth::user()->role === 'manager')
                                                <a href="{{ route('programme-instances.edit', $instance) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                                </a>
                                                
                                                <button type="button" 
                                                        onclick="confirmDelete('{{ $instance->id }}', '{{ $instance->label }}')"
                                                        class="text-red-600 hover:text-red-900" title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i data-lucide="graduation-cap" class="w-12 h-12 text-slate-400 mb-4"></i>
                                            <h3 class="text-lg font-medium text-slate-900 mb-2">No programme instances found</h3>
                                            <p class="text-slate-500 mb-4">
                                                @if(request()->anyFilled(['search', 'status', 'delivery_style', 'programme_id', 'enrolment_level', 'intake_start_from', 'intake_start_to']))
                                                    No programme instances match your current filters.
                                                @else
                                                    Create your first programme instance to start managing student intakes.
                                                @endif
                                            </p>
                                            @if(!request()->anyFilled(['search', 'status', 'delivery_style', 'programme_id', 'enrolment_level', 'intake_start_from', 'intake_start_to']))
                                                <a href="{{ route('programme-instances.create') }}" 
                                                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                    Create Programme Instance
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $instances->links() }}
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
                    <h3 class="text-lg font-medium text-slate-900">Delete Programme Instance</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-slate-500">
                            Are you sure you want to delete "<span id="deleteInstanceName"></span>"? 
                            This will remove all curriculum links and cannot be undone.
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
        function confirmDelete(instanceId, instanceName) {
            document.getElementById('deleteInstanceName').textContent = instanceName;
            document.getElementById('deleteForm').action = `/programme-instances/${instanceId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        document.getElementById('cancelDelete').addEventListener('click', function() {
            document.getElementById('deleteModal').classList.add('hidden');
        });

        // View toggle (placeholder for future implementation)
        document.getElementById('grid-view').addEventListener('click', function() {
            // Future: Switch to grid view
            this.classList.add('text-slate-600');
            this.classList.remove('text-slate-400');
            document.getElementById('table-view').classList.add('text-slate-400');
            document.getElementById('table-view').classList.remove('text-slate-600');
        });

        document.getElementById('table-view').addEventListener('click', function() {
            // Current: Table view is default
            this.classList.add('text-slate-600');
            this.classList.remove('text-slate-400');
            document.getElementById('grid-view').classList.add('text-slate-400');
            document.getElementById('grid-view').classList.remove('text-slate-600');
        });

        // Auto-submit form when sort options change
        document.getElementById('sort_by').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.querySelector('select[name="sort_direction"]').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</x-app-layout>