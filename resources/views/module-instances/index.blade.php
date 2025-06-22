{{-- resources/views/module-instances/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Module Instances</h2>
                <p class="mt-1 text-sm text-slate-600">Manage and track live module deliveries</p>
            </div>
            @if(Auth::user()->role === 'manager')
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('module-instances.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Create Module Instance
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
                    <form method="GET" action="{{ route('module-instances.index') }}" class="space-y-4">
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
                                           placeholder="Search by module title or code...">
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                <select name="status" id="status" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Statuses</option>
                                    <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="no_tutor" {{ request('status') === 'no_tutor' ? 'selected' : '' }}>No Tutor Assigned</option>
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
                            <!-- Tutor Filter -->
                            <div>
                                <label for="tutor_id" class="block text-sm font-medium text-slate-700 mb-1">Tutor</label>
                                <select name="tutor_id" id="tutor_id" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Tutors</option>
                                    @foreach($tutors as $tutor)
                                        <option value="{{ $tutor->id }}" {{ request('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                            {{ $tutor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Programme Association -->
                            <div>
                                <label for="programme_association" class="block text-sm font-medium text-slate-700 mb-1">Programme Link</label>
                                <select name="programme_association" id="programme_association" class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Types</option>
                                    <option value="programme_linked" {{ request('programme_association') === 'programme_linked' ? 'selected' : '' }}>Programme Linked</option>
                                    <option value="standalone" {{ request('programme_association') === 'standalone' ? 'selected' : '' }}>Standalone Only</option>
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div>
                                <label for="start_date_from" class="block text-sm font-medium text-slate-700 mb-1">Start Date From</label>
                                <input type="date" name="start_date_from" id="start_date_from" 
                                       value="{{ request('start_date_from') }}"
                                       class="block w-full border border-slate-300 rounded-md py-2 px-3 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="start_date_to" class="block text-sm font-medium text-slate-700 mb-1">Start Date To</label>
                                <input type="date" name="start_date_to" id="start_date_to" 
                                       value="{{ request('start_date_to') }}"
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
                                <a href="{{ route('module-instances.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-300 border border-transparent rounded-md font-semibold text-xs text-slate-700 uppercase tracking-widest hover:bg-slate-400 focus:outline-none focus:border-slate-400 focus:ring ring-slate-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                                    Clear
                                </a>
                            </div>
                            
                            <!-- Sorting -->
                            <div class="flex items-center space-x-2">
                                <label for="sort_by" class="text-sm font-medium text-slate-700">Sort by:</label>
                                <select name="sort_by" id="sort_by" class="border border-slate-300 rounded text-sm py-1 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="start_date" {{ request('sort_by') === 'start_date' ? 'selected' : '' }}>Start Date</option>
                                    <option value="module_title" {{ request('sort_by') === 'module_title' ? 'selected' : '' }}>Module Title</option>
                                    <option value="tutor_name" {{ request('sort_by') === 'tutor_name' ? 'selected' : '' }}>Tutor Name</option>
                                    <option value="student_count" {{ request('sort_by') === 'student_count' ? 'selected' : '' }}>Student Count</option>
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
                                {{ $instances->total() }} Module Instance{{ $instances->total() !== 1 ? 's' : '' }}
                            </h3>
                            @if(request()->anyFilled(['search', 'status', 'delivery_style', 'tutor_id', 'programme_association', 'start_date_from', 'start_date_to']))
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
                                    Module
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Delivery Style
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Tutor
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Schedule
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Students
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
                                    
                                    if ($instance->start_date <= $now) {
                                        if (!$instance->target_end_date || $instance->target_end_date >= $now) {
                                            $status = 'active';
                                            $statusColor = 'green';
                                        } else {
                                            $status = 'completed';
                                            $statusColor = 'slate';
                                        }
                                    }
                                    
                                    $studentCount = $instance->studentGradeRecords->pluck('student_id')->unique()->count();
                                @endphp
                                
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-slate-900">
                                                    <a href="{{ route('modules.show', $instance->module) }}" class="text-blue-600 hover:text-blue-900">
                                                        {{ $instance->module->module_code }}
                                                    </a>
                                                </div>
                                                <div class="text-sm text-slate-500">{{ $instance->module->title }}</div>
                                                <div class="text-xs text-slate-400">{{ $instance->module->credit_value }} credits</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                            {{ ucfirst($status) }}
                                        </span>
                                        @if(!$instance->tutor_id)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 ml-1">
                                                No Tutor
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($instance->delivery_style === 'sync') bg-blue-100 text-blue-800
                                            @else bg-green-100 text-green-800 @endif">
                                            @if($instance->delivery_style === 'sync') 
                                                <i data-lucide="users" class="w-3 h-3 mr-1"></i>Synchronous
                                            @else 
                                                <i data-lucide="clock" class="w-3 h-3 mr-1"></i>Asynchronous
                                            @endif
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        @if($instance->tutor)
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-slate-600">
                                                            {{ substr($instance->tutor->name, 0, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-slate-900">{{ $instance->tutor->name }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Not assigned</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        <div class="text-sm text-slate-900">
                                            <div><strong>Start:</strong> {{ $instance->start_date ? $instance->start_date->format('d M Y') : 'TBD' }}</div>
                                            @if($instance->target_end_date)
                                                <div><strong>End:</strong> {{ $instance->target_end_date->format('d M Y') }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-2xl font-bold text-slate-900">{{ $studentCount }}</span>
                                            <span class="text-xs text-slate-500 ml-1">enrolled</span>
                                        </div>
                                        @if($instance->programmeInstances->count() > 0)
                                            <div class="text-xs text-blue-600">{{ $instance->programmeInstances->count() }} programme(s)</div>
                                        @else
                                            <div class="text-xs text-green-600">Standalone</div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('module-instances.show', $instance) }}" 
                                               class="text-blue-600 hover:text-blue-900" title="View Details">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            
                                            @if(Auth::user()->role === 'manager')
                                                <a href="{{ route('module-instances.edit', $instance) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                                </a>
                                                
                                                @if($studentCount > 0)
                                                    <a href="{{ route('grade-records.module-grading', $instance) }}" 
                                                       class="text-green-600 hover:text-green-900" title="Manage Grades">
                                                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                                                    </a>
                                                @endif
                                                
                                                <button type="button" 
                                                        onclick="confirmDelete('{{ $instance->id }}', '{{ $instance->module->title }}')"
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
                                            <i data-lucide="book-open" class="w-12 h-12 text-slate-400 mb-4"></i>
                                            <h3 class="text-lg font-medium text-slate-900 mb-2">No module instances found</h3>
                                            <p class="text-slate-500 mb-4">
                                                @if(request()->anyFilled(['search', 'status', 'delivery_style', 'tutor_id', 'programme_association', 'start_date_from', 'start_date_to']))
                                                    No module instances match your current filters.
                                                @else
                                                    Create your first module instance to start tracking module deliveries.
                                                @endif
                                            </p>
                                            @if(!request()->anyFilled(['search', 'status', 'delivery_style', 'tutor_id', 'programme_association', 'start_date_from', 'start_date_to']))
                                                <a href="{{ route('module-instances.create') }}" 
                                                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                    Create Module Instance
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
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-slate-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i data-lucide="alert-triangle" class="h-6 w-6 text-red-600"></i>
                </div>
                <div class="mt-5 text-center">
                    <h3 class="text-lg font-medium text-slate-900">Delete Module Instance</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-slate-500">
                            Are you sure you want to delete "<span id="deleteModuleName"></span>"? 
                            This will remove all student enrolments and grade records for this instance.
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
        function confirmDelete(instanceId, moduleName) {
            document.getElementById('deleteModuleName').textContent = moduleName;
            document.getElementById('deleteForm').action = `/module-instances/${instanceId}`;
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