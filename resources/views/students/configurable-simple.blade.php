{{-- resources/views/students/configurable-simple.blade.php --}}
<x-wide-layout title="Students" subtitle="Manage student records and enrolments">
    <x-slot name="actions">
        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
            <x-button href="{{ route('students.recycle-bin') }}" variant="secondary" size="sm">
                🗑️ Recycle Bin
            </x-button>
        @endif
        <x-button href="{{ route('students.create') }}" variant="primary" class="!bg-blue-600 !text-white hover:!bg-blue-700 cursor-pointer">
            Add Student
        </x-button>
    </x-slot>

    <!-- Simple Configurable Interface -->
    <div x-data="{ 
        showModal: false,
        visibleColumns: ['actions', 'student', 'status', 'programmes'],
        openMenuId: null,
        
        toggleColumn(col) {
            const index = this.visibleColumns.indexOf(col);
            if (index > -1) {
                this.visibleColumns.splice(index, 1);
            } else {
                this.visibleColumns.push(col);
            }
        },
        
        toggleMenu(studentId) {
            this.openMenuId = this.openMenuId === studentId ? null : studentId;
        },
        
        closeMenu() {
            this.openMenuId = null;
        }
    }" class="space-y-4" @click.away="closeMenu()">
        
        <x-card class="p-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h2 class="text-lg font-semibold">Students</h2>
                    <span class="text-sm text-gray-500">{{ $students->total() }} total</span>
                </div>
                <button @click="showModal = true" 
                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 cursor-pointer">
                    ⚙️ Configure Columns
                </button>
            </div>
        </x-card>

        <!-- Modal -->
        <div x-show="showModal" 
             class="fixed inset-0 z-50 overflow-y-auto"
             @click.self="showModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                
                <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full relative">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configure Columns</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-2 border rounded">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       :checked="visibleColumns.includes('actions')"
                                       @change="toggleColumn('actions')"
                                       class="rounded border-gray-300">
                                <label class="text-sm font-medium text-gray-900">Actions Menu</label>
                            </div>
                            <span class="text-xs text-gray-500">actions</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-2 border rounded">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       :checked="visibleColumns.includes('student')"
                                       @change="toggleColumn('student')"
                                       class="rounded border-gray-300">
                                <label class="text-sm font-medium text-gray-900">Student Name</label>
                            </div>
                            <span class="text-xs text-gray-500">student_info</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-2 border rounded">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       :checked="visibleColumns.includes('status')"
                                       @change="toggleColumn('status')"
                                       class="rounded border-gray-300">
                                <label class="text-sm font-medium text-gray-900">Status</label>
                            </div>
                            <span class="text-xs text-gray-500">status_badge</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-2 border rounded">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       :checked="visibleColumns.includes('email')"
                                       @change="toggleColumn('email')"
                                       class="rounded border-gray-300">
                                <label class="text-sm font-medium text-gray-900">Email</label>
                            </div>
                            <span class="text-xs text-gray-500">text</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-2 border rounded">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       :checked="visibleColumns.includes('programmes')"
                                       @change="toggleColumn('programmes')"
                                       class="rounded border-gray-300">
                                <label class="text-sm font-medium text-gray-900">Programmes</label>
                            </div>
                            <span class="text-xs text-gray-500">programme_info</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button @click="showModal = false" 
                                class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                            Close
                        </button>
                    </div>
                    
                    <div class="mt-4 p-2 bg-gray-100 text-xs">
                        Visible: <span x-text="visibleColumns.join(', ')"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dynamic Student Table -->
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th x-show="visibleColumns.includes('actions')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">
                                Actions
                            </th>
                            <th x-show="visibleColumns.includes('student')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Student
                            </th>
                            <th x-show="visibleColumns.includes('status')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th x-show="visibleColumns.includes('email')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th x-show="visibleColumns.includes('programmes')" 
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Programmes
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($students as $student)
                            <tr>
                                <td x-show="visibleColumns.includes('actions')" 
                                    class="px-6 py-4 whitespace-nowrap w-16">
                                    <div class="relative">
                                        <button @click="toggleMenu({{ $student->id }})" 
                                                class="text-gray-400 hover:text-gray-600 cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Dropdown Menu -->
                                        <div x-show="openMenuId === {{ $student->id }}" 
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                            <div class="py-1">
                                                <a href="{{ route('students.show', $student) }}" 
                                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                    👁️ View Details
                                                </a>
                                                <a href="{{ route('students.edit', $student) }}" 
                                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">
                                                    ✏️ Edit Student
                                                </a>
                                                <div class="border-t border-gray-100"></div>
                                                <form action="{{ route('students.destroy', $student) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to delete {{ $student->full_name }}? This will move them to the recycle bin.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 cursor-pointer">
                                                        🗑️ Delete Student
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td x-show="visibleColumns.includes('student')" 
                                    class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $student->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $student->student_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td x-show="visibleColumns.includes('status')" 
                                    class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($student->status === 'active') bg-green-100 text-green-800
                                        @elseif($student->status === 'enrolled') bg-blue-100 text-blue-800
                                        @elseif($student->status === 'deferred') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                                <td x-show="visibleColumns.includes('email')" 
                                    class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $student->email }}</div>
                                </td>
                                <td x-show="visibleColumns.includes('programmes')" 
                                    class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @php
                                            $programmes = $student->enrolments->where('enrolment_type', 'programme')->pluck('programmeInstance.programme.title')->filter()->unique();
                                        @endphp
                                        @if($programmes->count() > 0)
                                            {{ $programmes->join(', ') }}
                                        @else
                                            <span class="text-gray-400">No programmes</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if($students->count() === 0)
                    <div class="text-center py-8 text-gray-500">
                        No students found.
                    </div>
                @endif
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="text-sm text-gray-500">
                    Showing columns: <span x-text="visibleColumns.join(', ')"></span>
                </div>
            </div>
        </x-card>
    </div>
</x-wide-layout>