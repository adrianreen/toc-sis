{{-- resources/views/students/index.blade.php --}}
<x-wide-layout title="Students" subtitle="Manage student records and enrolments">
    <x-slot name="actions">
        {{-- Future buttons --}}
        {{-- <x-button variant="success" size="sm">
            Import Students
        </x-button>
        <x-button variant="secondary" size="sm">
            Export (QHub XML)
        </x-button> --}}
        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
            <x-button href="{{ route('students.recycle-bin') }}" variant="secondary" size="sm">
                🗑️ Recycle Bin
            </x-button>
        @endif
        <x-button href="{{ route('students.create') }}" variant="primary">
            Add New Student
        </x-button>
    </x-slot>

    <div x-data="studentIndex()">
        <!-- Search and Filters Section -->
        <x-card class="mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Enhanced Search Input -->
                <div class="lg:col-span-2">
                    <x-form.input 
                        name="search" 
                        label="Search Students" 
                        placeholder="Search by name, student number, or email..."
                        x-model="search"
                    />
                </div>

                <!-- Status Filter -->
                <div>
                    <x-form.select 
                        name="status_filter" 
                        label="Status Filter" 
                        placeholder="All Statuses"
                        x-model="statusFilter"
                    >
                        <option value="active">Active</option>
                        <option value="deferred">Deferred</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="enrolled">Enrolled</option>
                    </x-form.select>
                </div>

                <!-- Programme Filter -->
                <div>
                    <x-form.select 
                        name="programme_filter" 
                        label="Programme Filter" 
                        placeholder="All Programmes"
                        x-model="programmeFilter"
                    >
                        @foreach($programmes as $programme)
                            <option value="{{ $programme->code }}">{{ $programme->code }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>

            <!-- Filter Summary and Clear Button -->
            <div class="mt-6 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="text-sm font-medium text-slate-900">
                        <span x-text="filteredStudents.length"></span> of <span x-text="allStudents.length"></span> students
                    </div>
                    <span x-show="search || statusFilter || programmeFilter" 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-toc-100 text-toc-800">
                        Filtered
                    </span>
                </div>
                <x-button 
                    x-show="search || statusFilter || programmeFilter"
                    @click="clearFilters()"
                    variant="ghost" 
                    size="sm"
                >
                    Clear filters
                </x-button>
            </div>
        </x-card>

        <!-- Students Table -->
        <x-card padding="none">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                <div class="flex items-center space-x-1">
                                    <span>Student</span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Contact
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Programmes
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Joined
                            </th>
                            <th scope="col" class="relative px-6 py-4">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        <template x-for="student in filteredStudents" :key="student.id">
                            <tr class="hover:bg-slate-50 transition-colors duration-200 group">
                                <!-- Student Info with Avatar -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <!-- Avatar with initials -->
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-toc-400 to-toc-600 flex items-center justify-center text-white font-semibold text-sm"
                                                 x-text="student.full_name.split(' ').map(n => n[0]).join('').substring(0, 2)">
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-slate-900" x-text="student.full_name"></div>
                                            <div class="text-sm text-slate-500 font-mono" x-text="student.student_number"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900" x-text="student.email"></div>
                                    <div class="text-sm text-slate-500">Email</div>
                                </td>

                                <!-- Status Badge using our component -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-status-badge 
                                        x-bind:status="student.status" 
                                        variant="dot" 
                                        size="sm"
                                        x-text="student.status.charAt(0).toUpperCase() + student.status.slice(1)"
                                    />
                                </td>

                                <!-- Programme Tags -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        <template x-for="programme in student.programmes" :key="programme">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-toc-100 text-toc-800 border border-toc-200">
                                                <span x-text="programme"></span>
                                            </span>
                                        </template>
                                        <span x-show="student.programmes.length === 0" class="text-slate-400 text-xs italic">
                                            No enrolments
                                        </span>
                                    </div>
                                </td>

                                <!-- Joined Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    <div x-text="student.created_at"></div>
                                </td>

                                <!-- Actions using our button components -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <x-button 
                                            x-bind:href="`/admin/students/${student.id}/progress`"
                                            variant="secondary" 
                                            size="xs"
                                            title="View detailed progress"
                                        >
                                            Progress
                                        </x-button>
                                        <x-button 
                                            x-bind:href="`/students/${student.id}`"
                                            variant="ghost" 
                                            size="xs"
                                        >
                                            View
                                        </x-button>
                                        <x-button 
                                            x-bind:href="`/students/${student.id}/edit`"
                                            variant="primary" 
                                            size="xs"
                                        >
                                            Edit
                                        </x-button>
                                        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                                            <button 
                                                x-bind:onclick="`confirmDelete('${student.full_name}', '/students/${student.id}')`"
                                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                                title="Delete student"
                                            >
                                                🗑️
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div x-show="filteredStudents.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No students found</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        <span x-show="search || statusFilter || programmeFilter">Try adjusting your search or filters.</span>
                        <span x-show="!search && !statusFilter && !programmeFilter">Get started by adding a new student.</span>
                    </p>
                    <div class="mt-6" x-show="!search && !statusFilter && !programmeFilter">
                        <x-button href="{{ route('students.create') }}" variant="primary">
                            Add New Student
                        </x-button>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeDeleteModal()"></div>
        
        <!-- Modal content -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full relative">
                <div class="p-6">
                    <!-- Icon -->
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="text-lg font-medium leading-6 text-gray-900 text-center mb-2">
                        Delete Student
                    </h3>
                    
                    <!-- Message -->
                    <p class="text-sm text-gray-500 text-center mb-6">
                        Are you sure you want to delete <strong id="studentName"></strong>? 
                        This will move them to the recycle bin where they can be restored later.
                    </p>
                    
                    <!-- Buttons -->
                    <div class="flex justify-center space-x-3">
                        <button 
                            type="button" 
                            onclick="closeDeleteModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                        
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                Move to Recycle Bin
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function studentIndex() {
            return {
                search: '',
                statusFilter: '',
                programmeFilter: '',
                allStudents: @json($studentsData),
                
                get filteredStudents() {
                    return this.allStudents.filter(student => {
                        const matchesSearch = !this.search || 
                            student.full_name.toLowerCase().includes(this.search.toLowerCase()) ||
                            student.student_number.toLowerCase().includes(this.search.toLowerCase()) ||
                            student.email.toLowerCase().includes(this.search.toLowerCase());
                        
                        const matchesStatus = !this.statusFilter || student.status === this.statusFilter;
                        const matchesProgramme = !this.programmeFilter || student.programmes.includes(this.programmeFilter);
                        
                        return matchesSearch && matchesStatus && matchesProgramme;
                    });
                },
                
                clearFilters() {
                    this.search = '';
                    this.statusFilter = '';
                    this.programmeFilter = '';
                }
            }
        }

        // Delete confirmation functions
        function confirmDelete(studentName, deleteUrl) {
            const studentNameEl = document.getElementById('studentName');
            const deleteFormEl = document.getElementById('deleteForm');
            const deleteModalEl = document.getElementById('deleteModal');
            
            if (!studentNameEl || !deleteFormEl || !deleteModalEl) {
                return;
            }
            
            studentNameEl.textContent = studentName;
            deleteFormEl.action = deleteUrl;
            deleteModalEl.classList.remove('hidden');
        }

        function closeDeleteModal() {
            const deleteModalEl = document.getElementById('deleteModal');
            if (deleteModalEl) {
                deleteModalEl.classList.add('hidden');
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</x-wide-layout>