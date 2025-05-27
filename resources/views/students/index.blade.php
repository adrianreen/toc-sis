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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Search and Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Real-time Search -->
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Students</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="search"
                                    x-model="search"
                                    placeholder="Search by name, student number, or email..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <!-- Clear button -->
                                <button 
                                    x-show="search.length > 0"
                                    @click="search = ''"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select 
                                id="statusFilter"
                                x-model="statusFilter"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Statuses</option>
                                <option value="enquiry">Enquiry</option>
                                <option value="enrolled">Enrolled</option>
                                <option value="active">Active</option>
                                <option value="deferred">Deferred</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Programme Filter -->
                        <div>
                            <label for="programmeFilter" class="block text-sm font-medium text-gray-700 mb-1">Programme</label>
                            <select 
                                id="programmeFilter"
                                x-model="programmeFilter"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Programmes</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->code }}">{{ $programme->code }} - {{ $programme->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Results Summary -->
                    <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
                        <div>
                            Showing <span x-text="filteredStudents.length"></span> of <span x-text="allStudents.length"></span> students
                            <span x-show="search || statusFilter || programmeFilter" class="text-indigo-600">
                                (filtered)
                            </span>
                        </div>
                        <button 
                            x-show="search || statusFilter || programmeFilter"
                            @click="clearFilters()"
                            class="text-indigo-600 hover:text-indigo-800 font-medium"
                        >
                            Clear all filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Programme(s)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="student in filteredStudents" :key="student.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <span x-text="student.student_number"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span x-text="student.full_name"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span x-text="student.email"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800': student.status === 'active',
                                                'bg-yellow-100 text-yellow-800': student.status === 'deferred',
                                                'bg-blue-100 text-blue-800': student.status === 'completed',
                                                'bg-red-100 text-red-800': student.status === 'cancelled',
                                                'bg-purple-100 text-purple-800': student.status === 'enrolled',
                                                'bg-gray-100 text-gray-800': student.status === 'enquiry'
                                            }"
                                            x-text="student.status.charAt(0).toUpperCase() + student.status.slice(1)"
                                        ></span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="programme in student.programmes" :key="programme">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    <span x-text="programme"></span>
                                                </span>
                                            </template>
                                            <span x-show="student.programmes.length === 0" class="text-gray-400 text-xs">
                                                No enrolments
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a :href="`/students/${student.id}`" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                        <a :href="`/students/${student.id}/edit`" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- No results row -->
                            <tr x-show="filteredStudents.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <span x-show="search || statusFilter || programmeFilter">
                                            No students match your current filters.
                                        </span>
                                        <span x-show="!search && !statusFilter && !programmeFilter">
                                            No students found.
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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