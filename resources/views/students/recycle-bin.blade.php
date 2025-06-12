{{-- resources/views/students/recycle-bin.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🗑️ Student Recycle Bin
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Deleted students that can be restored or permanently deleted
                </p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('students.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    ← Back to Students
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($deletedStudents->count() > 0)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                Showing {{ $deletedStudents->count() }} deleted student(s). 
                                Students can be restored or permanently deleted from here.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Deleted Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($deletedStudents as $student)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold text-sm">
                                                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $student->full_name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $student->student_number }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $student->email }}</div>
                                                <div class="text-sm text-gray-500">{{ $student->phone ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($student->status === 'active') bg-green-100 text-green-800
                                                    @elseif($student->status === 'deferred') bg-yellow-100 text-yellow-800
                                                    @elseif($student->status === 'completed') bg-blue-100 text-blue-800
                                                    @elseif($student->status === 'cancelled') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ ucfirst($student->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $student->deleted_at->format('d M Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <form method="POST" action="{{ route('students.restore', $student->id) }}" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button 
                                                        type="submit"
                                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-xs"
                                                        title="Restore student"
                                                    >
                                                        ↻ Restore
                                                    </button>
                                                </form>
                                                
                                                <button 
                                                    onclick="confirmPermanentDelete('{{ $student->full_name }}', '{{ route('students.force-delete', $student->id) }}')"
                                                    class="bg-red-600 hover:bg-red-800 text-white font-bold py-1 px-3 rounded text-xs"
                                                    title="Permanently delete student"
                                                >
                                                    ✗ Delete Forever
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $deletedStudents->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Recycle bin is empty</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                No deleted students to show. Deleted students will appear here where they can be restored.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Permanent Delete Confirmation Modal -->
    <div id="permanentDeleteModal" class="fixed inset-0 z-50 hidden">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closePermanentDeleteModal()"></div>
        
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
                        Permanently Delete Student
                    </h3>
                    
                    <!-- Message -->
                    <p class="text-sm text-gray-500 text-center mb-6">
                        Are you sure you want to <strong>permanently delete</strong> <strong id="permanentStudentName"></strong>? 
                        <br><br>
                        <span class="text-red-600 font-medium">This action cannot be undone!</span>
                    </p>
                    
                    <!-- Buttons -->
                    <div class="flex justify-center space-x-3">
                        <button 
                            type="button" 
                            onclick="closePermanentDeleteModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                        
                        <form id="permanentDeleteForm" method="POST" style="display: inline;" onsubmit="console.log('Permanent delete form submitting to:', this.action);">
                            @csrf
                            @method('DELETE')
                            <button 
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                onclick="console.log('Permanent delete button clicked');"
                            >
                                Delete Forever
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmPermanentDelete(studentName, deleteUrl) {
            console.log('confirmPermanentDelete called:', studentName, deleteUrl);
            
            const permanentStudentNameEl = document.getElementById('permanentStudentName');
            const permanentDeleteFormEl = document.getElementById('permanentDeleteForm');
            const permanentDeleteModalEl = document.getElementById('permanentDeleteModal');
            
            if (!permanentStudentNameEl || !permanentDeleteFormEl || !permanentDeleteModalEl) {
                console.error('Permanent delete modal elements not found:', {
                    permanentStudentName: !!permanentStudentNameEl,
                    permanentDeleteForm: !!permanentDeleteFormEl,
                    permanentDeleteModal: !!permanentDeleteModalEl
                });
                return;
            }
            
            permanentStudentNameEl.textContent = studentName;
            permanentDeleteFormEl.action = deleteUrl;
            permanentDeleteModalEl.classList.remove('hidden');
            
            console.log('Permanent delete modal should now be visible');
        }

        function closePermanentDeleteModal() {
            const permanentDeleteModalEl = document.getElementById('permanentDeleteModal');
            if (permanentDeleteModalEl) {
                permanentDeleteModalEl.classList.add('hidden');
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePermanentDeleteModal();
            }
        });

        // Test that elements exist when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Permanent delete modal elements check:', {
                permanentStudentName: !!document.getElementById('permanentStudentName'),
                permanentDeleteForm: !!document.getElementById('permanentDeleteForm'),
                permanentDeleteModal: !!document.getElementById('permanentDeleteModal')
            });
        });
    </script>
</x-app-layout>