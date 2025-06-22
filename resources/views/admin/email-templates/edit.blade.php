<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Email Template: {{ $template->name }}
            </h2>
            <a href="{{ route('admin.email-templates.show', $template) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                Cancel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Template Form -->
                <div class="lg:col-span-2">
                    <form method="POST" action="{{ route('admin.email-templates.update', $template) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-6">Template Information</h3>
                                
                                <div class="space-y-6">
                                    <!-- Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                                        <input type="text" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', $template->name) }}"
                                               required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-2 text-sm text-gray-500">A descriptive name for this email template.</p>
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                        <input type="text" 
                                               id="description" 
                                               name="description" 
                                               value="{{ old('description', $template->description) }}"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-2 text-sm text-gray-500">Optional description of when this template is used.</p>
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                        <select id="category" 
                                                name="category" 
                                                required
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="academic" {{ old('category', $template->category) === 'academic' ? 'selected' : '' }}>Academic</option>
                                            <option value="administrative" {{ old('category', $template->category) === 'administrative' ? 'selected' : '' }}>Administrative</option>
                                            <option value="notification" {{ old('category', $template->category) === 'notification' ? 'selected' : '' }}>Notification</option>
                                        </select>
                                    </div>

                                    <!-- Subject -->
                                    <div>
                                        <label for="subject" class="block text-sm font-medium text-gray-700">Subject Line</label>
                                        <input type="text" 
                                               id="subject" 
                                               name="subject" 
                                               value="{{ old('subject', $template->subject) }}"
                                               required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-2 text-sm text-gray-500">Email subject line. You can use variables like {student_name}.</p>
                                    </div>

                                    <!-- Body HTML -->
                                    <div>
                                        <label for="body_html" class="block text-sm font-medium text-gray-700">Email Content (HTML)</label>
                                        <textarea id="body_html" 
                                                  name="body_html" 
                                                  rows="15"
                                                  required
                                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono">{{ old('body_html', $template->body_html) }}</textarea>
                                        <p class="mt-2 text-sm text-gray-500">HTML content of the email. Use variables like {student_name}, {programme_title}, etc.</p>
                                    </div>

                                    <!-- Status -->
                                    <div>
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="is_active" 
                                                   name="is_active" 
                                                   value="1"
                                                   {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                                Template is active
                                            </label>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">Inactive templates cannot be used to send emails.</p>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="mt-8 flex justify-end space-x-3">
                                    <a href="{{ route('admin.email-templates.preview', $template) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                                        Preview
                                    </a>
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                        Update Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Available Variables -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Variables</h3>
                            <div class="space-y-2 text-sm">
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{student_name}')">{{ '{student_name}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{student_number}')">{{ '{student_number}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{student_email}')">{{ '{student_email}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{programme_title}')">{{ '{programme_title}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{module_title}')">{{ '{module_title}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{current_date}')">{{ '{current_date}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{tutor_name}')">{{ '{tutor_name}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{grade}')">{{ '{grade}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{assessment_name}')">{{ '{assessment_name}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono cursor-pointer hover:bg-gray-100" 
                                     onclick="insertVariable('{due_date}')">{{ '{due_date}' }}</div>
                                <div class="text-xs text-gray-500 mt-2">
                                    Click on a variable to insert it into the content at your cursor position.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- HTML Tips -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">HTML Tips</h3>
                            <div class="space-y-2 text-sm text-gray-600">
                                <p><strong>Headers:</strong> Use &lt;h1&gt;, &lt;h2&gt;, &lt;h3&gt; for headings</p>
                                <p><strong>Paragraphs:</strong> Use &lt;p&gt; for text blocks</p>
                                <p><strong>Bold:</strong> Use &lt;strong&gt; or &lt;b&gt;</p>
                                <p><strong>Links:</strong> Use &lt;a href="..."&gt;</p>
                                <p><strong>Lists:</strong> Use &lt;ul&gt; and &lt;li&gt;</p>
                                <p><strong>Line breaks:</strong> Use &lt;br&gt;</p>
                            </div>
                        </div>
                    </div>

                    @if($template->system_template)
                    <!-- System Template Warning -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">System Template</p>
                                <p class="text-sm text-yellow-700">This is a system template. Changes may be overwritten during system updates.</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function insertVariable(variable) {
            const textarea = document.getElementById('body_html');
            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            const textBefore = textarea.value.substring(0, startPos);
            const textAfter = textarea.value.substring(endPos, textarea.value.length);
            
            textarea.value = textBefore + variable + textAfter;
            textarea.selectionStart = textarea.selectionEnd = startPos + variable.length;
            textarea.focus();
        }
    </script>
</x-app-layout>