<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Email Template
            </h2>
            <a href="{{ route('admin.email-templates.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                Back to Templates
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.email-templates.store') }}">
                        @csrf

                        <!-- Template Starters -->
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-900 mb-2">Quick Start Templates</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <button type="button" onclick="loadTemplate('grade_notification')" 
                                        class="text-left px-3 py-2 text-sm bg-white border border-blue-200 rounded hover:bg-blue-100 transition-colors cursor-pointer">
                                    <div class="font-medium text-blue-900">Grade Released</div>
                                    <div class="text-blue-700 text-xs">Notify students when grades are available</div>
                                </button>
                                <button type="button" onclick="loadTemplate('welcome')" 
                                        class="text-left px-3 py-2 text-sm bg-white border border-blue-200 rounded hover:bg-blue-100 transition-colors cursor-pointer">
                                    <div class="font-medium text-blue-900">Welcome Email</div>
                                    <div class="text-blue-700 text-xs">Welcome new students to programme</div>
                                </button>
                                <button type="button" onclick="loadTemplate('reminder')" 
                                        class="text-left px-3 py-2 text-sm bg-white border border-blue-200 rounded hover:bg-blue-100 transition-colors cursor-pointer">
                                    <div class="font-medium text-blue-900">Deadline Reminder</div>
                                    <div class="text-blue-700 text-xs">Remind students of upcoming deadlines</div>
                                </button>
                            </div>
                        </div>

                        <!-- Preview Toggle Button -->
                        <div class="mb-4 flex justify-end">
                            <button type="button" 
                                    id="toggle-preview" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Toggle Preview
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-6" id="editor-container">
                            <!-- Left Column - Editor -->
                            <div id="editor-column">
                                <!-- Template Name -->
                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Template Name *
                                    </label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}"
                                           required
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                           placeholder="e.g., Grade Released Notification">
                                </div>

                                <!-- Subject -->
                                <div class="mb-4">
                                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Subject *
                                    </label>
                                    <input type="text" 
                                           id="subject" 
                                           name="subject" 
                                           value="{{ old('subject') }}"
                                           required
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Your assessment results are now available">
                                </div>

                                <!-- Category -->
                                <div class="mb-4">
                                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                        Category *
                                    </label>
                                    <select id="category" 
                                            name="category" 
                                            required
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select Category</option>
                                        <option value="academic" {{ old('category') === 'academic' ? 'selected' : '' }}>Academic</option>
                                        <option value="administrative" {{ old('category') === 'administrative' ? 'selected' : '' }}>Administrative</option>
                                        <option value="system" {{ old('category') === 'system' ? 'selected' : '' }}>System</option>
                                    </select>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                        Description
                                    </label>
                                    <textarea id="description" 
                                              name="description" 
                                              rows="3"
                                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                              placeholder="Brief description of when this template is used...">{{ old('description') }}</textarea>
                                </div>

                                <!-- Status -->
                                <div class="mb-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                            Active (template can be used for sending emails)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Available Variables -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Available Variables</h3>
                                <div class="bg-gray-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                                    <p class="text-sm text-gray-600 mb-3">
                                        Click to copy variables or drag them directly into your template.
                                    </p>
                                    
                                    @if(isset($availableVariables))
                                        @foreach($availableVariables as $category => $variables)
                                            <div class="mb-4">
                                                <h4 class="font-medium text-gray-800 mb-2">{{ ucfirst($category) }}</h4>
                                                <div class="space-y-1">
                                                    @foreach($variables as $variable => $description)
                                                        <div class="text-sm flex items-center group">
                                                            <code class="bg-white border border-gray-300 px-2 py-1 rounded text-xs font-mono cursor-grab hover:cursor-grabbing hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 flex-shrink-0 group-hover:shadow-sm" 
                                                                  data-variable="{{ $variable }}"
                                                                  draggable="true"
                                                                  ondragstart="handleDragStart(event)"
                                                                  onclick="copyToClipboard('{{ $variable }}')">{!! '{{' . $variable . '}}' !!}</code>
                                                            <div class="ml-2 flex-1 min-w-0">
                                                                <span class="text-gray-600 text-xs">{{ $description }}</span>
                                                            </div>
                                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 ml-2">
                                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-red-600">No available variables found!</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- HTML Body -->
                        <div class="mt-6">
                            <label for="body_html" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Content *
                            </label>
                            <!-- Quill Editor Container -->
                            <div class="relative">
                                <div id="quill-editor" 
                                     style="height: 300px;"
                                     ondrop="handleDrop(event)" 
                                     ondragover="handleDragOver(event)"
                                     ondragenter="handleDragEnter(event)"
                                     ondragleave="handleDragLeave(event)"></div>
                                <div id="drop-indicator" class="hidden absolute inset-0 bg-blue-100 border-2 border-dashed border-blue-400 rounded-lg flex items-center justify-center z-10">
                                    <div class="text-blue-600 font-medium">
                                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                        Drop variable here
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden textarea for form submission -->
                            <textarea id="body_html" name="body_html" required style="display: none;">{{ old('body_html') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Use the rich text editor to format your email. Variables like <code>{!! '{{' . 'student.name' . '}}' !!}</code> will be replaced automatically.
                                <br><strong>Tips:</strong> Drag variables from sidebar into editor • <kbd class="bg-gray-100 px-1 rounded">Ctrl+S</kbd> to save • <kbd class="bg-gray-100 px-1 rounded">Ctrl+P</kbd> to preview • <kbd class="bg-gray-100 px-1 rounded">Ctrl+Shift+V</kbd> to validate
                            </p>
                        </div>

                        <!-- Text Body (Optional) -->
                        <div class="mt-6">
                            <label for="body_text" class="block text-sm font-medium text-gray-700 mb-1">
                                Plain Text Version (Optional)
                            </label>
                            <textarea id="body_text" 
                                      name="body_text" 
                                      rows="10"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                                      placeholder="Plain text version of your email (recommended for accessibility)...">{{ old('body_text') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                If not provided, a plain text version will be automatically generated from the HTML content.
                            </p>
                        </div>
                        
                        <!-- Close editor column -->
                        </div>

                        <!-- Preview Column (initially hidden) -->
                        <div id="preview-column" class="hidden">
                            <div class="bg-gray-50 rounded-lg p-6 border">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Live Preview</h3>
                                    <div class="flex space-x-2">
                                        <button type="button" id="preview-desktop" class="px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 transition-colors cursor-pointer">Desktop</button>
                                        <button type="button" id="preview-mobile" class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors cursor-pointer">Mobile</button>
                                    </div>
                                </div>
                                
                                <!-- Email Preview Container -->
                                <div id="email-preview" class="bg-white rounded border shadow-sm transition-all duration-300" style="max-width: 600px;">
                                    <!-- Email Header -->
                                    <div class="border-b border-gray-200 p-4 bg-gray-50">
                                        <div class="text-sm text-gray-600">Subject:</div>
                                        <div id="preview-subject" class="font-medium text-gray-900">Your email subject will appear here</div>
                                    </div>
                                    
                                    <!-- Email Body -->
                                    <div id="preview-body" class="p-6">
                                        <p class="text-gray-500 italic">Start typing to see your email preview...</p>
                                    </div>
                                </div>
                                
                                <!-- Sample Data Info -->
                                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                    <div class="text-xs text-blue-800 font-medium mb-1">Preview uses sample data:</div>
                                    <div class="text-xs text-blue-600">
                                        • student.name → "John Smith"<br>
                                        • programme.title → "Business Management"<br>
                                        • college.name → "The Open College"
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        </div> <!-- Close grid container -->

                        <!-- Template Validation -->
                        <div id="validation-results" class="mt-6 hidden">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Template Validation</h3>
                                        <div id="validation-messages" class="mt-2 text-sm text-yellow-700">
                                            <!-- Validation messages will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('admin.email-templates.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-medium text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                Cancel
                            </a>
                            <button type="button" 
                                    id="validate-template"
                                    class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Validate Template
                            </button>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                Create Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quill.js CDN (Free) -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    
    <script>
        // Auto-save functionality
        let autoSaveTimer;
        let isDirty = false;
        
        function autoSave() {
            if (!isDirty) return;
            
            const formData = {
                name: document.getElementById('name').value,
                subject: document.getElementById('subject').value,
                category: document.getElementById('category').value,
                description: document.getElementById('description').value,
                body_html: quill.root.innerHTML,
                body_text: document.getElementById('body_text').value,
                is_active: document.getElementById('is_active').checked
            };
            
            // Save to localStorage as draft
            localStorage.setItem('email_template_draft', JSON.stringify({
                ...formData,
                timestamp: new Date().toISOString()
            }));
            
            // Show auto-save indicator
            showAutoSaveIndicator();
            isDirty = false;
        }
        
        function showAutoSaveIndicator() {
            const indicator = document.getElementById('autosave-indicator') || createAutoSaveIndicator();
            indicator.textContent = 'Draft saved at ' + new Date().toLocaleTimeString();
            indicator.classList.remove('opacity-0');
            
            setTimeout(() => {
                indicator.classList.add('opacity-0');
            }, 2000);
        }
        
        function createAutoSaveIndicator() {
            const indicator = document.createElement('div');
            indicator.id = 'autosave-indicator';
            indicator.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded-md text-sm opacity-0 transition-opacity duration-300 z-50';
            document.body.appendChild(indicator);
            return indicator;
        }
        
        function markDirty() {
            isDirty = true;
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, 2000); // Auto-save after 2 seconds of inactivity
        }
        
        function loadDraft() {
            const draft = localStorage.getItem('email_template_draft');
            if (draft) {
                const data = JSON.parse(draft);
                const draftAge = (new Date() - new Date(data.timestamp)) / 1000 / 60; // minutes
                
                if (draftAge < 60) { // Only load drafts less than 1 hour old
                    if (confirm('Found a recent draft saved ' + Math.round(draftAge) + ' minutes ago. Would you like to restore it?')) {
                        document.getElementById('name').value = data.name || '';
                        document.getElementById('subject').value = data.subject || '';
                        document.getElementById('category').value = data.category || '';
                        document.getElementById('description').value = data.description || '';
                        document.getElementById('body_text').value = data.body_text || '';
                        document.getElementById('is_active').checked = data.is_active;
                        
                        // Load into Quill after it's initialized
                        setTimeout(() => {
                            if (data.body_html) {
                                quill.root.innerHTML = data.body_html;
                            }
                        }, 100);
                    }
                }
            }
        }

        // Live preview functionality
        let previewVisible = false;
        const sampleData = {
            'student.name': 'John Smith',
            'student.first_name': 'John',
            'student.last_name': 'Smith',
            'student.email': 'john.smith@student.ie',
            'student.student_number': 'STU2025001',
            'programme.title': 'Bachelor of Arts in Business Management',
            'programme_instance.label': 'September 2025 Intake',
            'programme_instance.delivery_style': 'Synchronous',
            'college.name': 'The Open College',
            'sender.name': 'Dr. Patricia Collins',
            'portal_url': 'https://portal.theopencollege.ie'
        };
        
        function togglePreview() {
            previewVisible = !previewVisible;
            const container = document.getElementById('editor-container');
            const previewColumn = document.getElementById('preview-column');
            const toggleBtn = document.getElementById('toggle-preview');
            
            if (previewVisible) {
                container.className = 'grid grid-cols-1 lg:grid-cols-2 gap-6';
                previewColumn.classList.remove('hidden');
                toggleBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464a.75.75 0 00-1.061 0L6.05 9.817m3.828.061L12 12m0 0l2.121 2.121M12 12L9.879 9.879m0 0L8.464 8.464m0 0L7.05 7.05m5.657 5.657l1.414 1.414"></path></svg>Hide Preview';
                updatePreview();
            } else {
                container.className = 'grid grid-cols-1 gap-6';
                previewColumn.classList.add('hidden');
                toggleBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>Toggle Preview';
            }
        }
        
        function updatePreview() {
            if (!previewVisible) return;
            
            const subject = document.getElementById('subject').value;
            const bodyHtml = quill.root.innerHTML;
            
            // Replace variables with sample data
            let previewSubject = subject;
            let previewBody = bodyHtml;
            
            Object.keys(sampleData).forEach(variable => {
                const openBr = '\\{\\{';
                const closeBr = '\\}\\}';
                const regex = new RegExp(openBr + variable + closeBr, 'g');
                previewSubject = previewSubject.replace(regex, sampleData[variable]);
                previewBody = previewBody.replace(regex, sampleData[variable]);
            });
            
            document.getElementById('preview-subject').textContent = previewSubject || 'Your email subject will appear here';
            document.getElementById('preview-body').innerHTML = previewBody || '<p class="text-gray-500 italic">Start typing to see your email preview...</p>';
        }
        
        function togglePreviewSize(size) {
            const preview = document.getElementById('email-preview');
            const desktopBtn = document.getElementById('preview-desktop');
            const mobileBtn = document.getElementById('preview-mobile');
            
            if (size === 'mobile') {
                preview.style.maxWidth = '375px';
                mobileBtn.className = 'px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 transition-colors cursor-pointer';
                desktopBtn.className = 'px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors cursor-pointer';
            } else {
                preview.style.maxWidth = '600px';
                desktopBtn.className = 'px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 transition-colors cursor-pointer';
                mobileBtn.className = 'px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors cursor-pointer';
            }
        }

        // Enhanced copy function with modern clipboard API
        function copyToClipboard(variableName) {
            const openBr = '{' + '{';
            const closeBr = '}' + '}';
            const fullVariable = openBr + variableName + closeBr;
            
            // Use modern clipboard API if available
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(fullVariable).then(() => {
                    showCopyFeedback(window.event?.target, fullVariable);
                }).catch(() => {
                    fallbackCopy(fullVariable);
                });
            } else {
                fallbackCopy(fullVariable);
            }
        }
        
        function fallbackCopy(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showCopyFeedback(window.event?.target, text);
                } else {
                    showToast('Copy failed! Please try manually selecting the variable.', 'error');
                }
            } catch (err) {
                showToast('Copy error! Please try manually selecting the variable.', 'error');
            } finally {
                document.body.removeChild(textArea);
            }
        }
        
        function showCopyFeedback(element, text) {
            if (element) {
                const originalBg = element.style.backgroundColor;
                const originalColor = element.style.color;
                const originalTransform = element.style.transform;
                
                element.style.backgroundColor = '#10b981';
                element.style.color = 'white';
                element.style.transform = 'scale(1.05)';
                
                setTimeout(() => {
                    element.style.backgroundColor = originalBg;
                    element.style.color = originalColor;
                    element.style.transform = originalTransform;
                }, 800);
            }
            
            showToast('Variable copied to clipboard!', 'success');
        }
        
        // Advanced drag and drop functionality
        let draggedVariable = null;
        
        function handleDragStart(event) {
            draggedVariable = event.target.getAttribute('data-variable');
            const openBr = '{' + '{';
            const closeBr = '}' + '}';
            event.dataTransfer.setData('text/plain', openBr + draggedVariable + closeBr);
            event.target.style.opacity = '0.5';
            event.target.style.transform = 'scale(0.95)';
        }
        
        function handleDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'copy';
        }
        
        function handleDragEnter(event) {
            event.preventDefault();
            const dropIndicator = document.getElementById('drop-indicator');
            dropIndicator.classList.remove('hidden');
        }
        
        function handleDragLeave(event) {
            if (!event.currentTarget.contains(event.relatedTarget)) {
                const dropIndicator = document.getElementById('drop-indicator');
                dropIndicator.classList.add('hidden');
            }
        }
        
        function handleDrop(event) {
            event.preventDefault();
            const dropIndicator = document.getElementById('drop-indicator');
            dropIndicator.classList.add('hidden');
            
            if (draggedVariable) {
                const openBr = '{' + '{';
                const closeBr = '}' + '}';
                const variableText = openBr + draggedVariable + closeBr;
                
                // Get cursor position in Quill
                const range = quill.getSelection();
                const index = range ? range.index : quill.getLength();
                
                // Insert the variable at cursor position
                quill.insertText(index, variableText);
                quill.setSelection(index + variableText.length);
                
                // Update the hidden textarea
                document.getElementById('body_html').value = quill.root.innerHTML;
                markDirty();
                updatePreview();
                
                showToast('Variable inserted successfully!', 'success');
                draggedVariable = null;
            }
        }
        
        // Toast notification system
        function showToast(message, type = 'info') {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) {
                existingToast.remove();
            }
            
            const toast = document.createElement('div');
            const colorClass = type === 'success' ? 'bg-green-500' : 
                              type === 'error' ? 'bg-red-500' : 
                              type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
            toast.className = 'toast-notification fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium z-50 transform translate-y-full transition-transform duration-300 ' + colorClass;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.style.transform = 'translateY(0)';
            }, 100);
            
            // Auto remove
            setTimeout(() => {
                toast.style.transform = 'translateY(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
        
        // Template validation functionality
        function validateTemplate() {
            const name = document.getElementById('name').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const category = document.getElementById('category').value;
            const bodyHtml = quill.root.innerHTML;
            const bodyText = document.getElementById('body_text').value.trim();
            
            const warnings = [];
            const errors = [];
            const suggestions = [];
            
            // Basic validation
            if (!name) errors.push('Template name is required');
            if (!subject) errors.push('Email subject is required');
            if (!category) errors.push('Category selection is required');
            if (!bodyHtml || bodyHtml.trim() === '<p><br></p>') errors.push('Email content is required');
            
            // Advanced validation
            if (name && name.length < 3) warnings.push('Template name is very short (recommended: 10+ characters)');
            if (subject && subject.length < 5) warnings.push('Email subject is very short (recommended: 15+ characters)');
            if (bodyHtml && bodyHtml.length < 50) warnings.push('Email content seems too short for a meaningful message');
            
            // Variable validation
            const openBrace = '{';
            const closeBrace = '}';
            const varPattern = new RegExp('\\' + openBrace + '\\' + openBrace + '[^' + closeBrace + ']+\\' + closeBrace + '\\' + closeBrace, 'g');
            const variableMatches = bodyHtml.match(varPattern) || [];
            const subjectMatches = subject.match(varPattern) || [];
            const allVariables = [...variableMatches, ...subjectMatches];
            
            if (allVariables.length === 0) {
                const openVar = '{' + '{';
                const closeVar = '}' + '}';
                suggestions.push('Consider adding variables (like ' + openVar + 'student.name' + closeVar + ') to personalize your email');
            }
            
            // Check for unrecognized variables
            const knownVariables = [
                'student.name', 'student.first_name', 'student.last_name', 'student.email', 'student.student_number',
                'programme.title', 'programme_instance.label', 'programme_instance.delivery_style',
                'module.title', 'module.code', 'college.name', 'sender.name', 'portal_url', 'current_date'
            ];
            
            allVariables.forEach(variable => {
                const cleanVar = variable.replace(/[{}]/g, '');
                if (!knownVariables.includes(cleanVar)) {
                    warnings.push('Unknown variable: ' + variable + ' - may not be replaced when sent');
                }
            });
            
            // HTML quality checks
            if (bodyHtml.includes('style=')) {
                suggestions.push('Consider avoiding inline styles for better email compatibility');
            }
            
            if (!bodyText && bodyHtml.length > 100) {
                suggestions.push('Consider adding a plain text version for better accessibility');
            }
            
            // Display results
            displayValidationResults(errors, warnings, suggestions);
            
            return errors.length === 0;
        }
        
        function displayValidationResults(errors, warnings, suggestions) {
            const resultsContainer = document.getElementById('validation-results');
            const messagesContainer = document.getElementById('validation-messages');
            
            if (errors.length === 0 && warnings.length === 0 && suggestions.length === 0) {
                resultsContainer.classList.add('hidden');
                showToast('Template validation passed!', 'success');
                return;
            }
            
            let html = '';
            
            if (errors.length > 0) {
                html += '<div class="mb-3"><strong>Errors:</strong><ul class="list-disc list-inside mt-1">';
                errors.forEach(error => {
                    html += '<li>' + error + '</li>';
                });
                html += '</ul></div>';
            }
            
            if (warnings.length > 0) {
                html += '<div class="mb-3"><strong>Warnings:</strong><ul class="list-disc list-inside mt-1">';
                warnings.forEach(warning => {
                    html += '<li>' + warning + '</li>';
                });
                html += '</ul></div>';
            }
            
            if (suggestions.length > 0) {
                html += '<div class="mb-3"><strong>Suggestions:</strong><ul class="list-disc list-inside mt-1">';
                suggestions.forEach(suggestion => {
                    html += '<li>' + suggestion + '</li>';
                });
                html += '</ul></div>';
            }
            
            messagesContainer.innerHTML = html;
            resultsContainer.classList.remove('hidden');
            
            if (errors.length > 0) {
                showToast('Template has ' + errors.length + ' error(s) that need fixing', 'error');
            } else if (warnings.length > 0) {
                showToast('Template validation completed with ' + warnings.length + ' warning(s)', 'warning');
            } else {
                showToast('Template validation completed successfully', 'success');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Load draft if available
            loadDraft();
            
            console.log('DOM loaded, checking for variables...');
            const variableCodes = document.querySelectorAll('code[data-variable]');
            console.log('Found variable codes:', variableCodes.length);
            
            // Enhanced event delegation for variable interactions
            document.addEventListener('click', function(e) {
                if (e.target.matches('code[data-variable]')) {
                    const variable = e.target.getAttribute('data-variable');
                    copyToClipboard(variable);
                }
            });
            
            // Reset drag styling on dragend
            document.addEventListener('dragend', function(e) {
                if (e.target.matches('code[data-variable]')) {
                    e.target.style.opacity = '1';
                    e.target.style.transform = 'scale(1)';
                }
            });
            
            // Preview toggle functionality
            document.getElementById('toggle-preview').addEventListener('click', togglePreview);
            document.getElementById('preview-desktop').addEventListener('click', () => togglePreviewSize('desktop'));
            document.getElementById('preview-mobile').addEventListener('click', () => togglePreviewSize('mobile'));
            document.getElementById('validate-template').addEventListener('click', validateTemplate);
            
            // Auto-save event listeners
            document.getElementById('name').addEventListener('input', markDirty);
            document.getElementById('subject').addEventListener('input', function() {
                markDirty();
                updatePreview();
            });
            document.getElementById('category').addEventListener('change', markDirty);
            document.getElementById('description').addEventListener('input', markDirty);
            document.getElementById('body_text').addEventListener('input', markDirty);
            document.getElementById('is_active').addEventListener('change', markDirty);
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl+S / Cmd+S for manual save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    autoSave();
                }
                
                // Ctrl+P / Cmd+P for preview toggle
                if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                    e.preventDefault();
                    togglePreview();
                }
                
                // Ctrl+Shift+V / Cmd+Shift+V for validation
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'V') {
                    e.preventDefault();
                    validateTemplate();
                }
                
                // Escape to close preview
                if (e.key === 'Escape' && previewVisible) {
                    togglePreview();
                }
                
                // Escape to hide validation results
                if (e.key === 'Escape' && !document.getElementById('validation-results').classList.contains('hidden')) {
                    document.getElementById('validation-results').classList.add('hidden');
                }
            });
            
            // Clear draft on successful form submission
            document.querySelector('form').addEventListener('submit', function() {
                localStorage.removeItem('email_template_draft');
            });
            
            // Warn before leaving with unsaved changes
            window.addEventListener('beforeunload', function(e) {
                if (isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });

        // Initialize Quill Editor
        const quill = new Quill('#quill-editor', {
            theme: 'snow',
            placeholder: 'Enter your email template here...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Sync Quill content with hidden textarea
        quill.on('text-change', function() {
            const html = quill.root.innerHTML;
            document.getElementById('body_html').value = html;
            markDirty();
            updatePreview();
        });

        // Load initial content if any
        const initialContent = document.getElementById('body_html').value;
        if (initialContent) {
            quill.root.innerHTML = initialContent;
        }

        // Update the loadTemplate function to work with Quill
        window.loadTemplate = function(type) {
            const templates = {
                grade_notification: {
                    name: 'Grade Released Notification',
                    subject: 'Your Assessment Results - @@{{student.name}}',
                    category: 'academic',
                    description: 'Notification sent when student grades are released',
                    body_html: `<h2>Dear @@{{student.first_name}},</h2>

<p>We are pleased to inform you that your assessment results for <strong>@@{{programme.title}}</strong> are now available.</p>

<div style="background-color: #f9fafb; padding: 16px; border-radius: 8px; margin: 20px 0;">
    <h3>Student Information</h3>
    <p><strong>Student Number:</strong> @@{{student.student_number}}</p>
    <p><strong>Programme:</strong> @@{{programme.title}}</p>
    <p><strong>Intake:</strong> @@{{programme_instance.label}}</p>
</div>

<p>You can view your detailed results by logging into the student portal:</p>

<p style="text-align: center; margin: 20px 0;">
    <a href="@@{{portal_url}}" style="background-color: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">View Results</a>
</p>

<p>If you have any questions about your results, please don't hesitate to contact us.</p>

<p>Best regards,<br>
@@{{sender.name}}<br>
@@{{college.name}}</p>`,
                    body_text: `Dear @@{{student.first_name}},

We are pleased to inform you that your assessment results for @@{{programme.title}} are now available.

Student Information:
- Student Number: @@{{student.student_number}}
- Programme: @@{{programme.title}}
- Intake: @@{{programme_instance.label}}

You can view your detailed results by logging into the student portal at @@{{portal_url}}.

If you have any questions about your results, please don't hesitate to contact us.

Best regards,
@@{{sender.name}}
@@{{college.name}}`
                },
                welcome: {
                    name: 'Welcome to Programme',
                    subject: 'Welcome to @@{{programme.title}} - @@{{student.first_name}}',
                    category: 'administrative',
                    description: 'Welcome email for new students starting a programme',
                    body_html: `<h2>Welcome @@{{student.first_name}}!</h2>

<p>Congratulations on enrolling in <strong>@@{{programme.title}}</strong> at @@{{college.name}}.</p>

<div style="background-color: #ecfdf5; padding: 16px; border-radius: 8px; margin: 20px 0;">
    <h3>Your Programme Details</h3>
    <p><strong>Programme:</strong> @@{{programme.title}}</p>
    <p><strong>Intake:</strong> @@{{programme_instance.label}}</p>
    <p><strong>Student Number:</strong> @@{{student.student_number}}</p>
    <p><strong>Delivery Style:</strong> @@{{programme_instance.delivery_style}}</p>
</div>

<p>Here's what happens next:</p>
<ul>
    <li>You'll receive login details for our learning platform</li>
    <li>Course materials will become available shortly</li>
    <li>Your assigned tutor will be in touch soon</li>
</ul>

<p style="text-align: center; margin: 20px 0;">
    <a href="@@{{portal_url}}" style="background-color: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">Access Student Portal</a>
</p>

<p>We're excited to have you join us and look forward to supporting your learning journey!</p>

<p>Best regards,<br>
@@{{sender.name}}<br>
@@{{college.name}}</p>`,
                    body_text: `Welcome @@{{student.first_name}}!

Congratulations on enrolling in @@{{programme.title}} at @@{{college.name}}.

Your Programme Details:
- Programme: @@{{programme.title}}
- Intake: @@{{programme_instance.label}}
- Student Number: @@{{student.student_number}}
- Delivery Style: @@{{programme_instance.delivery_style}}

Here's what happens next:
- You'll receive login details for our learning platform
- Course materials will become available shortly
- Your assigned tutor will be in touch soon

Access the student portal at @@{{portal_url}}.

We're excited to have you join us and look forward to supporting your learning journey!

Best regards,
@@{{sender.name}}
@@{{college.name}}`
                },
                reminder: {
                    name: 'Assessment Deadline Reminder',
                    subject: 'Reminder: Assessment Due Soon - @@{{student.first_name}}',
                    category: 'academic',
                    description: 'Reminder for upcoming assessment deadlines',
                    body_html: `<h2>Dear @@{{student.first_name}},</h2>

<p>This is a friendly reminder that you have an upcoming assessment deadline.</p>

<div style="background-color: #fef3c7; padding: 16px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;">
    <h3>⚠️ Assessment Details</h3>
    <p><strong>Programme:</strong> @@{{programme.title}}</p>
    <p><strong>Student Number:</strong> @@{{student.student_number}}</p>
    <p><strong>Deadline:</strong> Please check your student portal for specific dates</p>
</div>

<p>To ensure you don't miss your deadline:</p>
<ul>
    <li>Check your student portal for exact submission times</li>
    <li>Ensure all required components are uploaded</li>
    <li>Contact your tutor if you need any clarification</li>
    <li>Submit your work well before the deadline</li>
</ul>

<p style="text-align: center; margin: 20px 0;">
    <a href="@@{{portal_url}}" style="background-color: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">Check Portal</a>
</p>

<p>If you're experiencing any difficulties, please don't hesitate to reach out for support.</p>

<p>Best regards,<br>
@@{{sender.name}}<br>
@@{{college.name}}</p>`,
                    body_text: `Dear @@{{student.first_name}},

This is a friendly reminder that you have an upcoming assessment deadline.

Assessment Details:
- Programme: @@{{programme.title}}
- Student Number: @@{{student.student_number}}
- Deadline: Please check your student portal for specific dates

To ensure you don't miss your deadline:
- Check your student portal for exact submission times
- Ensure all required components are uploaded
- Contact your tutor if you need any clarification
- Submit your work well before the deadline

Check your portal at @@{{portal_url}}.

If you're experiencing any difficulties, please don't hesitate to reach out for support.

Best regards,
@@{{sender.name}}
@@{{college.name}}`
                }
            };

            const template = templates[type];
            if (template) {
                document.getElementById('name').value = template.name;
                document.getElementById('subject').value = template.subject.replaceAll('@@{{', '{{');
                document.getElementById('category').value = template.category;
                document.getElementById('description').value = template.description;
                
                // Set content in Quill editor
                const htmlContent = template.body_html.replaceAll('@@{{', '{{');
                quill.root.innerHTML = htmlContent;
                document.getElementById('body_html').value = htmlContent;
                
                document.getElementById('body_text').value = template.body_text.replaceAll('@@{{', '{{');
            }
        };
    </script>
</x-app-layout>