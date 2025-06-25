{{-- resources/views/policies/create.blade.php --}}
<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Header Section --}}
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('policies.manage') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-toc-600 cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                                Policy Management
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Create Policy</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                
                <div class="mt-4">
                    <h1 class="text-3xl font-bold text-gray-900">Create New Policy</h1>
                    <p class="mt-2 text-gray-600">Add a new policy document for students and staff</p>
                </div>
            </div>

            {{-- Create Form --}}
            <x-card>
                <div class="p-8">
                    <form action="{{ route('policies.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf

                        {{-- Basic Information --}}
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                            <div class="grid grid-cols-1 gap-6">
                                {{-- Title --}}
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                        Policy Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           name="title" 
                                           id="title"
                                           value="{{ old('title') }}"
                                           required
                                           class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500 @error('title') border-red-300 @enderror"
                                           placeholder="e.g., Assessment Submission Guidelines">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea name="description" 
                                              id="description"
                                              rows="3"
                                              class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500 @error('description') border-red-300 @enderror"
                                              placeholder="Brief description of what this policy covers...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">This will be shown in the policy listing and search results.</p>
                                </div>

                                {{-- Category --}}
                                <div>
                                    <label for="policy_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select name="policy_category_id" 
                                            id="policy_category_id"
                                            required
                                            class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500 @error('policy_category_id') border-red-300 @enderror">
                                        <option value="">Select a category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('policy_category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('policy_category_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Scope and Access --}}
                        <div class="border-t border-gray-200 pt-8">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Scope and Access</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Scope --}}
                                <div>
                                    <label for="scope" class="block text-sm font-medium text-gray-700 mb-2">
                                        Policy Scope <span class="text-red-500">*</span>
                                    </label>
                                    <select name="scope" 
                                            id="scope"
                                            required
                                            class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500 @error('scope') border-red-300 @enderror">
                                        <option value="">Select scope</option>
                                        <option value="college" {{ old('scope') === 'college' ? 'selected' : '' }}>College-wide</option>
                                        <option value="programme" {{ old('scope') === 'programme' ? 'selected' : '' }}>Programme-specific</option>
                                    </select>
                                    @error('scope')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">College-wide policies apply to all students, programme-specific only to certain programmes.</p>
                                </div>

                                {{-- Programme Type --}}
                                <div>
                                    <label for="programme_type" class="block text-sm font-medium text-gray-700 mb-2">
                                        Programme Type <span class="text-red-500">*</span>
                                    </label>
                                    <select name="programme_type" 
                                            id="programme_type"
                                            required
                                            class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500 @error('programme_type') border-red-300 @enderror">
                                        <option value="">Select programme type</option>
                                        <option value="all" {{ old('programme_type') === 'all' ? 'selected' : '' }}>All Programmes</option>
                                        <option value="elc" {{ old('programme_type') === 'elc' ? 'selected' : '' }}>ELC Programmes</option>
                                        <option value="degree_obu" {{ old('programme_type') === 'degree_obu' ? 'selected' : '' }}>Degree (OBU) Programmes</option>
                                        <option value="qqi" {{ old('programme_type') === 'qqi' ? 'selected' : '' }}>QQI Programmes</option>
                                    </select>
                                    @error('programme_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="border-t border-gray-200 pt-8">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Policy Content</h2>
                            
                            {{-- File Upload --}}
                            <div class="mb-6">
                                <label for="policy_file" class="block text-sm font-medium text-gray-700 mb-2">
                                    Policy Document (PDF)
                                </label>
                                <div class="mt-1">
                                    <!-- Beautiful File Upload Zone -->
                                    <div id="upload-zone" class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition-all duration-300 bg-gradient-to-br from-gray-50 to-white">
                                        <!-- Hidden File Input -->
                                        <input type="file" 
                                               id="policy_file" 
                                               name="policy_file" 
                                               accept=".pdf"
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        
                                        <!-- Upload Icon & Text -->
                                        <div id="upload-prompt" class="pointer-events-none">
                                            <div class="mx-auto w-16 h-16 mb-4 flex items-center justify-center bg-blue-100 rounded-full">
                                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Upload Policy Document</h3>
                                            <p class="text-sm text-gray-600 mb-1">
                                                <span class="font-medium text-blue-600 hover:text-blue-500 cursor-pointer">Click to browse</span> or drag and drop your PDF file here
                                            </p>
                                            <p class="text-xs text-gray-500">PDF files up to 10MB</p>
                                        </div>
                                        
                                        <!-- File Preview (Hidden by default) -->
                                        <div id="file-preview" class="hidden pointer-events-none">
                                            <div class="mx-auto w-16 h-16 mb-4 flex items-center justify-center bg-green-100 rounded-full">
                                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">File Ready to Upload</h3>
                                            <div class="bg-white border border-gray-200 rounded-lg p-4 mx-auto max-w-sm">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-shrink-0">
                                                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p id="file-name" class="text-sm font-medium text-gray-900 truncate"></p>
                                                        <p id="file-size" class="text-sm text-gray-500"></p>
                                                    </div>
                                                    <button type="button" id="remove-file" class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Drag Overlay -->
                                        <div id="drag-overlay" class="absolute inset-0 bg-blue-50 border-2 border-blue-400 rounded-xl hidden pointer-events-none">
                                            <div class="flex items-center justify-center h-full">
                                                <div class="text-center">
                                                    <div class="mx-auto w-16 h-16 mb-4 flex items-center justify-center bg-blue-200 rounded-full">
                                                        <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                        </svg>
                                                    </div>
                                                    <p class="text-lg font-semibold text-blue-700">Drop your PDF file here</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('policy_file')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Text Content --}}
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                    Additional Text Content
                                </label>
                                <textarea name="content" 
                                          id="content"
                                          rows="8"
                                          class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500 @error('content') border-red-300 @enderror"
                                          placeholder="Optional: Add text content that will be displayed on the policy page...">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">This text will be displayed alongside the PDF download. Leave blank if the PDF contains all necessary information.</p>
                            </div>
                        </div>

                        {{-- Publication Settings --}}
                        <div class="border-t border-gray-200 pt-8">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Publication Settings</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Status --}}
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" 
                                            id="status"
                                            required
                                            class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500 @error('status') border-red-300 @enderror">
                                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">Drafts are only visible to staff. Published policies are visible to students.</p>
                                </div>

                                {{-- Publication Date --}}
                                <div>
                                    <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">
                                        Publication Date
                                    </label>
                                    <input type="datetime-local" 
                                           name="published_at" 
                                           id="published_at"
                                           value="{{ old('published_at') }}"
                                           class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500 @error('published_at') border-red-300 @enderror">
                                    @error('published_at')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">Leave blank to publish immediately when status is set to "Published".</p>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="border-t border-gray-200 pt-8">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('policies.manage') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-800 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Cancel
                                </a>

                                <div class="flex space-x-3">
                                    <button type="submit" 
                                            name="action" 
                                            value="save_draft"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-800 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                        </svg>
                                        Save as Draft
                                    </button>
                                    
                                    <button type="submit" 
                                            name="action" 
                                            value="publish"
                                            class="inline-flex items-center px-4 py-2 rounded-lg font-medium text-sm transition-colors cursor-pointer"
                                            style="background-color: #2563eb; color: #ffffff; border: none;"
                                            onmouseover="this.style.backgroundColor='#1d4ed8'"
                                            onmouseout="this.style.backgroundColor='#2563eb'">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Create Policy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </x-card>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const statusSelect = document.getElementById('status');
            const fileInput = document.getElementById('policy_file');
            const uploadZone = document.getElementById('upload-zone');
            const uploadPrompt = document.getElementById('upload-prompt');
            const filePreview = document.getElementById('file-preview');
            const fileName = document.getElementById('file-name');
            const fileSize = document.getElementById('file-size');
            const removeButton = document.getElementById('remove-file');
            const dragOverlay = document.getElementById('drag-overlay');
            
            // Handle form submission
            if (form && statusSelect) {
                form.addEventListener('submit', function(e) {
                    const actionButton = e.submitter;
                    if (actionButton && actionButton.name === 'action') {
                        if (actionButton.value === 'save_draft') {
                            statusSelect.value = 'draft';
                        } else if (actionButton.value === 'publish') {
                            statusSelect.value = 'published';
                        }
                    }
                });
            }
            
            // File handling functions
            function showFilePreview(file) {
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                uploadPrompt.classList.add('hidden');
                filePreview.classList.remove('hidden');
            }
            
            function hideFilePreview() {
                uploadPrompt.classList.remove('hidden');
                filePreview.classList.add('hidden');
                fileInput.value = '';
            }
            
            function validateFile(file) {
                if (file.type !== 'application/pdf') {
                    alert('Please select a PDF file only.');
                    return false;
                }
                
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB.');
                    return false;
                }
                
                return true;
            }
            
            // File input change handler
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && validateFile(file)) {
                    showFilePreview(file);
                } else if (!file) {
                    hideFilePreview();
                }
            });
            
            // Remove file button
            removeButton.addEventListener('click', function(e) {
                e.preventDefault();
                hideFilePreview();
            });
            
            // Drag and drop functionality
            let dragCounter = 0;
            
            uploadZone.addEventListener('dragenter', function(e) {
                e.preventDefault();
                dragCounter++;
                dragOverlay.classList.remove('hidden');
            });
            
            uploadZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dragCounter--;
                if (dragCounter <= 0) {
                    dragOverlay.classList.add('hidden');
                    dragCounter = 0;
                }
            });
            
            uploadZone.addEventListener('dragover', function(e) {
                e.preventDefault();
            });
            
            uploadZone.addEventListener('drop', function(e) {
                e.preventDefault();
                dragCounter = 0;
                dragOverlay.classList.add('hidden');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (validateFile(file)) {
                        fileInput.files = files;
                        showFilePreview(file);
                    }
                }
            });
            
            // Hover effects
            uploadZone.addEventListener('mouseenter', function() {
                if (uploadPrompt && !uploadPrompt.classList.contains('hidden')) {
                    uploadZone.style.borderColor = '#3b82f6';
                    uploadZone.style.backgroundColor = '#f8fafc';
                }
            });
            
            uploadZone.addEventListener('mouseleave', function() {
                if (uploadPrompt && !uploadPrompt.classList.contains('hidden')) {
                    uploadZone.style.borderColor = '#d1d5db';
                    uploadZone.style.backgroundColor = '';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>