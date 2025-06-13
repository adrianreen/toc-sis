{{-- resources/views/students/show.blade.php --}}
<x-app-layout>
<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Student Details: {{ $student->full_name }}
        </h2>
        <div class="hidden sm:block">
            <a href="{{ route('admin.student-progress', $student) }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                View Progress
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

            <!-- Student Information and Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                <!-- Student Information -->
                <div class="lg:col-span-3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Student Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                            <p class="text-sm text-gray-600">Student Number</p>
                            <p class="font-medium">{{ $student->student_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($student->status === 'active') bg-green-100 text-green-800
                                @elseif($student->status === 'deferred') bg-yellow-100 text-yellow-800
                                @elseif($student->status === 'completed') bg-blue-100 text-blue-800
                                @elseif($student->status === 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($student->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium">{{ $student->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Phone</p>
                            <p class="font-medium">{{ $student->phone ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Date of Birth</p>
                            <p class="font-medium">{{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">County</p>
                            <p class="font-medium">{{ $student->county ?? '-' }}</p>
                        </div>
                    </div>
                    @if($student->address || $student->city || $student->eircode)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Address</p>
                            <p class="font-medium">
                                {{ $student->address }}{{ $student->address && $student->city ? ', ' : '' }}
                                {{ $student->city }}{{ $student->city && $student->eircode ? ', ' : '' }}
                                {{ $student->eircode }}
                            </p>
                        </div>
                    @endif
                    @if($student->notes)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Notes</p>
                            <p class="font-medium">{{ $student->notes }}</p>
                        </div>
                    @endif
                    </div>
                </div>

                <!-- Quick Actions Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            <h3 class="text-base font-semibold mb-4">Quick Actions</h3>
                            
                            <!-- Mobile Progress Button -->
                            <div class="sm:hidden mb-3">
                                <a href="{{ route('admin.student-progress', $student) }}" 
                                   class="w-full inline-flex justify-center items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                    View Progress
                                </a>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                <a href="{{ route('transcripts.download', $student) }}" 
                                   class="w-full inline-flex items-center justify-center px-3 py-2 border border-orange-300 rounded-md shadow-sm bg-orange-50 text-sm font-medium text-orange-700 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition duration-150 ease-in-out">
                                    Download Transcript
                                </a>
                                <a href="{{ route('enrolments.create', $student) }}" 
                                   class="w-full inline-flex items-center justify-center px-3 py-2 border border-emerald-300 rounded-md shadow-sm bg-emerald-50 text-sm font-medium text-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-150 ease-in-out">
                                    Enrol in Programme
                                </a>
                                <a href="{{ route('students.edit', $student) }}" 
                                   class="w-full inline-flex items-center justify-center px-3 py-2 border border-blue-300 rounded-md shadow-sm bg-blue-50 text-sm font-medium text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                                    Edit Details
                                </a>
                                @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                                    <button 
                                        onclick="confirmDelete('{{ $student->full_name }}', '{{ route('students.destroy', $student) }}')"
                                        class="w-full inline-flex items-center justify-center px-3 py-2 border border-red-300 rounded-md shadow-sm bg-red-50 text-sm font-medium text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out cursor-pointer"
                                    >
                                        Delete Student
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Programme Enrolments</h3>
                    @if($student->enrolments->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Programme
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cohort
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Enrolment Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Expected Completion
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($student->enrolments as $enrolment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $enrolment->programme->code }} - {{ $enrolment->programme->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->cohort ? $enrolment->cohort->code : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->enrolment_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->expected_completion_date ? $enrolment->expected_completion_date->format('d M Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($enrolment->status === 'active') bg-green-100 text-green-800
                                                @elseif($enrolment->status === 'deferred') bg-yellow-100 text-yellow-800
                                                @elseif($enrolment->status === 'completed') bg-blue-100 text-blue-800
                                                @elseif($enrolment->status === 'withdrawn') bg-orange-100 text-orange-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($enrolment->status) }}
                                            </span>
                                        </td>
                                        {{-- START: MODIFIED SECTION --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($enrolment->status === 'active' && $enrolment->programme->isCohortBased())
                                                <a href="{{ route('deferrals.create', [$student, $enrolment]) }}"
                                                   class="text-yellow-600 hover:text-yellow-900 mr-2">
                                                    Defer
                                                </a>
                                            @elseif($enrolment->status === 'deferred')
                                                <span class="text-gray-500">Deferred</span>
                                            @endif
                                            {{-- You might want to add other actions here, e.g., a "View Details" link for the enrolment itself --}}
                                            {{-- or an "Edit Enrolment" link if applicable --}}
                                        </td>
                                        {{-- END: MODIFIED SECTION --}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No programme enrolments yet.</p>
                    @endif
                </div>
            </div>
        </div>
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
</x-app-layout>