{{-- resources/views/students/index.blade.php --}}
<x-wide-layout title="Students" subtitle="Manage student records and enrolments">
    <x-slot name="actions">
        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
            <x-button href="{{ route('students.recycle-bin') }}" variant="secondary" size="sm">
                🗑️ Recycle Bin
            </x-button>
        @endif
        <x-button href="{{ route('students.create') }}" variant="primary">
            Add New Student
        </x-button>
    </x-slot>

    <div>
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Search and Filters -->
        <x-card class="mb-6" x-data="studentTableSearch()">
            <form method="GET" action="{{ route('students.index') }}" id="searchForm">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Search Input -->
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            Search Students
                        </label>
                        <div class="relative">
                            <!-- Search Icon -->
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            
                            <!-- Search Input -->
                            <input 
                                type="text" 
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search by name, student number, or email..."
                                class="block w-full pl-10 pr-20 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200"
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
                                    class="p-1 rounded-full hover:bg-gray-100 transition-colors"
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
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Status
                            </span>
                        </label>
                        <div class="relative">
                            <select 
                                id="status"
                                name="status"
                                x-model="statusFilter"
                                @change="performFilter()"
                                class="block w-full py-3 pl-4 pr-10 text-sm border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors hover:border-gray-400"
                            >
                                <option value="">All Statuses</option>
                                <option value="active">✅ Active</option>
                                <option value="enrolled">📋 Enrolled</option>
                                <option value="deferred">⏸️ Deferred</option>
                                <option value="completed">🎓 Completed</option>
                                <option value="cancelled">❌ Cancelled</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Programme Filter -->
                    <div>
                        <label for="programme" class="block text-sm font-medium text-gray-700 mb-2">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                Programme
                            </span>
                        </label>
                        <div class="relative">
                            <select 
                                id="programme"
                                name="programme"
                                x-model="programmeFilter"
                                @change="performFilter()"
                                class="block w-full py-3 pl-4 pr-10 text-sm border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors hover:border-gray-400"
                            >
                                <option value="">All Programmes</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->code }}">{{ $programme->code }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Count -->
                <div class="mt-6 flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="flex items-center space-x-4">
                        <!-- Clear All Filters Button -->
                        <button 
                            type="button"
                            x-show="searchTerm.length > 0 || statusFilter !== '' || programmeFilter !== ''"
                            @click="clearAllFilters()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear All Filters
                        </button>
                        
                        <!-- Fallback static button for when no JS -->
                        @if(request()->hasAny(['search', 'status', 'programme']))
                            <a href="{{ route('students.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                               x-show="false">
                                Clear All Filters
                            </a>
                        @endif
                    </div>
                    
                    <div class="text-sm font-medium text-gray-900" id="results-count">
                        {{ $students->total() }} students
                        @if(request()->hasAny(['search', 'status', 'programme']))
                            <span class="text-blue-600">(filtered)</span>
                        @endif
                    </div>
                </div>
            </form>
        </x-card>

        <!-- Students Table -->
        <x-card padding="none" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Student
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Programmes
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joined
                            </th>
                            <th scope="col" class="relative px-6 py-4">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="students-tbody">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50 transition-colors duration-200 group cursor-pointer" 
                                onclick="window.location.href='{{ route('students.show', $student) }}'"
                                title="Click to view student details">
                                <!-- Student Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-semibold text-sm">
                                                {{ collect(explode(' ', $student->full_name))->map(fn($n) => $n[0])->take(2)->implode('') }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $student->full_name }}</div>
                                            <div class="text-sm text-gray-500 font-mono">{{ $student->student_number }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $student->email }}</div>
                                    <div class="text-sm text-gray-500">Email</div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-status-badge 
                                        :status="$student->status" 
                                        variant="dot" 
                                        size="sm"
                                    >
                                        {{ ucfirst($student->status) }}
                                    </x-status-badge>
                                </td>

                                <!-- Programmes -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @forelse($student->enrolments->pluck('programme.code')->unique() as $programme)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $programme }}
                                            </span>
                                        @empty
                                            <span class="text-gray-400 text-xs italic">
                                                No enrolments
                                            </span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- Joined Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $student->created_at->format('d M Y') }}</div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <x-button 
                                            href="{{ route('students.show', $student) }}"
                                            variant="ghost" 
                                            size="xs"
                                        >
                                            View
                                        </x-button>
                                        <x-button 
                                            href="{{ route('students.edit', $student) }}"
                                            variant="primary" 
                                            size="xs"
                                        >
                                            Edit
                                        </x-button>
                                        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                                            <form method="POST" action="{{ route('students.destroy', $student) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete {{ $student->full_name }}? This will move them to the recycle bin.')"
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit"
                                                    class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                                    title="Delete student"
                                                >
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        @if(request()->hasAny(['search', 'status', 'programme']))
                                            Try adjusting your search or filters.
                                        @else
                                            Get started by adding a new student.
                                        @endif
                                    </p>
                                    @if(!request()->hasAny(['search', 'status', 'programme']))
                                        <div class="mt-6">
                                            <x-button href="{{ route('students.create') }}" variant="primary">
                                                Add New Student
                                            </x-button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- Show More Button (appears via JavaScript when needed) -->
                <div id="show-more-container" class="hidden bg-gray-50 border-t border-gray-200 px-6 py-4 text-center">
                    <button 
                        type="button"
                        onclick="document.getElementById('searchForm').submit()"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        View All Results
                    </button>
                    <p class="text-xs text-gray-500 mt-1">Switch to paginated view to see all results</p>
                </div>
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            @endif
        </x-card>
    </div>

    <script>
        // Simple student table search using the same API as dashboard
        function studentTableSearch() {
            return {
                searchTerm: '{{ request('search') }}',
                statusFilter: '{{ request('status') }}',
                programmeFilter: '{{ request('programme') }}',
                debounceTimer: null,
                loading: false,
                
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
                
                async performTableFilter() {
                    // If no filters, reload the page to show all results
                    if (this.searchTerm.length === 0 && this.statusFilter === '' && this.programmeFilter === '') {
                        window.location.href = '{{ route('students.index') }}';
                        return;
                    }
                    
                    this.loading = true;
                    
                    try {
                        const params = new URLSearchParams();
                        if (this.searchTerm.length >= 2) params.append('q', this.searchTerm);
                        if (this.statusFilter) params.append('status', this.statusFilter);
                        if (this.programmeFilter) params.append('programme', this.programmeFilter);
                        
                        const response = await fetch(`/api/students/search?${params.toString()}&limit=100`);
                        if (response.ok) {
                            const data = await response.json();
                            this.updateTable(data.students, data.has_more);
                            this.updateResultsCount(data.total, data.showing, data.has_more);
                        } else {
                            console.error('Search failed:', response.statusText);
                            // Fallback to form submission on error
                            document.getElementById('searchForm').submit();
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        // Fallback to form submission
                        document.getElementById('searchForm').submit();
                    } finally {
                        this.loading = false;
                    }
                },
                
                updateTable(students, hasMore = false) {
                    const tbody = document.getElementById('students-tbody');
                    const showMoreContainer = document.getElementById('show-more-container');
                    
                    // Show/hide the "Show More" button
                    if (hasMore && students.length > 0) {
                        showMoreContainer.classList.remove('hidden');
                    } else {
                        showMoreContainer.classList.add('hidden');
                    }
                    
                    if (students.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <h3 class="text-sm font-medium text-gray-900 mb-1">No students found</h3>
                                        <p class="text-sm text-gray-500 mb-4">No students match "${this.searchTerm}"</p>
                                        <button onclick="this.closest('[x-data]').__x.$data.clearSearch()" 
                                                class="text-sm text-blue-600 hover:text-blue-700 font-medium">
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
                    const statusClasses = this.getStatusClasses(student.status);
                    const dotClasses = this.getStatusDotClasses(student.status);
                    
                    return `
                        <tr class="hover:bg-gray-50 transition-colors duration-200 group cursor-pointer" 
                            onclick="window.location.href='/students/${student.id}'"
                            title="Click to view student details">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white font-semibold text-sm">
                                            ${initials}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">${student.full_name}</div>
                                        <div class="text-sm text-gray-500 font-mono">${student.student_number}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${student.email}</div>
                                <div class="text-sm text-gray-500">Email</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClasses}">
                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full ${dotClasses}"></span>
                                    ${student.status.charAt(0).toUpperCase() + student.status.slice(1)}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    ${student.programmes && student.programmes.length > 0 
                                        ? student.programmes.map(prog => `<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">${prog}</span>`).join('')
                                        : '<span class="text-gray-400 text-xs italic">No enrolments</span>'
                                    }
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>${student.created_at}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <a href="/students/${student.id}" class="inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">View</a>
                                    <a href="/students/${student.id}/edit" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700">Edit</a>
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
                        'cancelled': 'bg-red-100 text-red-800'
                    };
                    return classes[status] || 'bg-gray-100 text-gray-800';
                },
                
                getStatusDotClasses(status) {
                    const classes = {
                        'active': 'bg-green-400',
                        'enrolled': 'bg-blue-400',
                        'deferred': 'bg-yellow-400',
                        'completed': 'bg-purple-400',
                        'cancelled': 'bg-red-400'
                    };
                    return classes[status] || 'bg-gray-400';
                },
                
                updateResultsCount(total, showing = null, hasMore = false) {
                    const countElement = document.getElementById('results-count');
                    if (countElement) {
                        const hasFilters = this.searchTerm.length > 0 || this.statusFilter !== '' || this.programmeFilter !== '';
                        
                        let countText = `${total} students`;
                        if (showing !== null && showing < total) {
                            countText = `Showing ${showing} of ${total} students`;
                            if (hasMore) {
                                countText += ' <span class="text-orange-600">(limited results)</span>';
                            }
                        }
                        
                        if (hasFilters) {
                            countText += ' <span class="text-blue-600">(filtered)</span>';
                        }
                        
                        countElement.innerHTML = countText;
                    }
                }
            }
        }
    </script>
</x-wide-layout>