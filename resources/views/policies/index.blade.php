{{-- resources/views/policies/index.blade.php --}}
<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Header Section --}}
            <div class="mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">College Policies</h1>
                        <p class="mt-2 text-gray-600">Access policies, procedures, and important documents</p>
                    </div>
                    
                    @if(in_array(auth()->user()->role, ['manager', 'student_services']))
                        <div class="flex gap-3">
                            <a href="{{ route('policies.manage') }}" 
                               class="inline-flex items-center px-4 py-2 bg-toc-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                                Manage Policies
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Search Section --}}
            <x-card class="mb-8">
                <div class="p-6">
                    <form method="GET" action="{{ route('policies.index') }}" class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label for="search" class="sr-only">Search policies</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ $searchTerm }}"
                                       placeholder="Search policies, procedures, and documents..."
                                       class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-toc-500 focus:border-toc-500">
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-toc-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                Search
                            </button>
                            @if($searchTerm)
                                <a href="{{ route('policies.index') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-gray-300 border border-transparent rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors cursor-pointer">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </x-card>

            {{-- Search Results --}}
            @if($searchTerm && $searchResults->count() > 0)
                <x-card class="mb-8">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Search Results for "{{ $searchTerm }}" ({{ $searchResults->count() }} found)
                        </h2>
                        <div class="space-y-4">
                            @foreach($searchResults as $policy)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-medium text-gray-900">
                                                <a href="{{ route('policies.show', $policy) }}" class="hover:text-toc-600 cursor-pointer">
                                                    {{ $policy->title }}
                                                </a>
                                            </h3>
                                            <p class="text-sm text-gray-600 mt-1">{{ $policy->category->name }}</p>
                                            @if($policy->description)
                                                <p class="text-sm text-gray-500 mt-2">{{ $policy->description }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-3 ml-4">
                                            @if($policy->hasFile())
                                                <a href="{{ route('policies.download', $policy) }}" 
                                                   class="inline-flex items-center text-sm text-toc-600 hover:text-toc-800 cursor-pointer">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                                    </svg>
                                                    Download PDF
                                                </a>
                                            @endif
                                            <a href="{{ route('policies.show', $policy) }}" 
                                               class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800 cursor-pointer">
                                                View Details
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            @elseif($searchTerm)
                <x-card class="mb-8">
                    <div class="p-6 text-center">
                        <div class="text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No results found</h3>
                            <p>No policies match your search for "{{ $searchTerm }}". Try different keywords or browse categories below.</p>
                        </div>
                    </div>
                </x-card>
            @endif

            {{-- Policy Categories - SharePoint Style --}}
            @if($categories->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    @foreach($categories as $category)
                        <x-card class="h-fit">
                            <div class="p-6">
                                {{-- Category Header --}}
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        @php
                                            $iconColors = [
                                                'Assessments' => 'bg-blue-100 text-blue-600',
                                                'eLearning Support Services' => 'bg-green-100 text-green-600',
                                                'Certification' => 'bg-purple-100 text-purple-600',
                                                'QA Policies' => 'bg-red-100 text-red-600',
                                                'Protection for Learners' => 'bg-indigo-100 text-indigo-600',
                                                'Degree Programmes' => 'bg-yellow-100 text-yellow-600',
                                                'ELC Programmes' => 'bg-pink-100 text-pink-600',
                                                'QQI Programmes' => 'bg-gray-100 text-gray-600',
                                            ];
                                        @endphp
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $iconColors[$category->name] ?? 'bg-gray-100 text-gray-600' }}">
                                            @switch($category->name)
                                                @case('Assessments')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    @break
                                                @case('eLearning Support Services')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                    @break
                                                @case('Certification')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    @break
                                                @case('QA Policies')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    @break
                                                @case('Protection for Learners')
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                    @break
                                                @default
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                                    </svg>
                                            @endswitch
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h2 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h2>
                                        @if($category->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $category->description }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">{{ $category->publishedPolicies->count() }} {{ Str::plural('policy', $category->publishedPolicies->count()) }}</p>
                                    </div>
                                </div>

                                {{-- Policy List --}}
                                @if($category->publishedPolicies->count() > 0)
                                    <div class="space-y-3">
                                        @foreach($category->publishedPolicies as $policy)
                                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors group">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <h3 class="font-medium text-gray-900 group-hover:text-toc-600 transition-colors">
                                                            <a href="{{ route('policies.show', $policy) }}" class="cursor-pointer">
                                                                {{ $policy->title }}
                                                            </a>
                                                        </h3>
                                                        @if($policy->description)
                                                            <p class="text-xs text-gray-600 mt-1">{{ Str::limit($policy->description, 80) }}</p>
                                                        @endif
                                                        <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                                            <span>{{ $policy->getProgrammeTypeLabel() }}</span>
                                                            @if($policy->view_count > 0)
                                                                <span>{{ $policy->view_count }} views</span>
                                                            @endif
                                                            @if($policy->hasFile())
                                                                <span class="flex items-center">
                                                                    <svg class="w-3 h-3 mr-1 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                        <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                                    </svg>
                                                                    PDF ({{ $policy->getFileSizeHuman() }})
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-2 ml-4">
                                                        @if($policy->hasFile())
                                                            <a href="{{ route('policies.show', $policy) }}#pdf" 
                                                               class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                                               title="View PDF">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                </svg>
                                                            </a>
                                                            <a href="{{ route('policies.download', $policy) }}" 
                                                               class="p-2 text-toc-600 hover:text-toc-800 hover:bg-toc-50 rounded-lg transition-colors cursor-pointer"
                                                               title="Download PDF">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                                </svg>
                                                            </a>
                                                        @endif
                                                        <a href="{{ route('policies.show', $policy) }}" 
                                                           class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                                                           title="View Details">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-6 text-gray-500">
                                        <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-sm">No policies available in this category</p>
                                    </div>
                                @endif
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <x-card>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No policies available</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            There are currently no published policies available for your programme type.
                        </p>
                        @if(in_array(auth()->user()->role, ['manager', 'student_services']))
                            <div class="mt-6">
                                <a href="{{ route('policies.manage') }}" 
                                   class="inline-flex items-center px-4 py-2 rounded-lg font-medium text-sm transition-colors cursor-pointer"
                                   style="background-color: #2563eb; color: #ffffff; border: none;"
                                   onmouseover="this.style.backgroundColor='#1d4ed8'"
                                   onmouseout="this.style.backgroundColor='#2563eb'">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create First Policy
                                </a>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif

            {{-- Quick Links Footer --}}
            @if($categories->count() > 0)
                <div class="mt-12 text-center">
                    <div class="border-t border-gray-200 pt-8">
                        <p class="text-sm text-gray-600 mb-4">Need help finding a specific policy?</p>
                        <div class="flex flex-wrap justify-center gap-4">
                            @if(in_array(auth()->user()->role, ['manager', 'student_services']))
                                <a href="{{ route('policies.manage') }}" 
                                   class="inline-flex items-center text-sm text-toc-600 hover:text-toc-800 cursor-pointer">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                    Manage Policies
                                </a>
                            @endif
                            <a href="{{ route('notifications.index') }}" 
                               class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800 cursor-pointer">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M15 17h5l-5 5v-5zM4 19.5v-15A2.5 2.5 0 016.5 2H20v20H6.5a2.5 2.5 0 010-5H20"/>
                                </svg>
                                View Notifications
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>