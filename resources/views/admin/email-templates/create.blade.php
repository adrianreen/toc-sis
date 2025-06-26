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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div>
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
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
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
                                        Use these variables in your template. They will be replaced with actual values when emails are sent.
                                    </p>
                                    
                                    @if(isset($availableVariables))
                                        <p class="text-xs text-red-600 mb-2">DEBUG: Found {{ count($availableVariables) }} variable categories</p>
                                        @foreach($availableVariables as $category => $variables)
                                            <div class="mb-4">
                                                <h4 class="font-medium text-gray-800 mb-2">{{ ucfirst($category) }} ({{ count($variables) }} vars)</h4>
                                                <div class="space-y-1">
                                                    @foreach($variables as $variable => $description)
                                                        <div class="text-sm">
                                                            <code class="bg-gray-200 px-2 py-1 rounded text-xs font-mono cursor-pointer hover:bg-gray-300 transition-colors" 
                                                                  data-variable="{{ $variable }}" 
                                                                  onclick="copyToClipboard('{{ $variable }}')">{!! '{{' . $variable . '}}' !!}</code>
                                                            <span class="text-gray-600 ml-2">{{ $description }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-red-600">DEBUG: No available variables found!</p>
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
                            <div id="quill-editor" style="height: 300px;"></div>
                            <!-- Hidden textarea for form submission -->
                            <textarea id="body_html" name="body_html" required style="display: none;">{{ old('body_html') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Use the rich text editor to format your email. Variables like <code>{!! '{{student.name}}' !!}</code> will be replaced automatically.
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

                        <!-- Actions -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('admin.email-templates.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-medium text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                Cancel
                            </a>
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
        // Simple global copy function
        function copyToClipboard(variableName) {
            console.log('copyToClipboard called with:', variableName);
            
            const openBrace = '{';
            const closeBrace = '}';
            const fullVariable = openBrace + openBrace + variableName + closeBrace + closeBrace;
            console.log('Will copy full variable:', fullVariable);
            
            // Create temporary textarea
            const textArea = document.createElement('textarea');
            textArea.value = fullVariable;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    console.log('Copy successful! Copied:', fullVariable);
                    
                    // Show visual feedback
                    const clickedElement = window.event ? window.event.target : null;
                    if (clickedElement) {
                        const originalText = clickedElement.textContent;
                        clickedElement.textContent = 'Copied!';
                        clickedElement.style.backgroundColor = '#10b981';
                        clickedElement.style.color = 'white';
                        
                        setTimeout(function() {
                            clickedElement.textContent = originalText;
                            clickedElement.style.backgroundColor = '';
                            clickedElement.style.color = '';
                        }, 1000);
                    }
                } else {
                    alert('Copy failed! Variable: ' + fullVariable);
                }
            } catch (err) {
                console.error('Copy error:', err);
                alert('Copy error! Variable: ' + fullVariable);
            } finally {
                document.body.removeChild(textArea);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, checking for variables...');
            const variableCodes = document.querySelectorAll('code[data-variable]');
            console.log('Found variable codes:', variableCodes.length);
            
            // Also try event delegation as backup
            document.addEventListener('click', function(e) {
                if (e.target.matches('code[data-variable]')) {
                    console.log('Event delegation triggered');
                    const variable = e.target.getAttribute('data-variable');
                    copyToClipboard(variable);
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