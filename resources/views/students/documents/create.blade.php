<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Upload Documents</h1>
                <p class="text-gray-600 mt-1">Upload documents for {{ $student->full_name }}</p>
            </div>
            <a href="{{ route('students.documents.index', $student) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors cursor-pointer">
                ← Back to Documents
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Upload Form -->
            <div class="bg-white shadow-sm rounded-lg border">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Upload New Documents</h3>
                    @if (session('error'))
                        <div class="mt-3 bg-red-50 border border-red-200 rounded-md p-3">
                            <div class="text-sm text-red-800">
                                <strong>Upload failed:</strong> {{ session('error') }}
                            </div>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="mt-3 bg-green-50 border border-green-200 rounded-md p-3">
                            <div class="text-sm text-green-800">
                                {{ session('success') }}
                            </div>
                        </div>
                    @endif
                </div>
                
                <form action="{{ route('students.documents.store', $student) }}" method="POST" enctype="multipart/form-data" class="p-6" onsubmit="return validateForm()">
                    @csrf
                    
                    <!-- Document Type Selection -->
                    <div class="mb-6">
                        <label for="document_type" class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                        <select name="document_type" id="document_type" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-toc-500 focus:border-toc-500">
                            @foreach($documentTypes as $key => $label)
                                <option value="{{ $key }}" {{ $documentType === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Title (Optional) -->
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title (Optional)</label>
                        <input type="text" name="title" id="title" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-toc-500 focus:border-toc-500"
                               placeholder="e.g., Diploma in Business Management">
                    </div>

                    <!-- Description (Optional) -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea name="description" id="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-toc-500 focus:border-toc-500"
                                  placeholder="Additional notes about this document..."></textarea>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-6">
                        <label for="files" class="block text-sm font-medium text-gray-700 mb-2">Select Files</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" name="files[]" id="files" multiple accept=".pdf,.jpg,.jpeg,.png,.gif" required
                                   class="block w-full text-sm text-gray-500 border border-gray-300 rounded-md p-2 mb-2">
                            <div id="upload-area" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">
                                    <span class="font-medium text-toc-600 hover:text-toc-500">Click to select files</span>
                                    or drag and drop
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    PDF, JPG, PNG, GIF up to 10MB each
                                </p>
                            </div>
                            
                            <!-- Selected Files Display -->
                            <div id="selected-files" class="mt-4 hidden">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Files:</h4>
                                <div id="files-list" class="space-y-2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('students.documents.index', $student) }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors cursor-pointer"
                                style="background-color: #2563eb !important; color: white !important; cursor: pointer !important;">
                            Upload Documents
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Information -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-800 mb-2">Upload Guidelines</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Accepted formats: PDF, JPG, PNG, GIF</li>
                    <li>• Maximum file size: 10MB per file</li>
                    <li>• You can upload multiple files at once</li>
                    <li>• Files are securely stored and only accessible to authorized users</li>
                    <li>• Duplicate files will be automatically detected</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('files');
            const uploadArea = document.getElementById('upload-area');
            const selectedFilesDiv = document.getElementById('selected-files');
            const filesList = document.getElementById('files-list');

            // Click to select files
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });

            // Drag and drop functionality
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('border-toc-500', 'bg-toc-50');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('border-toc-500', 'bg-toc-50');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('border-toc-500', 'bg-toc-50');
                
                const files = e.dataTransfer.files;
                
                // Create a new DataTransfer object to properly set files
                const dt = new DataTransfer();
                for (let i = 0; i < files.length; i++) {
                    dt.items.add(files[i]);
                }
                fileInput.files = dt.files;
                
                displaySelectedFiles(files);
            });

            // File selection change
            fileInput.addEventListener('change', function() {
                displaySelectedFiles(this.files);
            });

            function displaySelectedFiles(files) {
                if (files.length === 0) {
                    selectedFilesDiv.classList.add('hidden');
                    return;
                }

                selectedFilesDiv.classList.remove('hidden');
                filesList.innerHTML = '';

                Array.from(files).forEach(function(file) {
                    const fileDiv = document.createElement('div');
                    fileDiv.className = 'flex items-center justify-between bg-gray-100 px-3 py-2 rounded text-sm';
                    
                    const fileName = document.createElement('span');
                    fileName.textContent = file.name;
                    
                    const fileSize = document.createElement('span');
                    fileSize.className = 'text-gray-500';
                    fileSize.textContent = formatFileSize(file.size);
                    
                    fileDiv.appendChild(fileName);
                    fileDiv.appendChild(fileSize);
                    filesList.appendChild(fileDiv);
                });
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
        
        // Form validation function
        function validateForm() {
            const fileInput = document.getElementById('files');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            console.log('Form validation - Files selected:', fileInput.files.length);
            
            if (fileInput.files.length === 0) {
                alert('Please select at least one file to upload.');
                return false;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            submitBtn.style.opacity = '0.6';
            
            return true;
        }
    </script>
</x-app-layout>