{{-- resources/views/students/show.blade.php --}}
<x-app-layout>
<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Student Details: {{ $student->full_name }}
        </h2>
        <div class="hidden sm:block">
            <a href="{{ route('students.show-progress', $student) }}" 
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
                                <a href="{{ route('students.show-progress', $student) }}" 
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

                            <!-- Email Actions Section -->
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Email Actions</h4>
                                <div class="space-y-2">
                                    <!-- Quick Send Buttons -->
                                    <form method="POST" action="{{ route('student-emails.quick-send', $student) }}">
                                        @csrf
                                        <input type="hidden" name="action" value="results_transcript">
                                        <button type="submit" 
                                                class="w-full inline-flex items-center justify-center px-3 py-2 border border-purple-300 rounded-md shadow-sm bg-purple-50 text-sm font-medium text-purple-700 hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150 ease-in-out cursor-pointer">
                                            Send Results & Transcript
                                        </button>
                                    </form>

                                    @if($student->status === 'enquiry')
                                        <form method="POST" action="{{ route('student-emails.quick-send', $student) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="welcome">
                                            <button type="submit" 
                                                    class="w-full inline-flex items-center justify-center px-3 py-2 border border-green-300 rounded-md shadow-sm bg-green-50 text-sm font-medium text-green-700 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out cursor-pointer">
                                                Send Welcome Email
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Custom Email Compose -->
                                    <a href="{{ route('student-emails.compose', $student) }}" 
                                       class="w-full inline-flex items-center justify-center px-3 py-2 border border-indigo-300 rounded-md shadow-sm bg-indigo-50 text-sm font-medium text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                        Compose Custom Email
                                    </a>

                                    <!-- Email History -->
                                    <a href="{{ route('student-emails.index', $student) }}" 
                                       class="w-full inline-flex items-center justify-center px-3 py-2 border border-slate-300 rounded-md shadow-sm bg-slate-50 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition duration-150 ease-in-out">
                                        View Email History
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Enrolments</h3>
                    @if($student->enrolments->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Programme/Module
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Instance
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Enrolment Date
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
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $enrolment->enrolment_type === 'programme' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ ucfirst($enrolment->enrolment_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($enrolment->enrolment_type === 'programme')
                                                {{ $enrolment->programmeInstance->programme->title }}
                                            @else
                                                {{ $enrolment->moduleInstance->module->title }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($enrolment->enrolment_type === 'programme')
                                                {{ $enrolment->programmeInstance->label }}
                                            @else
                                                {{ $enrolment->moduleInstance->label ?? 'Default Instance' }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrolment->enrolment_date->format('d M Y') }}
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                @if($enrolment->status === 'active' && $enrolment->enrolment_type === 'programme')
                                                    <a href="{{ route('deferrals.create', [$student, $enrolment]) }}"
                                                       class="text-yellow-600 hover:text-yellow-900">
                                                        Defer
                                                    </a>
                                                @elseif($enrolment->status === 'deferred')
                                                    <span class="text-gray-500">Deferred</span>
                                                @endif
                                                
                                                @if($enrolment->enrolment_type === 'programme')
                                                    <a href="{{ route('programme-instances.show', $enrolment->programmeInstance) }}"
                                                       class="text-blue-600 hover:text-blue-900">
                                                        View
                                                    </a>
                                                @else
                                                    <a href="{{ route('module-instances.show', $enrolment->moduleInstance) }}"
                                                       class="text-blue-600 hover:text-blue-900">
                                                        View
                                                    </a>
                                                @endif
                                                
                                                {{-- Unenroll button for admin error correction --}}
                                                @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                                                    <a href="{{ route('enrolments.unenroll-form', $enrolment) }}"
                                                       class="text-red-600 hover:text-red-900"
                                                       title="Unenroll student (admin correction)">
                                                        Unenroll
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No enrolments yet.</p>
                    @endif
                </div>
            </div>

            <!-- Documents Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Documents</h3>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('students.documents.create', $student) }}?type=rpl_proof" 
                               class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors cursor-pointer"
                               style="background-color: #2563eb !important; color: white !important; cursor: pointer !important;">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Upload RPL Documents
                            </a>
                            <a href="{{ route('students.documents.index', $student) }}" 
                               class="text-toc-600 hover:text-toc-800 text-sm font-medium cursor-pointer">
                                View All
                            </a>
                        </div>
                    </div>
                    
                    @if($student->documents->count() > 0)
                        <div class="space-y-3">
                            @foreach($student->documents->take(5) as $document)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <p class="font-medium text-gray-900">{{ $document->title }}</p>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $document->status_color }}">
                                                {{ ucfirst($document->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $document->document_type_label }} • 
                                            {{ $document->formatted_file_size }} • 
                                            Uploaded {{ $document->uploaded_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($document->mime_type === 'application/pdf')
                                            <a href="{{ route('student-documents.view', $document) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm font-medium" target="_blank">
                                                View
                                            </a>
                                        @endif
                                        <a href="{{ route('student-documents.download', $document) }}" 
                                           class="text-green-600 hover:text-green-800 text-sm font-medium">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($student->documents->count() > 5)
                            <div class="mt-4 text-center">
                                <a href="{{ route('students.documents.index', $student) }}" 
                                   class="text-toc-600 hover:text-toc-800 text-sm font-medium">
                                    View all {{ $student->documents->count() }} documents →
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-gray-600 text-sm">No documents uploaded yet.</p>
                            <a href="{{ route('students.documents.create', $student) }}?type=rpl_proof" 
                               class="mt-2 inline-flex items-center text-toc-600 hover:text-toc-800 text-sm font-medium">
                                Upload your first document →
                            </a>
                        </div>
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