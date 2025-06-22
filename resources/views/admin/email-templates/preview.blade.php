<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Preview: {{ $template->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $template->description }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.email-templates.edit', $template) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    Edit Template
                </a>
                <a href="{{ route('admin.email-templates.show', $template) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                    Back to Details
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Preview Options -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview Options</h3>
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="sample_student" class="block text-sm font-medium text-gray-700">Sample Student</label>
                            <select id="sample_student" 
                                    name="student_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Generic Sample Data</option>
                                @foreach($sampleStudents as $student)
                                    <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->full_name }} ({{ $student->student_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="sample_programme" class="block text-sm font-medium text-gray-700">Sample Programme</label>
                            <select id="sample_programme" 
                                    name="programme_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Generic Programme</option>
                                @foreach($sampleProgrammes as $programme)
                                    <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                        {{ $programme->code }} - {{ $programme->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                                Update Preview
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <!-- Email Preview -->
                <div class="lg:col-span-3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Preview</h3>
                            
                            <!-- Email Header -->
                            <div class="border-b border-gray-200 pb-4 mb-6">
                                <div class="space-y-2">
                                    <div class="flex">
                                        <span class="text-sm font-medium text-gray-500 w-16">From:</span>
                                        <span class="text-sm text-gray-900">{{ config('mail.from.name') }} &lt;{{ config('mail.from.address') }}&gt;</span>
                                    </div>
                                    <div class="flex">
                                        <span class="text-sm font-medium text-gray-500 w-16">To:</span>
                                        <span class="text-sm text-gray-900">{{ $previewData['student_email'] }}</span>
                                    </div>
                                    <div class="flex">
                                        <span class="text-sm font-medium text-gray-500 w-16">Subject:</span>
                                        <span class="text-sm text-gray-900 font-medium">{{ $renderedSubject }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Body -->
                            <div class="prose max-w-none">
                                <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                                    {!! $renderedBody !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Information -->
                <div class="space-y-6">
                    <!-- Template Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Template Info</h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="text-gray-500">Category:</span>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($template->category === 'academic') bg-purple-100 text-purple-800
                                        @elseif($template->category === 'administrative') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($template->category) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Status:</span>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($template->is_active) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Created:</span>
                                    <span class="ml-2 text-gray-900">{{ $template->created_at->format('d M Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Usage:</span>
                                    <span class="ml-2 text-gray-900">{{ $template->email_logs_count }} emails sent</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Data Used -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sample Data</h3>
                            <div class="space-y-2 text-sm">
                                @foreach($previewData as $key => $value)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">{{ '{{' . $key . '}}' }}:</span>
                                        <span class="text-gray-900 font-mono text-xs">{{ Str::limit($value, 20) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-3">
                                These values are used to replace variables in the template for this preview.
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                            <div class="space-y-3">
                                <button onclick="window.print()" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                                    Print Preview
                                </button>
                                
                                <form method="POST" action="{{ route('admin.email-templates.duplicate', $template) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150 ease-in-out">
                                        Duplicate Template
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if(!$template->is_active)
                    <!-- Inactive Template Warning -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-red-800">Template Inactive</p>
                                <p class="text-sm text-red-700">This template cannot be used to send emails until it's activated.</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .py-12, .space-y-6, .grid {
                margin: 0 !important;
                padding: 0 !important;
                gap: 0 !important;
            }
            
            .lg\\:col-span-3 {
                grid-column: span 4 / span 4 !important;
            }
            
            .space-y-6 > *:not(.lg\\:col-span-3) {
                display: none !important;
            }
        }
    </style>
</x-app-layout>