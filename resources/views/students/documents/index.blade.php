<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Documents</h1>
                <p class="text-gray-600 mt-1">Manage documents for {{ $student->full_name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('students.documents.create', $student) }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors cursor-pointer"
                   style="background-color: #2563eb !important; color: white !important; cursor: pointer !important;">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Upload Documents
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="bg-white shadow-sm rounded-lg border mb-6">
                <div class="p-4">
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <div>
                            <select name="type" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">All Document Types</option>
                                @foreach($documentTypes as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="status" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">All Statuses</option>
                                <option value="uploaded" {{ request('status') === 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-toc-600 text-white rounded-md hover:bg-toc-700 transition-colors text-sm">
                            Filter
                        </button>
                        @if(request()->hasAny(['type', 'status']))
                            <a href="{{ route('students.documents.index', $student) }}" 
                               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors text-sm">
                                Clear Filters
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Documents List -->
            @if($documents->count() > 0)
                <div class="bg-white shadow-sm rounded-lg border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Documents ({{ $documents->total() }})</h3>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        @foreach($documents as $document)
                            <div class="p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="text-lg font-medium text-gray-900">
                                                {{ $document->title }}
                                            </h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document->status_color }}">
                                                {{ ucfirst($document->status) }}
                                            </span>
                                        </div>
                                        
                                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-2">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $document->document_type_label }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                {{ $document->original_filename }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                                </svg>
                                                {{ $document->formatted_file_size }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $document->uploaded_at->format('M d, Y \a\t g:i A') }}
                                            </span>
                                        </div>

                                        @if($document->description)
                                            <p class="text-sm text-gray-600 mb-2">{{ $document->description }}</p>
                                        @endif

                                        <div class="text-xs text-gray-500">
                                            Uploaded by {{ $document->uploadedBy->name }}
                                            @if($document->verified_by)
                                                • {{ ucfirst($document->status) }} by {{ $document->verifiedBy->name }} on {{ $document->verified_at->format('M d, Y') }}
                                            @endif
                                        </div>

                                        @if($document->rejection_reason)
                                            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md">
                                                <p class="text-sm text-red-800"><strong>Rejection Reason:</strong> {{ $document->rejection_reason }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center space-x-2 ml-4">
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

                                        @if(in_array(Auth::user()->role, ['manager', 'student_services']))
                                            @if($document->status === 'uploaded')
                                                <form method="POST" action="{{ route('student-documents.verify', $document) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                        Verify
                                                    </button>
                                                </form>
                                                
                                                <button onclick="showRejectModal({{ $document->id }})" 
                                                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Reject
                                                </button>
                                            @endif
                                        @endif

                                        @if(Auth::user()->role === 'student' && Auth::user()->student_id === $student->id)
                                            <form method="POST" action="{{ route('student-documents.destroy', $document) }}" 
                                                  class="inline" onsubmit="return confirm('Are you sure you want to delete this document?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($documents->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $documents->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white shadow-sm rounded-lg border">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No documents uploaded</h3>
                        <p class="mt-2 text-gray-600">Get started by uploading your first document.</p>
                        <div class="mt-6">
                            <a href="{{ route('students.documents.create', $student) }}" 
                               class="bg-toc-600 text-white px-4 py-2 rounded-lg hover:bg-toc-700 transition-colors">
                                Upload Documents
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Document</h3>
                        <div class="mb-4">
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for rejection:
                            </label>
                            <textarea name="rejection_reason" id="rejection_reason" required rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                      placeholder="Please provide a reason for rejecting this document..."></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" onclick="hideRejectModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Reject Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showRejectModal(documentId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/student-documents/${documentId}/reject`;
            modal.classList.remove('hidden');
        }

        function hideRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
            document.getElementById('rejection_reason').value = '';
        }

        // Close modal when clicking outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideRejectModal();
            }
        });
    </script>
</x-app-layout>