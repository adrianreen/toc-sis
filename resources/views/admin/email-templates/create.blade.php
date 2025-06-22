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
                                        @foreach($availableVariables as $category => $variables)
                                            <div class="mb-4">
                                                <h4 class="font-medium text-gray-800 mb-2">{{ ucfirst($category) }}</h4>
                                                <div class="space-y-1">
                                                    @foreach($variables as $variable => $description)
                                                        <div class="text-sm">
                                                            <code class="bg-gray-200 px-2 py-1 rounded text-xs font-mono">{{{{ $variable }}}}</code>
                                                            <span class="text-gray-600 ml-2">{{ $description }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- HTML Body -->
                        <div class="mt-6">
                            <label for="body_html" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Content (HTML) *
                            </label>
                            <textarea id="body_html" 
                                      name="body_html" 
                                      rows="15"
                                      required
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                                      placeholder="Enter your HTML email template here...">{{ old('body_html') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Use HTML tags for formatting. Variables like <code>{{ '{{student_name}}' }}</code> will be replaced automatically.
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
</x-app-layout>