{{-- resources/views/students/configurable-index.blade.php --}}
<x-wide-layout title="Students" subtitle="Manage student records and enrolments">
    <x-slot name="actions">
        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
            <x-button href="{{ route('students.recycle-bin') }}" variant="secondary" size="sm">
                🗑️ Recycle Bin
            </x-button>
            <x-button href="{{ route('students.index') }}" variant="secondary" size="sm">
                ← Standard Table
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

    <!-- Enhanced Configurable Student Management Interface -->
    <div x-data="{ 
        showTableConfig: false,
        visibleColumns: ['status', 'programmes', 'created_at'],
        
        openTableConfig() {
            this.showTableConfig = true;
        },
        
        closeTableConfig() {
            this.showTableConfig = false;
        },
        
        toggleColumn(col) {
            const index = this.visibleColumns.indexOf(col);
            if (index > -1) {
                this.visibleColumns.splice(index, 1);
            } else {
                this.visibleColumns.push(col);
            }
        }
    }" class="space-y-4">
        
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
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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

                        <!-- Table Configuration Button -->
                        <button 
                            type="button"
                            class="flex items-center gap-2 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition cursor-pointer"
                            @click="openTableConfig"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                            Columns
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

        <!-- Configurable Students Table -->
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

            <!-- Dynamic Configurable Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="configurable-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <template x-for="columnKey in visibleColumns" :key="columnKey">
                                <th x-show="isColumnVisible(columnKey)" 
                                    :style="getColumnWidth(columnKey)"
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider transition-all duration-200"
                                    :class="getColumnClasses(columnKey)"
                                    @click="sortByColumn(columnKey)">
                                    <div class="flex items-center gap-1">
                                        <span x-text="getColumnLabel(columnKey)"></span>
                                        <svg x-show="isColumnSortable(columnKey)" 
                                             class="w-4 h-4 transition-colors cursor-pointer" 
                                             :class="sortField === columnKey ? 'text-blue-500' : 'text-gray-400'" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="m3 16 4 4 4-4"/><path d="M7 20V4"/><path d="m21 8-4-4-4 4"/><path d="M17 4v16"/>
                                        </svg>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="table-body">
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50 transition-colors" 
                                :class="selectedStudents.includes({{ $student->id }}) ? 'bg-blue-50' : ''">
                                
                                <!-- Dynamic columns based on configuration -->
                                <template x-for="columnKey in visibleColumns" :key="columnKey + '-' + {{ $student->id }}">
                                    <td x-show="isColumnVisible(columnKey)" 
                                        :style="getColumnWidth(columnKey)"
                                        class="px-4 py-3 whitespace-nowrap transition-all duration-200"
                                        x-html="renderColumnData(columnKey, getStudentData({{ $student->id }}))">
                                    </td>
                                </template>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="20" class="px-6 py-12 text-center">
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
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
                <div class="bg-white px-3 py-2 border-t border-gray-200 sm:px-4">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            @endif
        </x-card>

        <!-- Table Configuration Modal -->
        <div x-show="showTableConfig" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             @click.self="closeTableConfig">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

                <!-- Modal Content -->
                <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Configure Table Columns</h3>
                        <button @click="closeTableConfig" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Column Configuration -->
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Visible Columns</h4>
                            <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-2">
                                <!-- Student Column -->
                                <div class="flex items-center justify-between p-2 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               id="col-student"
                                               checked
                                               disabled
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50">
                                        <label for="col-student" class="text-sm text-gray-900">
                                            Student <span class="text-xs text-gray-400">(required)</span>
                                        </label>
                                    </div>
                                    <span class="text-xs text-gray-500">student_info</span>
                                </div>
                                
                                <!-- Status Column -->
                                <div class="flex items-center justify-between p-2 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               id="col-status"
                                               :checked="visibleColumns.includes('status')"
                                               @change="toggleColumn('status')"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <label for="col-status" class="text-sm text-gray-900 cursor-pointer">
                                            Status
                                        </label>
                                    </div>
                                    <span class="text-xs text-gray-500">status_badge</span>
                                </div>
                                
                                <!-- Student Number Column -->
                                <div class="flex items-center justify-between p-2 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               id="col-student-number"
                                               :checked="visibleColumns.includes('student_number')"
                                               @change="toggleColumn('student_number')"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <label for="col-student-number" class="text-sm text-gray-900 cursor-pointer">
                                            Student Number
                                        </label>
                                    </div>
                                    <span class="text-xs text-gray-500">text</span>
                                </div>
                                
                                <!-- Programmes Column -->
                                <div class="flex items-center justify-between p-2 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               id="col-programmes"
                                               :checked="visibleColumns.includes('programmes')"
                                               @change="toggleColumn('programmes')"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <label for="col-programmes" class="text-sm text-gray-900 cursor-pointer">
                                            Programmes
                                        </label>
                                    </div>
                                    <span class="text-xs text-gray-500">programme_info</span>
                                </div>
                                
                                <!-- Email Column -->
                                <div class="flex items-center justify-between p-2 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               id="col-email"
                                               :checked="visibleColumns.includes('email')"
                                               @change="toggleColumn('email')"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <label for="col-email" class="text-sm text-gray-900 cursor-pointer">
                                            Email
                                        </label>
                                    </div>
                                    <span class="text-xs text-gray-500">text</span>
                                </div>
                                
                                <!-- Created At Column -->
                                <div class="flex items-center justify-between p-2">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" 
                                               id="col-created-at"
                                               :checked="visibleColumns.includes('created_at')"
                                               @change="toggleColumn('created_at')"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <label for="col-created-at" class="text-sm text-gray-900 cursor-pointer">
                                            Enrolled Date
                                        </label>
                                    </div>
                                    <span class="text-xs text-gray-500">date</span>
                                </div>
                            </div>
                        </div>

                        <!-- Preset Configurations -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Quick Presets</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="applyPreset('minimal')" 
                                        class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">
                                    Minimal View
                                </button>
                                <button @click="applyPreset('detailed')" 
                                        class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">
                                    Detailed View
                                </button>
                                <button @click="applyPreset('contact')" 
                                        class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">
                                    Contact Info
                                </button>
                                <button @click="applyPreset('academic')" 
                                        class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">
                                    Academic Focus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex justify-between mt-6">
                        <button @click="resetToDefaults" 
                                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">
                            Reset to Defaults
                        </button>
                        <div class="flex space-x-3">
                            <button @click="closeTableConfig" 
                                    class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button @click="saveTableConfig" 
                                    class="px-4 py-2 text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors cursor-pointer">
                                Save Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

    <!-- Global student data for table rendering -->
    <script>
        window.studentsData = {
            @foreach($students as $student)
                {{ $student->id }}: @json($student->toArray()),
            @endforeach
        };
    </script>

    <script>
        // No complex JavaScript needed
                // Search and filters (existing functionality)
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
                
                // Get student data for rendering
                getStudentData(studentId) {
                    return window.studentsData[studentId] || {};
                },
                
                // Table Configuration
                showTableConfig: false,
                availableColumns: {
                    'checkbox': { key: 'checkbox', label: '', type: 'checkbox', sortable: false, width: 50, required: true },
                    'student': { key: 'student', label: 'Student', type: 'student_info', sortable: true, width: 250, required: true },
                    'student_number': { key: 'student_number', label: 'Student #', type: 'text', sortable: true, width: 120 },
                    'status': { key: 'status', label: 'Status', type: 'status_badge', sortable: true, width: 100 },
                    'programmes': { key: 'programmes', label: 'Programmes', type: 'programme_info', sortable: false, width: 200 },
                    'email': { key: 'email', label: 'Email', type: 'text', sortable: true, width: 200 },
                    'created_at': { key: 'created_at', label: 'Enrolled', type: 'date', sortable: true, width: 120 },
                    'actions': { key: 'actions', label: 'Actions', type: 'actions', sortable: false, width: 100, required: true }
                },
                visibleColumns: ['checkbox', 'student', 'status', 'programmes', 'created_at', 'actions'],
                columnOrder: ['checkbox', 'student', 'status', 'programmes', 'created_at', 'actions'],
                columnWidths: {},
                
                init() {
                    // Initialize filters from URL
                    this.showFilters = this.hasActiveFilters;
                    
                    // Load table configuration (disabled for now - using defaults above)
                    // this.loadTableConfiguration();
                    
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
                                case ',':
                                    e.preventDefault();
                                    this.openTableConfig();
                                    break;
                            }
                        }
                        if (e.key === 'Escape') {
                            if (this.showTableConfig) {
                                this.closeTableConfig();
                            } else {
                                this.clearSelection();
                            }
                        }
                    });
                },
                
                // Load table configuration from API
                async loadTableConfiguration() {
                    try {
                        const response = await fetch('/api/table-preferences/students');
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const config = await response.json();
                        
                        this.availableColumns = config.columns || this.getDefaultColumns();
                        this.visibleColumns = config.visible_columns || ['checkbox', 'student', 'status', 'programmes', 'created_at', 'actions'];
                        this.columnOrder = config.column_order || this.visibleColumns;
                        this.columnWidths = config.column_widths || {};
                        
                        if (config.sort_preferences) {
                            this.sortField = config.sort_preferences.field;
                            this.sortDirection = config.sort_preferences.direction;
                        }
                    } catch (error) {
                        console.error('Failed to load table configuration:', error);
                        // Fallback to defaults
                        this.availableColumns = this.getDefaultColumns();
                        this.visibleColumns = ['checkbox', 'student', 'status', 'programmes', 'created_at', 'actions'];
                        this.columnOrder = this.visibleColumns;
                        this.columnWidths = {};
                    }
                },
                
                // Get default column definitions
                getDefaultColumns() {
                    return {
                        'checkbox': { key: 'checkbox', label: '', type: 'checkbox', sortable: false, width: 50, required: true },
                        'student': { key: 'student', label: 'Student', type: 'student_info', sortable: true, width: 250, required: true },
                        'student_number': { key: 'student_number', label: 'Student #', type: 'text', sortable: true, width: 120 },
                        'status': { key: 'status', label: 'Status', type: 'status_badge', sortable: true, width: 100 },
                        'programmes': { key: 'programmes', label: 'Programmes', type: 'programme_info', sortable: false, width: 200 },
                        'email': { key: 'email', label: 'Email', type: 'text', sortable: true, width: 200 },
                        'phone': { key: 'phone', label: 'Phone', type: 'text', sortable: false, width: 150 },
                        'location': { key: 'location', label: 'Location', type: 'location', sortable: true, width: 150 },
                        'age': { key: 'age', label: 'Age', type: 'calculated_age', sortable: true, width: 80 },
                        'last_activity': { key: 'last_activity', label: 'Last Activity', type: 'date', sortable: true, width: 130 },
                        'created_at': { key: 'created_at', label: 'Enrolled', type: 'date', sortable: true, width: 120 },
                        'actions': { key: 'actions', label: 'Actions', type: 'actions', sortable: false, width: 100, required: true }
                    };
                },
                
                // Save table configuration to API
                async saveTableConfiguration() {
                    try {
                        const response = await fetch('/api/table-preferences/students', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: JSON.stringify({
                                visible_columns: this.visibleColumns,
                                column_order: this.columnOrder,
                                column_widths: this.columnWidths,
                                sort_preferences: {
                                    field: this.sortField,
                                    direction: this.sortDirection
                                }
                            })
                        });
                        
                        if (response.ok) {
                            this.showNotification('Table configuration saved successfully', 'success');
                        }
                    } catch (error) {
                        console.error('Failed to save table configuration:', error);
                        this.showNotification('Failed to save configuration', 'error');
                    }
                },
                
                // Table configuration methods
                openTableConfig() {
                    this.showTableConfig = true;
                },
                
                closeTableConfig() {
                    this.showTableConfig = false;
                },
                
                saveTableConfig() {
                    this.saveTableConfiguration();
                    this.closeTableConfig();
                },
                
                toggleColumn(columnKey) {
                    const index = this.visibleColumns.indexOf(columnKey);
                    if (index > -1) {
                        this.visibleColumns.splice(index, 1);
                    } else {
                        this.visibleColumns.push(columnKey);
                    }
                },
                
                isColumnVisible(columnKey) {
                    return this.visibleColumns.includes(columnKey);
                },
                
                getColumnLabel(columnKey) {
                    return this.availableColumns[columnKey]?.label || columnKey;
                },
                
                getColumnWidth(columnKey) {
                    const width = this.columnWidths[columnKey] || this.availableColumns[columnKey]?.width;
                    return width ? `width: ${width}px; min-width: ${width}px;` : '';
                },
                
                getColumnClasses(columnKey) {
                    const column = this.availableColumns[columnKey];
                    return column?.sortable ? 'cursor-pointer hover:text-gray-700' : '';
                },
                
                isColumnSortable(columnKey) {
                    return this.availableColumns[columnKey]?.sortable || false;
                },
                
                sortByColumn(columnKey) {
                    if (!this.isColumnSortable(columnKey)) return;
                    
                    if (this.sortField === columnKey) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortField = columnKey;
                        this.sortDirection = 'asc';
                    }
                    this.updateURL();
                },
                
                // Render column data based on type
                renderColumnData(columnKey, student) {
                    const column = this.availableColumns[columnKey];
                    if (!column) return '';
                    
                    switch (column.type) {
                        case 'checkbox':
                            return `<input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" 
                                           :checked="selectedStudents.includes(${student.id})" 
                                           @change="toggleStudent(${student.id})">`;
                        
                        case 'student_info':
                            const initials = student.full_name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                            return `<div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-sm font-semibold">
                                            ${initials}
                                        </div>
                                        <div class="ml-4">
                                            <a href="/students/${student.id}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 cursor-pointer block">
                                                ${student.full_name.length > 30 ? student.full_name.substring(0, 30) + '...' : student.full_name}
                                            </a>
                                            <div class="text-sm text-gray-500 truncate">${student.email}</div>
                                        </div>
                                    </div>`;
                        
                        case 'text':
                            return `<span class="text-sm text-gray-900">${student[columnKey] || ''}</span>`;
                        
                        case 'status_badge':
                            const statusClasses = this.getStatusClasses(student.status);
                            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClasses}">
                                        ${student.status.charAt(0).toUpperCase() + student.status.slice(1)}
                                    </span>`;
                        
                        case 'programme_info':
                            const programmes = student.programmes && student.programmes.length > 0 
                                ? student.programmes.join(', ') 
                                : 'No programme';
                            return `<div class="text-sm text-gray-900">${programmes}</div>`;
                        
                        case 'location':
                            const location = [student.city, student.county].filter(Boolean).join(', ');
                            return `<span class="text-sm text-gray-900">${location || 'N/A'}</span>`;
                        
                        case 'calculated_age':
                            if (student.date_of_birth) {
                                const age = new Date().getFullYear() - new Date(student.date_of_birth).getFullYear();
                                return `<span class="text-sm text-gray-900">${age}</span>`;
                            }
                            return '<span class="text-sm text-gray-400">-</span>';
                        
                        case 'date':
                            if (student[columnKey]) {
                                const date = new Date(student[columnKey]);
                                return `<span class="text-sm text-gray-500">${date.toLocaleDateString()}</span>`;
                            }
                            return '<span class="text-sm text-gray-400">-</span>';
                        
                        case 'actions':
                            return `<div class="flex items-center gap-2 justify-end">
                                        <a href="/students/${student.id}" class="text-blue-600 hover:text-blue-700 text-sm font-medium cursor-pointer">View</a>
                                        <a href="/students/${student.id}/edit" class="text-gray-600 hover:text-gray-700 text-sm font-medium cursor-pointer">Edit</a>
                                    </div>`;
                        
                        default:
                            return `<span class="text-sm text-gray-900">${student[columnKey] || ''}</span>`;
                    }
                },
                
                // Preset configurations
                applyPreset(preset) {
                    const presets = {
                        minimal: ['checkbox', 'student', 'status', 'actions'],
                        detailed: ['checkbox', 'student', 'student_number', 'email', 'phone', 'status', 'programme', 'location', 'created_at', 'actions'],
                        contact: ['checkbox', 'student', 'email', 'phone', 'location', 'actions'],
                        academic: ['checkbox', 'student', 'student_number', 'status', 'programme', 'created_at', 'last_activity', 'actions']
                    };
                    
                    if (presets[preset]) {
                        this.visibleColumns = presets[preset];
                    }
                },
                
                resetToDefaults() {
                    if (confirm('Reset table configuration to defaults? This will override your current settings.')) {
                        fetch('/api/table-preferences/students', { method: 'DELETE' })
                            .then(() => this.loadTableConfiguration())
                            .then(() => this.showNotification('Table configuration reset to defaults', 'success'));
                    }
                },
                
                // Utility methods
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
                
                showNotification(message, type = 'info') {
                    // Simple notification system
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white text-sm z-50 transition-transform duration-300 ${
                        type === 'success' ? 'bg-green-500' : 
                        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
                    }`;
                    notification.textContent = message;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.style.transform = 'translateY(-100%)';
                        setTimeout(() => notification.remove(), 300);
                    }, 3000);
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
                
                // Search functionality (existing methods remain the same)
                performFilter() {
                    if (this.debounceTimer) {
                        clearTimeout(this.debounceTimer);
                    }
                    
                    if (this.searchTerm.length > 0 && this.searchTerm.length < 2) {
                        return;
                    }
                    
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
                        
                        const response = await fetch(`/api/students/search?${params.toString()}&limit=100`);
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.updateTable(data.students, data.has_more);
                            this.updateResultsCount(data.total, data.showing, data.has_more);
                        } else {
                            this.updateURL();
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        this.updateURL();
                    } finally {
                        this.loading = false;
                    }
                },
                
                updateTable(students, hasMore = false) {
                    this.students = students;
                    this.selectedStudents = [];
                    this.selectAll = false;
                    this.renderTableRows(students);
                },
                
                renderTableRows(students) {
                    const tbody = document.getElementById('table-body');
                    if (!tbody) return;
                    
                    if (students.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="20" class="px-6 py-12 text-center">
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
                    return `
                        <tr class="hover:bg-gray-50 transition-colors" 
                            :class="selectedStudents.includes(${student.id}) ? 'bg-blue-50' : ''">
                            ${this.visibleColumns.map(columnKey => `
                                <td class="px-4 py-3 whitespace-nowrap transition-all duration-200" 
                                    style="${this.getColumnWidth(columnKey)}">
                                    ${this.renderColumnData(columnKey, student)}
                                </td>
                            `).join('')}
                        </tr>
                    `;
                },
                
                // Filter management
                toggleFilters() {
                    this.showFilters = !this.showFilters;
                    
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
                
                // Selection management (existing methods)
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
                
                // Bulk actions
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
                
                exportStudents() {
                    const totalStudents = {{ $students->total() }};
                    if (confirm(`Export all ${totalStudents} students?`)) {
                        alert('Export functionality coming soon');
                    }
                },
                
                updateResultsCount(total, showing = null, hasMore = false) {
                    const countElements = document.querySelectorAll('[x-show="!loading"]');
                    countElements.forEach(el => {
                        if (el.textContent.includes('students')) {
                            el.textContent = `${total} students`;
                        }
                    });
                },
                
                updateURL() {
                    const params = new URLSearchParams();
                    if (this.searchTerm.trim()) params.append('search', this.searchTerm.trim());
                    if (this.statusFilter) params.append('status', this.statusFilter);
                    if (this.programmeFilter) params.append('programme', this.programmeFilter);
                    if (this.sortField !== 'created_at') params.append('sort', this.sortField);
                    if (this.sortDirection !== 'desc') params.append('direction', this.sortDirection);
                    
                    this.loading = true;
                    window.location.search = params.toString();
                }
            }
        }
    </script>
</x-wide-layout>