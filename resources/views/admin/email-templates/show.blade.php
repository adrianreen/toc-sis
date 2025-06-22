<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Email Template: {{ $template->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $template->description }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.email-templates.preview', $template) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    Preview
                </a>
                <a href="{{ route('admin.email-templates.edit', $template) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Edit Template
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Template Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Template Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $template->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Category</label>
                                    <p class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($template->category === 'academic') bg-purple-100 text-purple-800
                                            @elseif($template->category === 'administrative') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($template->category) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Subject</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $template->subject }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status</label>
                                    <p class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($template->is_active) bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                                @if($template->system_template)
                                <div class="md:col-span-2">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-sm text-blue-800">This is a system template and cannot be deleted.</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Email Content -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Content</h3>
                            <div class="prose max-w-none">
                                {!! $template->body_html !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Usage Statistics -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Usage Statistics</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Emails Sent</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $template->email_logs_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Last Used</span>
                                    <span class="text-sm text-gray-900">
                                        @if($template->email_logs_count > 0)
                                            {{ $template->emailLogs->first()?->created_at?->diffForHumans() ?? 'Never' }}
                                        @else
                                            Never
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Created</span>
                                    <span class="text-sm text-gray-900">{{ $template->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Created By</span>
                                    <span class="text-sm text-gray-900">{{ $template->createdBy->name }}</span>
                                </div>
                                @if($template->updated_at && $template->updated_at != $template->created_at)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Last Modified</span>
                                    <span class="text-sm text-gray-900">{{ $template->updated_at->format('d M Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Available Variables -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Variables</h3>
                            <div class="space-y-2 text-sm">
                                <div class="bg-gray-50 p-2 rounded font-mono">{{ '{student_name}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono">{{ '{student_number}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono">{{ '{student_email}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono">{{ '{programme_title}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono">{{ '{module_title}' }}</div>
                                <div class="bg-gray-50 p-2 rounded font-mono">{{ '{current_date}' }}</div>
                                <div class="text-xs text-gray-500 mt-2">
                                    Variables are automatically replaced with actual values when emails are sent.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                            <div class="space-y-3">
                                <form method="POST" action="{{ route('admin.email-templates.duplicate', $template) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150 ease-in-out">
                                        Duplicate Template
                                    </button>
                                </form>
                                
                                @if(!$template->system_template && $template->email_logs_count === 0)
                                <form method="POST" 
                                      action="{{ route('admin.email-templates.destroy', $template) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                                        Delete Template
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>