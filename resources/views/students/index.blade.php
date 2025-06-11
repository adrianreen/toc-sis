{{-- resources/views/students/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Students
            </h2>
            <div class="flex space-x-2">
                {{-- Future: Bulk Import Button --}}
                {{-- <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Import Students
                </button> --}}
                
                {{-- Future: Export Button --}}
                {{-- <button class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Export (QHub XML)
                </button> --}}
                
                <a href="{{ route('students.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Student
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="studentIndex()">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

           <!-- Make the search section look better too -->
<div class="bg-white shadow-sm border border-gray-200 rounded-xl mb-6">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Enhanced Search Input -->
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search Students</label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="search"
                        x-model="search"
                        placeholder="Search by name, student number, or email..."
                        class="block w-full pl-11 pr-10 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button 
                        x-show="search.length > 0"
                        @click="search = ''"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center hover:bg-gray-50 rounded-r-lg transition-colors duration-200"
                    >
                        <svg class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Enhanced Dropdowns -->
            <div>
                <label for="statusFilter" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select 
                    id="statusFilter"
                    x-model="statusFilter"
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent py-3 transition-all duration-200"
                >
                    <option value="">All Statuses</option>
                    <option value="enquiry">📝 Enquiry</option>
                    <option value="enrolled">📋 Enrolled</option>
                    <option value="active">✅ Active</option>
                    <option value="deferred">⏸️ Deferred</option>
                    <option value="completed">🎓 Completed</option>
                    <option value="cancelled">❌ Cancelled</option>
                </select>
            </div>

            <div>
                <label for="programmeFilter" class="block text-sm font-semibold text-gray-700 mb-2">Programme</label>
                <select 
                    id="programmeFilter"
                    x-model="programmeFilter"
                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent py-3 transition-all duration-200"
                >
                    <option value="">All Programmes</option>
                    @foreach($programmes as $programme)
                        <option value="{{ $programme->code }}">{{ $programme->code }} - {{ $programme->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Enhanced Results Summary -->
        <div class="mt-6 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="text-sm font-medium text-gray-900">
                    <span x-text="filteredStudents.length"></span> of <span x-text="allStudents.length"></span> students
                </div>
                <span x-show="search || statusFilter || programmeFilter" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Filtered
                </span>
            </div>
            <button 
                x-show="search || statusFilter || programmeFilter"
                @click="clearFilters()"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md text-gray-600 hover:text-gray-700 hover:bg-gray-50 transition-colors duration-200"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear filters
            </button>
        </div>
    </div>
</div>

            <!-- Table -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <div class="flex items-center space-x-1">
                            <span>Student</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                            </svg>
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Contact
                    </th>
                    <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Programmes
                    </th>
                    <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Joined
                    </th>
                    <th scope="col" class="relative px-4 py-4 w-32">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <template x-for="student in filteredStudents" :key="student.id">
                    <tr class="hover:bg-blue-50 hover:shadow-sm transition-all duration-200 group cursor-pointer" 
                        @click="window.location.href = `/students/${student.id}`"
                        title="Click to view student details">
                        <!-- Student Info with Avatar -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <!-- Avatar with initials -->
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm"
                                         x-text="student.full_name.split(' ').map(n => n[0]).join('').substring(0, 2)">
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900" x-text="student.full_name"></div>
                                    <div class="text-sm text-gray-500 font-mono" x-text="student.student_number"></div>
                                </div>
                            </div>
                        </td>

                        <!-- Contact -->
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900" x-text="student.email"></div>
                            <div class="text-sm text-gray-500">Email</div>
                        </td>

                        <!-- Enhanced Status Badge -->
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span 
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ring-1 ring-inset"
                                :class="{
                                    'bg-green-50 text-green-700 ring-green-600/20': student.status === 'active',
                                    'bg-yellow-50 text-yellow-700 ring-yellow-600/20': student.status === 'deferred',
                                    'bg-blue-50 text-blue-700 ring-blue-600/20': student.status === 'completed',
                                    'bg-red-50 text-red-700 ring-red-600/20': student.status === 'cancelled',
                                    'bg-purple-50 text-purple-700 ring-purple-600/20': student.status === 'enrolled',
                                    'bg-gray-50 text-gray-700 ring-gray-600/20': student.status === 'enquiry'
                                }"
                            >
                                <!-- Status dot -->
                                <span 
                                    class="w-1.5 h-1.5 rounded-full mr-2"
                                    :class="{
                                        'bg-green-500': student.status === 'active',
                                        'bg-yellow-500': student.status === 'deferred',
                                        'bg-blue-500': student.status === 'completed',
                                        'bg-red-500': student.status === 'cancelled',
                                        'bg-purple-500': student.status === 'enrolled',
                                        'bg-gray-500': student.status === 'enquiry'
                                    }"
                                ></span>
                                <span x-text="student.status.charAt(0).toUpperCase() + student.status.slice(1)"></span>
                            </span>
                        </td>

                        <!-- Enhanced Programme Tags -->
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                <template x-for="programme in student.programmes" :key="programme">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        <span x-text="programme"></span>
                                    </span>
                                </template>
                                <span x-show="student.programmes.length === 0" class="text-gray-400 text-xs italic">
                                    No enrolments
                                </span>
                            </div>
                        </td>

                        <!-- Joined Date -->
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div x-text="student.created_at"></div>
                        </td>

                        <!-- Enhanced Actions -->
<td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
    <div class="flex items-center justify-end space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <a :href="`/admin/students/${student.id}/progress`" 
           class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-purple-700 bg-purple-50 hover:bg-purple-100 transition-colors duration-200"
           title="View detailed progress">
            Progress
        </a>
        <a :href="`/students/${student.id}`" 
           class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
            View
        </a>
        <a :href="`/students/${student.id}/edit`" 
           class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-200">
            Edit
        </a>
    </div>
</td>
                    </tr>
                </template>

                <!-- Enhanced Empty State -->
                <tr x-show="filteredStudents.length === 0">
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a4 4 0 11-4-4 4 4 0 014 4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">No students found</h3>
                            <p class="text-sm text-gray-500" x-show="search || statusFilter || programmeFilter">
                                Try adjusting your search criteria or clearing filters.
                            </p>
                            <p class="text-sm text-gray-500" x-show="!search && !statusFilter && !programmeFilter">
                                Get started by adding your first student.
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <!-- Mobile pagination -->
                @if ($students->previousPageUrl())
                    <a href="{{ $students->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                @endif
                @if ($students->nextPageUrl())
                    <a href="{{ $students->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                @endif
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $students->firstItem() }}</span> to <span class="font-medium">{{ $students->lastItem() }}</span> of <span class="font-medium">{{ $students->total() }}</span> students
                    </p>
                </div>
                <div>
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Loading indicator for future AJAX implementation -->
            <div x-show="loading" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
                <div class="bg-white p-4 rounded-lg">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    <p class="mt-2 text-sm text-gray-600">Loading students...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function studentIndex() {
            return {
                // All students data - loaded once from server
                allStudents: @json($studentsData ?? []),
                
                // Filter states
                search: '',
                statusFilter: '',
                programmeFilter: '',
                
                // Loading state for future AJAX implementation
                loading: false,
                
                // Debug initialization
                init() {
                    console.log('Alpine.js studentIndex initialized');
                    console.log('Students data:', this.allStudents);
                    console.log('Total students:', this.allStudents.length);
                },
                
                // Computed filtered students
                get filteredStudents() {
                    console.log('Filtering students...', {
                        search: this.search,
                        statusFilter: this.statusFilter,
                        programmeFilter: this.programmeFilter,
                        totalStudents: this.allStudents.length
                    });
                    
                    let filtered = [...this.allStudents]; // Create a copy
                    
                    // Search filter (name, student number, email)
                    if (this.search && this.search.trim()) {
                        const searchTerm = this.search.toLowerCase().trim();
                        console.log('Applying search filter:', searchTerm);
                        filtered = filtered.filter(student => {
                            const matches = student.full_name.toLowerCase().includes(searchTerm) ||
                                student.student_number.toLowerCase().includes(searchTerm) ||
                                student.email.toLowerCase().includes(searchTerm);
                            return matches;
                        });
                        console.log('After search filter:', filtered.length);
                    }
                    
                    // Status filter
                    if (this.statusFilter) {
                        console.log('Applying status filter:', this.statusFilter);
                        filtered = filtered.filter(student => student.status === this.statusFilter);
                        console.log('After status filter:', filtered.length);
                    }
                    
                    // Programme filter
                    if (this.programmeFilter) {
                        console.log('Applying programme filter:', this.programmeFilter);
                        filtered = filtered.filter(student => 
                            student.programmes && student.programmes.includes(this.programmeFilter)
                        );
                        console.log('After programme filter:', filtered.length);
                    }
                    
                    console.log('Final filtered results:', filtered.length);
                    return filtered;
                },
                
                // Clear all filters
                clearFilters() {
                    console.log('Clearing all filters');
                    this.search = '';
                    this.statusFilter = '';
                    this.programmeFilter = '';
                }
                
                // Note: If search performance becomes slow with large datasets (1000+ students),
                // we should switch to server-side AJAX filtering using debounced requests
                // This would involve:
                // 1. Adding a debounged watch on search/filters
                // 2. Making AJAX calls to a new endpoint like /students/search
                // 3. Updating the filteredStudents array from server response
                // 4. Adding proper loading states and error handling
            }
        }
        
        // Debug: Check if Alpine.js is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            console.log('Alpine available:', typeof Alpine !== 'undefined');
        });
    </script>
</x-app-layout>