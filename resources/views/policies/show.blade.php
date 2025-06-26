{{-- resources/views/policies/show.blade.php --}}
<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Header Section --}}
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('policies.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-toc-600 cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Policies
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $policy->category->name }}</span>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ Str::limit($policy->title, 30) }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            {{-- Policy Content --}}
            <x-card>
                <div class="p-8">
                    {{-- Policy Header --}}
                    <div class="mb-8">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $policy->title }}</h1>
                                
                                {{-- Policy Meta Information --}}
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        <span>{{ $policy->category->name }}</span>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        <span>{{ $policy->getProgrammeTypeLabel() }}</span>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Published {{ $policy->published_at->format('M j, Y') }}</span>
                                    </div>
                                    
                                    @if($policy->view_count > 0)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>{{ number_format($policy->view_count) }} {{ Str::plural('view', $policy->view_count) }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Status Badge --}}
                                @if(!$policy->isPublished() && in_array(auth()->user()->role, ['manager', 'student_services', 'teacher']))
                                    <div class="mb-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            {{ ucfirst($policy->status) }} - Not visible to students
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center space-x-3 ml-6">
                                @if($policy->hasFile())
                                    <button onclick="togglePdfView()" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View PDF
                                    </button>
                                    
                                    <a href="{{ route('policies.download', $policy) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-toc-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download
                                        @if($policy->getFileSizeHuman())
                                            <span class="ml-1 text-xs opacity-75">({{ $policy->getFileSizeHuman() }})</span>
                                        @endif
                                    </a>
                                @endif

                                @if(in_array(auth()->user()->role, ['manager', 'student_services']))
                                    <a href="{{ route('policies.edit', $policy) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit Policy
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Policy Description --}}
                    @if($policy->description)
                        <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <h2 class="text-lg font-semibold text-blue-900 mb-2">Overview</h2>
                            <p class="text-blue-800">{{ $policy->description }}</p>
                        </div>
                    @endif

                    {{-- Policy Content --}}
                    @if($policy->content)
                        <div class="prose max-w-none mb-8">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Policy Details</h2>
                            <div class="text-gray-700 leading-relaxed">
                                {!! nl2br(e($policy->content)) !!}
                            </div>
                        </div>
                    @endif

                    {{-- PDF Viewer --}}
                    @if($policy->hasFile())
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Policy Document</h2>
                                <div class="flex items-center space-x-3">
                                    <button onclick="togglePdfView()" 
                                            id="toggle-pdf-btn"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span id="toggle-text">View PDF</span>
                                    </button>
                                    <a href="{{ route('policies.download', $policy) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>

                            {{-- PDF Info Card --}}
                            <div id="pdf-info" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $policy->file_name }}</h3>
                                        <p class="text-sm text-gray-500">
                                            PDF Document • {{ $policy->getFileSizeHuman() }}
                                            @if($policy->download_count > 0)
                                                • {{ number_format($policy->download_count) }} {{ Str::plural('download', $policy->download_count) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- PDF Viewer using iframe (more reliable) --}}
                            <div id="pdf-viewer" class="hidden mt-4 border border-gray-300 rounded-lg overflow-hidden bg-white shadow-lg">
                                <div class="bg-gray-100 px-4 py-2 border-b border-gray-300 flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-700">{{ $policy->file_name }}</div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('policies.view-pdf', $policy) }}" 
                                           target="_blank"
                                           class="text-sm text-blue-600 hover:text-blue-800 cursor-pointer font-medium">
                                            Open in new tab
                                        </a>
                                        <span class="text-gray-400">|</span>
                                        <button onclick="togglePdfView()" class="text-sm text-gray-600 hover:text-gray-900 cursor-pointer font-medium">
                                            Close
                                        </button>
                                    </div>
                                </div>
                                <div class="relative bg-white" style="height: 800px;">
                                    {{-- Primary PDF iframe --}}
                                    <iframe id="pdf-frame" 
                                            src="" 
                                            class="w-full h-full border-0"
                                            title="PDF Viewer - {{ $policy->file_name }}"
                                            sandbox="allow-same-origin allow-scripts allow-forms"
                                            loading="lazy"
                                            style="min-height: 800px;">
                                        <p>Your browser does not support iframes. <a href="{{ route('policies.view-pdf', $policy) }}" target="_blank">Click here to view the PDF</a>.</p>
                                    </iframe>
                                    
                                    {{-- Alternative PDF.js viewer --}}
                                    <div id="pdf-js-viewer" class="hidden w-full h-full">
                                        <iframe id="pdf-js-frame"
                                                class="w-full h-full border-0"
                                                style="min-height: 800px;"
                                                title="PDF.js Viewer - {{ $policy->file_name }}">
                                        </iframe>
                                    </div>
                                    
                                    {{-- Fallback UI --}}
                                    <div id="pdf-fallback" class="hidden absolute inset-0 flex items-center justify-center bg-gray-50">
                                        <div class="text-center text-gray-500 p-8">
                                            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                            </svg>
                                            <h3 class="text-xl font-semibold text-gray-900 mb-2">PDF Viewer Unavailable</h3>
                                            <p class="text-base text-gray-600 mb-6 max-w-md mx-auto">Your browser cannot display this PDF inline. You can still view or download it using the options below.</p>
                                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                                <a href="{{ route('policies.view-pdf', $policy) }}" 
                                                   target="_blank"
                                                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors cursor-pointer font-medium">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                    Open in New Tab
                                                </a>
                                                <a href="{{ route('policies.download', $policy) }}" 
                                                   class="inline-flex items-center px-6 py-3 bg-toc-600 text-white rounded-lg hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-toc-500 focus:ring-offset-2 transition-colors cursor-pointer font-medium">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    Download PDF
                                                </a>
                                                <button onclick="tryPdfJsViewer()" 
                                                        class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors cursor-pointer font-medium">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                    Try Alternative Viewer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Policy Information --}}
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Policy Information</h2>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Category</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $policy->category->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Applies To</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $policy->getProgrammeTypeLabel() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Scope</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $policy->getScopeLabel() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Version</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $policy->version }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Published</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $policy->published_at->format('F j, Y \a\t g:i A') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $policy->creator->name }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Analytics for Staff --}}
                    @if(in_array(auth()->user()->role, ['manager', 'student_services', 'teacher']))
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Analytics (Staff Only)</h2>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Views</dt>
                                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($policy->view_count) }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Downloads</dt>
                                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($policy->download_count) }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
                                    <div class="p-5">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                </svg>
                                            </div>
                                            <div class="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt class="text-sm font-medium text-gray-500 truncate">Engagement</dt>
                                                    <dd class="text-lg font-medium text-gray-900">
                                                        @if($policy->view_count > 0)
                                                            {{ number_format(($policy->download_count / $policy->view_count) * 100, 1) }}%
                                                        @else
                                                            0%
                                                        @endif
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Navigation Footer --}}
                    <div class="border-t border-gray-200 pt-6 mt-8">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('policies.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to Policies
                            </a>

                            @if(in_array(auth()->user()->role, ['manager', 'student_services']))
                                <div class="flex space-x-3">
                                    <a href="{{ route('policies.manage') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        Manage All Policies
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <script>
        // Global variables for PDF viewer
        let pdfViewerState = {
            isOpen: false,
            usingPdfJs: false,
            loadAttempts: 0
        };

        // Define toggle function globally
        window.togglePdfView = function() {
            const viewer = document.getElementById('pdf-viewer');
            const info = document.getElementById('pdf-info');
            const toggleText = document.getElementById('toggle-text');
            const frame = document.getElementById('pdf-frame');
            const fallback = document.getElementById('pdf-fallback');
            
            console.log('Toggle PDF View called', {viewer, info, toggleText, frame, fallback});
            
            if (!viewer || !info || !toggleText || !frame) {
                console.error('PDF viewer elements not found');
                return;
            }
            
            if (viewer.classList.contains('hidden')) {
                // Show PDF viewer
                viewer.classList.remove('hidden');
                info.classList.add('hidden');
                toggleText.textContent = 'Hide PDF';
                pdfViewerState.isOpen = true;
                
                // Load PDF if not already loaded
                if (!frame.src) {
                    loadPdfInFrame();
                }
                
                // Scroll to PDF viewer
                viewer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                // Hide PDF viewer
                viewer.classList.add('hidden');
                info.classList.remove('hidden');
                toggleText.textContent = 'View PDF';
                pdfViewerState.isOpen = false;
                
                // Hide fallback if it was shown
                if (fallback) {
                    fallback.classList.add('hidden');
                }
            }
        };

        // Function to load PDF in iframe
        function loadPdfInFrame() {
            const frame = document.getElementById('pdf-frame');
            const fallback = document.getElementById('pdf-fallback');
            const pdfUrl = '{{ route("policies.view-pdf", $policy) }}';
            
            console.log('Loading PDF from:', pdfUrl);
            pdfViewerState.loadAttempts++;
            
            // Show loading state
            frame.style.background = '#f3f4f6 url("data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="35" fill="none" stroke="#9ca3af" stroke-width="8" stroke-dasharray="164.93361431346415 54.97787143782138" transform="rotate(0 50 50)"><animateTransform attributeName="transform" type="rotate" dur="1s" values="0 50 50;360 50 50" repeatCount="indefinite"/></circle></svg>') + '") no-repeat center center';
            frame.style.backgroundSize = '50px 50px';
            
            // Set iframe src
            frame.src = pdfUrl;
            
            // Handle iframe load events
            frame.onload = function() {
                console.log('PDF iframe loaded successfully');
                frame.style.background = 'white';
                if (fallback) {
                    fallback.classList.add('hidden');
                }
            };
            
            frame.onerror = function() {
                console.error('PDF iframe failed to load');
                frame.style.background = 'white';
                showFallback();
            };
            
            // Fallback timeout - show fallback if PDF doesn't load within 6 seconds
            setTimeout(function() {
                if (pdfViewerState.isOpen && pdfViewerState.loadAttempts <= 2) {
                    // Check if the iframe has loaded content
                    if (frame.src && frame.contentWindow) {
                        try {
                            // Try to access iframe document (will fail for PDF but that's expected)
                            const doc = frame.contentDocument || frame.contentWindow.document;
                            if (doc && doc.body && doc.body.innerHTML.trim() === '') {
                                console.warn('PDF iframe appears empty after timeout');
                                showFallback();
                            }
                        } catch (e) {
                            // Cross-origin error is expected for PDF content
                            // If we get here, the PDF is likely loading correctly
                            console.log('PDF iframe cross-origin (this is expected for PDFs)');
                        }
                    } else {
                        console.warn('PDF iframe failed to initialize');
                        showFallback();
                    }
                }
                frame.style.background = 'white';
            }, 6000);
        }

        // Function to show fallback options
        function showFallback() {
            const fallback = document.getElementById('pdf-fallback');
            if (fallback && pdfViewerState.isOpen) {
                fallback.classList.remove('hidden');
            }
        }

        // Alternative PDF.js viewer function
        window.tryPdfJsViewer = function() {
            const primaryFrame = document.getElementById('pdf-frame');
            const pdfJsViewer = document.getElementById('pdf-js-viewer');
            const pdfJsFrame = document.getElementById('pdf-js-frame');
            const fallback = document.getElementById('pdf-fallback');
            
            console.log('Trying PDF.js viewer');
            
            if (!pdfJsViewer || !pdfJsFrame) {
                console.error('PDF.js viewer elements not found');
                return;
            }
            
            // Hide primary frame and fallback
            primaryFrame.style.display = 'none';
            if (fallback) {
                fallback.classList.add('hidden');
            }
            
            // Show PDF.js viewer
            pdfJsViewer.classList.remove('hidden');
            pdfViewerState.usingPdfJs = true;
            
            // Load PDF with PDF.js (using Mozilla's hosted PDF.js)
            const pdfUrl = encodeURIComponent('{{ route("policies.view-pdf", $policy) }}');
            const pdfJsUrl = `https://mozilla.github.io/pdf.js/web/viewer.html?file=${pdfUrl}`;
            
            console.log('Loading PDF.js viewer with URL:', pdfJsUrl);
            pdfJsFrame.src = pdfJsUrl;
            
            pdfJsFrame.onload = function() {
                console.log('PDF.js viewer loaded successfully');
            };
            
            pdfJsFrame.onerror = function() {
                console.error('PDF.js viewer failed to load');
                // Fallback to showing the primary frame again
                primaryFrame.style.display = 'block';
                pdfJsViewer.classList.add('hidden');
                pdfViewerState.usingPdfJs = false;
                showFallback();
            };
        };

        // Auto-expand PDF if URL has #pdf hash
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, checking for #pdf hash:', window.location.hash);
            if (window.location.hash === '#pdf') {
                togglePdfView();
            }
        });
    </script>
</x-app-layout>