{{-- resources/views/students/index.blade.php --}}
<x-wide-layout title="Students" subtitle="Manage student records and enrolments">
    <x-slot name="actions">
        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
            <x-button href="{{ route('students.recycle-bin') }}" variant="secondary" size="sm">
                🗑️ Recycle Bin
            </x-button>
        @endif
        <x-button href="{{ route('students.create') }}" variant="primary" class="!bg-blue-600 !text-white hover:!bg-blue-700 cursor-pointer">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Student
        </x-button>
    </x-slot>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Enhanced Student Management Interface -->
    <div x-data="studentManager()" class="space-y-4">
        
        <!-- Search and Filter Bar -->
        <x-card class="p-3">
            <form method="GET" action="{{ route('students.index') }}" class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search students by name, email, or student number..."
                            class="block w-full pl-10 pr-20 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer transition-all duration-200"
                            :class="loading ? 'bg-blue-50' : ''"
                            x-model="searchTerm"
                            @input="performFilter()"
                            autocomplete="off"
                        />
                        
                        <!-- Loading Spinner & Clear Button -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <!-- Loading Spinner -->
                            <div x-show="loading" class="mr-2">
                                <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            
                            <!-- Clear Button -->
                            <button 
                                type="button"
                                x-show="searchTerm.length > 0"
                                @click="clearSearch()"
                                class="p-1 rounded-full hover:bg-gray-100 transition-colors cursor-pointer"
                                title="Clear search"
                            >
                                <svg class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Search Suggestions/Status -->
                        <div x-show="searchTerm.length > 0 && searchTerm.length < 2" 
                             class="absolute top-full left-0 right-0 mt-1 bg-blue-50 border border-blue-200 rounded-lg p-2 text-xs text-blue-600 z-10">
                            <span>Type at least 2 characters to search...</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <button 
                            type="button"
                            class="flex items-center gap-2 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer"
                            @click="toggleFilters"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filters
                            <span x-show="hasActiveFilters" class="bg-blue-100 text-blue-800 text-xs px-1.5 py-0.5 rounded-full" x-text="activeFilterCount"></span>
                        </button>

                        <button 
                            type="button"
                            class="flex items-center gap-2 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer"
                            @click="exportStudents"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Export
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div x-show="showFilters" x-transition class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-gray-200">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select 
                            name="status" 
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer"
                            x-model="statusFilter"
                            @change="performFilter()"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="deferred">Deferred</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="enquiry">Enquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Programme</label>
                        <select 
                            name="programme" 
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer"
                            x-model="programmeFilter"
                            @change="performFilter()"
                        >
                            <option value="">All Programmes</option>
                            @foreach($programmes ?? [] as $programme)
                                <option value="{{ $programme->id }}">{{ $programme->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button 
                            type="button"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition cursor-pointer"
                            @click="clearAllFilters"
                        >
                            Clear All Filters
                        </button>
                    </div>
                </div>

                <!-- Active Filter Chips -->
                <div x-show="hasActiveFilters" class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm text-gray-500">Active filters:</span>
                    <template x-for="filter in activeFilters" :key="filter.key">
                        <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded-full">
                            <span x-text="filter.label"></span>
                            <button 
                                type="button"
                                class="text-blue-600 hover:text-blue-800 cursor-pointer"
                                @click="removeFilter(filter.key)"
                            >&times;</button>
                        </span>
                    </template>
                </div>
            </form>
        </x-card>

        <!-- Students Table -->
        <x-card padding="none" class="overflow-hidden">
            <!-- Table Controls -->
            <div class="px-3 py-2 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                x-model="selectAll"
                                @change="toggleSelectAll"
                            />
                            <span class="ml-2 text-sm text-gray-700">
                                Select all (<span x-text="students.length"></span>)
                            </span>
                        </label>
                        
                        <div x-show="selectedStudents.length > 0" class="text-sm text-blue-600">
                            <span x-text="selectedStudents.length"></span> selected
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span x-show="!loading">{{ $students->total() }} students</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                            Searching...
                        </span>
                        @if(request()->hasAny(['search', 'status', 'programme']))
                            <span class="text-blue-600">(filtered)</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="w-12 px-3 py-2">
                                <span class="sr-only">Select</span>
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" @click="sortBy('name')">
                                <div class="flex items-center gap-1">
                                    Student
                                    <svg class="w-4 h-4" :class="sortField === 'name' ? 'text-blue-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="m21 8-4-4-4 4"/><path d="M17 4v16"/>
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Programme
                            </th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700" @click="sortBy('created_at')">
                                <div class="flex items-center gap-1">
                                    Joined
                                    <svg class="w-4 h-4" :class="sortField === 'created_at' ? 'text-blue-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="m21 8-4-4-4 4"/><path d="M17 4v16"/>
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="relative px-4 py-2">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($students as $student)
                            <tr 
                                class="hover:bg-gray-50 transition-colors"
                                :class="selectedStudents.includes({{ $student->id }}) ? 'bg-blue-50' : ''"
                            >
                                <td class="px-3 py-3">
                                    <input 
                                        type="checkbox" 
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                        :checked="selectedStudents.includes({{ $student->id }})"
                                        @change="toggleStudent({{ $student->id }})"
                                    />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-sm font-semibold">
                                            {{ collect(explode(' ', $student->full_name))->map(fn($n) => $n[0])->take(2)->implode('') }}
                                        </div>
                                        <div class="ml-4">
                                            <a 
                                                href="{{ route('students.show', $student) }}" 
                                                class="text-sm font-semibold text-blue-600 hover:text-blue-700 cursor-pointer block"
                                                title="{{ $student->full_name }}"
                                            >
                                                {{ Str::limit($student->full_name, 30) }}
                                            </a>
                                            <div class="text-sm text-gray-500 truncate">{{ $student->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-status-badge 
                                        :status="$student->status" 
                                        variant="subtle" 
                                        size="sm"
                                        icon="auto"
                                    >
                                        {{ ucfirst($student->status) }}
                                    </x-status-badge>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($student->enrolments->where('enrolment_type', 'programme')->isNotEmpty())
                                            {{ $student->enrolments->where('enrolment_type', 'programme')->first()->programmeInstance->programme->title ?? 'N/A' }}
                                        @else
                                            <span class="text-gray-400 italic">No programme</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $student->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center gap-2 justify-end">
                                        <a 
                                            href="{{ route('students.show', $student) }}" 
                                            class="text-blue-600 hover:text-blue-700 text-sm font-medium cursor-pointer"
                                        >
                                            View
                                        </a>
                                        <a 
                                            href="{{ route('students.edit', $student) }}" 
                                            class="text-gray-600 hover:text-gray-700 text-sm font-medium cursor-pointer"
                                        >
                                            Edit
                                        </a>
                                        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                                            <form method="POST" action="{{ route('students.destroy', $student) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete {{ $student->full_name }}?')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium cursor-pointer">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            @if(request()->hasAny(['search', 'status', 'programme']))
                                                Try adjusting your search or filters.
                                            @else
                                                Get started by adding a new student.
                                            @endif
                                        </p>
                                        @if(!request()->hasAny(['search', 'status', 'programme']) && in_array(Auth::user()->role, ['manager', 'student_services']))
                                            <div class="mt-6">
                                                <x-button href="{{ route('students.create') }}" variant="primary">
                                                    Add New Student
                                                </x-button>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden divide-y divide-gray-200">
                @foreach($students as $student)
                    <div 
                        class="p-3 hover:bg-gray-50 transition-colors"
                        :class="selectedStudents.includes({{ $student->id }}) ? 'bg-blue-50' : ''"
                    >
                        <div class="flex items-start gap-3">
                            <input 
                                type="checkbox" 
                                class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                :checked="selectedStudents.includes({{ $student->id }})"
                                @change="toggleStudent({{ $student->id }})"
                            />
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                {{ collect(explode(' ', $student->full_name))->map(fn($n) => $n[0])->take(2)->implode('') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="min-w-0 flex-1">
                                        <a 
                                            href="{{ route('students.show', $student) }}" 
                                            class="text-sm font-semibold text-blue-600 hover:text-blue-700 cursor-pointer block truncate"
                                            title="{{ $student->full_name }}"
                                            @click.stop
                                        >
                                            {{ Str::limit($student->full_name, 25) }}
                                        </a>
                                        <p class="text-sm text-gray-500 truncate">{{ $student->email }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 ml-2 flex-shrink-0">
                                        <x-status-badge 
                                            :status="$student->status" 
                                            variant="subtle" 
                                            size="xs"
                                            icon="auto"
                                        >
                                            {{ ucfirst($student->status) }}
                                        </x-status-badge>
                                        <a 
                                            href="{{ route('students.show', $student) }}" 
                                            class="text-blue-600 hover:text-blue-700 text-xs font-medium cursor-pointer"
                                            @click.stop
                                        >
                                            View
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-sm text-gray-600 truncate">
                                        @if($student->enrolments->where('enrolment_type', 'programme')->isNotEmpty())
                                            {{ Str::limit($student->enrolments->where('enrolment_type', 'programme')->first()->programmeInstance->programme->title ?? 'N/A', 25) }}
                                        @else
                                            <span class="text-gray-400 italic">No programme</span>
                                        @endif
                                    </p>
                                    <span class="text-xs text-gray-400 ml-2 flex-shrink-0">
                                        {{ $student->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
                <div class="bg-white px-3 py-2 border-t border-gray-200 sm:px-4">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            @endif
        </x-card>

        <!-- Bulk Actions Bar (Sticky) -->
        <div 
            x-show="selectedStudents.length > 0" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-full"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-full"
            class="fixed bottom-0 left-0 right-0 z-50"
        >
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-4">
                <div class="bg-slate-800 text-white rounded-lg shadow-2xl p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                            <span class="font-medium text-white">
                                <span x-text="selectedStudents.length"></span> students selected
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <button 
                            class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition cursor-pointer"
                            @click="bulkAction('status')"
                        >
                            Change Status
                        </button>
                        <button 
                            class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 transition cursor-pointer"
                            @click="bulkAction('email')"
                        >
                            Send Email
                        </button>
                        <button 
                            class="px-3 py-1.5 text-sm bg-purple-600 text-white rounded-md hover:bg-purple-700 transition cursor-pointer"
                            @click="bulkAction('export')"
                        >
                            Export Selected
                        </button>
                        <div class="hidden sm:block h-6 w-px bg-slate-600"></div>
                        <button 
                            class="text-sm text-slate-300 hover:text-white transition cursor-pointer underline"
                            @click="clearSelection"
                        >
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function studentManager() {
            return {
                // Search and filters
                searchTerm: '{{ request('search', '') }}',
                statusFilter: '{{ request('status', '') }}',
                programmeFilter: '{{ request('programme', '') }}',
                showFilters: false,
                
                // Sorting
                sortField: '{{ request('sort', 'created_at') }}',
                sortDirection: '{{ request('direction', 'desc') }}',
                
                // Selection
                selectedStudents: [],
                selectAll: false,
                
                // Performance
                searchTimeout: null,
                debounceTimer: null,
                loading: false,
                
                // Data
                students: @json($students->items()),
                
                init() {
                    // Initialize filters from URL
                    this.showFilters = this.hasActiveFilters;
                    
                    // Add keyboard shortcuts
                    document.addEventListener('keydown', (e) => {
                        if (e.ctrlKey || e.metaKey) {
                            switch (e.key) {
                                case 'a':
                                    if (e.target.tagName !== 'INPUT') {
                                        e.preventDefault();
                                        this.selectAll = true;
                                        this.toggleSelectAll();
                                    }
                                    break;
                                case 'k':
                                    e.preventDefault();
                                    document.querySelector('input[name="search"]').focus();
                                    break;
                            }
                        }
                        if (e.key === 'Escape') {
                            this.clearSelection();
                        }
                    });
                },
                
                // Computed properties
                get hasActiveFilters() {
                    return this.searchTerm || this.statusFilter || this.programmeFilter;
                },
                
                get activeFilterCount() {
                    let count = 0;
                    if (this.statusFilter) count++;
                    if (this.programmeFilter) count++;
                    return count;
                },
                
                get activeFilters() {
                    const filters = [];
                    if (this.statusFilter) {
                        filters.push({ key: 'status', label: `Status: ${this.statusFilter}` });
                    }
                    if (this.programmeFilter) {
                        filters.push({ key: 'programme', label: 'Programme: Selected' });
                    }
                    return filters;
                },
                
                // Search functionality
                performFilter() {
                    // Clear previous timer
                    if (this.debounceTimer) {
                        clearTimeout(this.debounceTimer);
                    }
                    
                    // For search, wait for at least 2 characters or if clearing
                    if (this.searchTerm.length > 0 && this.searchTerm.length < 2) {
                        return;
                    }
                    
                    // Debounce search input, instant for dropdowns
                    const delay = this.searchTerm !== '{{ request('search') }}' ? 300 : 0;
                    
                    this.debounceTimer = setTimeout(() => {
                        this.performTableFilter();
                    }, delay);
                },
                
                clearSearch() {
                    this.searchTerm = '';
                    this.performFilter();
                },
                
                async performTableFilter() {
                    // If no filters, reload the page to show all results
                    if (this.searchTerm.length === 0 && this.statusFilter === '' && this.programmeFilter === '') {
                        window.location.href = '{{ route('students.index') }}';
                        return;
                    }
                    
                    this.loading = true;
                    
                    try {
                        const params = new URLSearchParams();
                        if (this.searchTerm.length >= 2) params.append('search', this.searchTerm);
                        if (this.statusFilter) params.append('status', this.statusFilter);
                        if (this.programmeFilter) params.append('programme', this.programmeFilter);
                        
                        console.log('Making search request:', `/api/students/search?${params.toString()}&limit=100`);
                        
                        const response = await fetch(`/api/students/search?${params.toString()}&limit=100`);
                        console.log('Response status:', response.status);
                        
                        if (response.ok) {
                            const data = await response.json();
                            console.log('Search results:', data);
                            this.updateTable(data.students, data.has_more);
                            this.updateResultsCount(data.total, data.showing, data.has_more);
                        } else {
                            console.error('Search failed:', response.statusText);
                            // Show the actual table content updates
                            this.updateURL();
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        // Show the actual table content updates  
                        this.updateURL();
                    } finally {
                        this.loading = false;
                    }
                },
                
                updateTable(students, hasMore = false) {
                    // Update the students data for the frontend
                    this.students = students;
                    
                    // Clear any selections since we have new data
                    this.selectedStudents = [];
                    this.selectAll = false;
                    
                    // Update the actual table content
                    this.renderTableRows(students);
                    this.renderMobileCards(students);
                },
                
                renderTableRows(students) {
                    const tbody = document.querySelector('.hidden.md\\:block tbody');
                    if (!tbody) return;
                    
                    if (students.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
                                        <p class="mt-1 text-sm text-gray-500">No students match "${this.searchTerm}"</p>
                                        <button onclick="this.closest('[x-data]').__x.$data.clearSearch()" 
                                                class="mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium cursor-pointer">
                                            Clear search and view all students
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    tbody.innerHTML = students.map(student => this.renderStudentRow(student)).join('');
                },
                
                renderStudentRow(student) {
                    const initials = student.full_name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                    const programmes = student.programmes && student.programmes.length > 0 
                        ? student.programmes.join(', ')
                        : 'No programme';
                    
                    return `
                        <tr class="hover:bg-gray-50 transition-colors" 
                            :class="selectedStudents.includes(${student.id}) ? 'bg-blue-50' : ''">
                            <td class="px-3 py-3">
                                <input 
                                    type="checkbox" 
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                    :checked="selectedStudents.includes(${student.id})"
                                    @change="toggleStudent(${student.id})"
                                />
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-sm font-semibold">
                                        ${initials}
                                    </div>
                                    <div class="ml-4">
                                        <a 
                                            href="/students/${student.id}" 
                                            class="text-sm font-semibold text-blue-600 hover:text-blue-700 cursor-pointer block"
                                            title="${student.full_name}"
                                        >
                                            ${student.full_name.length > 30 ? student.full_name.substring(0, 30) + '...' : student.full_name}
                                        </a>
                                        <div class="text-sm text-gray-500 truncate">${student.email}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${this.getStatusClasses(student.status)}">
                                    ${student.status.charAt(0).toUpperCase() + student.status.slice(1)}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    ${programmes.length > 50 ? programmes.substring(0, 50) + '...' : programmes}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                ${student.created_at}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="/students/${student.id}" class="text-blue-600 hover:text-blue-700 text-sm font-medium cursor-pointer">View</a>
                                    <a href="/students/${student.id}/edit" class="text-gray-600 hover:text-gray-700 text-sm font-medium cursor-pointer">Edit</a>
                                </div>
                            </td>
                        </tr>
                    `;
                },
                
                getStatusClasses(status) {
                    const classes = {
                        'active': 'bg-green-100 text-green-800',
                        'enrolled': 'bg-blue-100 text-blue-800',
                        'deferred': 'bg-yellow-100 text-yellow-800',
                        'completed': 'bg-purple-100 text-purple-800',
                        'cancelled': 'bg-red-100 text-red-800',
                        'enquiry': 'bg-gray-100 text-gray-700'
                    };
                    return classes[status] || 'bg-gray-100 text-gray-700';
                },
                
                renderMobileCards(students) {
                    const mobileContainer = document.querySelector('.md\\:hidden');
                    if (!mobileContainer) return;
                    
                    if (students.length === 0) {
                        mobileContainer.innerHTML = `
                            <div class="p-3 text-center">
                                <p class="text-gray-500">No students found matching "${this.searchTerm}"</p>
                                <button onclick="this.closest('[x-data]').__x.$data.clearSearch()" 
                                        class="mt-2 text-blue-600 hover:text-blue-700 cursor-pointer">
                                    Clear search
                                </button>
                            </div>
                        `;
                        return;
                    }
                    
                    mobileContainer.innerHTML = `
                        <div class="divide-y divide-gray-200">
                            ${students.map(student => this.renderMobileCard(student)).join('')}
                        </div>
                    `;
                },
                
                renderMobileCard(student) {
                    const initials = student.full_name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                    const programmes = student.programmes && student.programmes.length > 0 
                        ? student.programmes.join(', ')
                        : 'No programme';
                    
                    return `
                        <div class="p-3 hover:bg-gray-50 transition-colors" 
                             :class="selectedStudents.includes(${student.id}) ? 'bg-blue-50' : ''">
                            <div class="flex items-start gap-3">
                                <input 
                                    type="checkbox" 
                                    class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                    :checked="selectedStudents.includes(${student.id})"
                                    @change="toggleStudent(${student.id})"
                                />
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                                    ${initials}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="min-w-0 flex-1">
                                            <a 
                                                href="/students/${student.id}" 
                                                class="text-sm font-semibold text-blue-600 hover:text-blue-700 cursor-pointer block truncate"
                                                title="${student.full_name}"
                                            >
                                                ${student.full_name.length > 25 ? student.full_name.substring(0, 25) + '...' : student.full_name}
                                            </a>
                                            <p class="text-sm text-gray-500 truncate">${student.email}</p>
                                        </div>
                                        <div class="flex items-center gap-2 ml-2 flex-shrink-0">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${this.getStatusClasses(student.status)}">
                                                ${student.status.charAt(0).toUpperCase() + student.status.slice(1)}
                                            </span>
                                            <a href="/students/${student.id}" class="text-blue-600 hover:text-blue-700 text-xs font-medium cursor-pointer">View</a>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <p class="text-sm text-gray-600 truncate">
                                            ${programmes.length > 25 ? programmes.substring(0, 25) + '...' : programmes}
                                        </p>
                                        <span class="text-xs text-gray-400 ml-2 flex-shrink-0">
                                            ${student.created_at}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                },
                
                updateResultsCount(total, showing = null, hasMore = false) {
                    const countElements = document.querySelectorAll('[x-show="!loading"]');
                    countElements.forEach(el => {
                        if (el.textContent.includes('students')) {
                            el.textContent = `${total} students`;
                        }
                    });
                },
                
                // Filter management
                toggleFilters() {
                    this.showFilters = !this.showFilters;
                    
                    // Focus first filter if opening
                    if (this.showFilters) {
                        this.$nextTick(() => {
                            const firstSelect = document.querySelector('[name="status"]');
                            if (firstSelect) firstSelect.focus();
                        });
                    }
                },
                
                removeFilter(key) {
                    if (key === 'status') this.statusFilter = '';
                    if (key === 'programme') this.programmeFilter = '';
                    this.performFilter();
                },
                
                clearAllFilters() {
                    this.searchTerm = '';
                    this.statusFilter = '';
                    this.programmeFilter = '';
                    this.loading = false;
                    if (this.debounceTimer) {
                        clearTimeout(this.debounceTimer);
                    }
                    window.location.href = '{{ route('students.index') }}';
                },
                
                // Sorting
                sortBy(field) {
                    if (this.sortField === field) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortField = field;
                        this.sortDirection = 'asc';
                    }
                    this.updateURL();
                },
                
                // Selection management
                toggleStudent(studentId) {
                    const index = this.selectedStudents.indexOf(studentId);
                    if (index > -1) {
                        this.selectedStudents.splice(index, 1);
                    } else {
                        this.selectedStudents.push(studentId);
                    }
                    this.updateSelectAllState();
                },
                
                toggleSelectAll() {
                    if (this.selectAll) {
                        this.selectedStudents = this.students.map(s => s.id);
                    } else {
                        this.selectedStudents = [];
                    }
                },
                
                updateSelectAllState() {
                    this.selectAll = this.selectedStudents.length === this.students.length && this.students.length > 0;
                },
                
                clearSelection() {
                    this.selectedStudents = [];
                    this.selectAll = false;
                },
                
                // Bulk actions with better UX
                bulkAction(action) {
                    if (this.selectedStudents.length === 0) {
                        alert('Please select students first');
                        return;
                    }
                    
                    const count = this.selectedStudents.length;
                    const studentText = count === 1 ? 'student' : 'students';
                    
                    switch (action) {
                        case 'status':
                            if (confirm(`Change status for ${count} ${studentText}?`)) {
                                alert(`Status change for ${count} ${studentText} - Implementation coming soon`);
                            }
                            break;
                        case 'email':
                            if (confirm(`Send email to ${count} ${studentText}?`)) {
                                alert(`Email ${count} ${studentText} - Implementation coming soon`);
                            }
                            break;
                        case 'export':
                            alert(`Exporting ${count} ${studentText} - Implementation coming soon`);
                            break;
                    }
                },
                
                // Export functionality
                exportStudents() {
                    const totalStudents = {{ $students->total() }};
                    if (confirm(`Export all ${totalStudents} students?`)) {
                        alert('Export functionality coming soon');
                    }
                },
                
                // URL management with loading state
                updateURL() {
                    const params = new URLSearchParams();
                    if (this.searchTerm.trim()) params.append('search', this.searchTerm.trim());
                    if (this.statusFilter) params.append('status', this.statusFilter);
                    if (this.programmeFilter) params.append('programme', this.programmeFilter);
                    if (this.sortField !== 'created_at') params.append('sort', this.sortField);
                    if (this.sortDirection !== 'desc') params.append('direction', this.sortDirection);
                    
                    // Show loading briefly before navigation
                    this.loading = true;
                    
                    window.location.search = params.toString();
                }
            }
        }
    </script>
</x-wide-layout>