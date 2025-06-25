{{-- resources/views/policies/manage.blade.php --}}
<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Header Section --}}
            <div class="mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Policy Management</h1>
                        <p class="mt-2 text-gray-600">Create and manage policies for students and staff</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('policies.create') }}" 
                           class="inline-flex items-center px-4 py-2 rounded-lg font-medium text-sm transition-colors cursor-pointer"
                           style="background-color: #2563eb; color: #ffffff; border: none;"
                           onmouseover="this.style.backgroundColor='#1d4ed8'"
                           onmouseout="this.style.backgroundColor='#2563eb'">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Policy
                        </a>
                        
                        <a href="{{ route('policies.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors cursor-pointer">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View Student Interface
                        </a>
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Policies</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </x-card>

                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Published</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['published'] }}</p>
                        </div>
                    </div>
                </x-card>

                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Draft</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['draft'] }}</p>
                        </div>
                    </div>
                </x-card>

                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">This Month</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['this_month'] }}</p>
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- Filter and Search Section --}}
            <x-card class="mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('policies.manage') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            {{-- Search --}}
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Policy title or description..."
                                       class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                            </div>

                            {{-- Category --}}
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category" id="category" class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" id="status" class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                                    <option value="">All Statuses</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>

                            {{-- Programme Type --}}
                            <div>
                                <label for="programme_type" class="block text-sm font-medium text-gray-700 mb-1">Programme Type</label>
                                <select name="programme_type" id="programme_type" class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                                    <option value="">All Types</option>
                                    <option value="all" {{ request('programme_type') === 'all' ? 'selected' : '' }}>All Programmes</option>
                                    <option value="elc" {{ request('programme_type') === 'elc' ? 'selected' : '' }}>ELC Programmes</option>
                                    <option value="degree_obu" {{ request('programme_type') === 'degree_obu' ? 'selected' : '' }}>Degree (OBU)</option>
                                    <option value="qqi" {{ request('programme_type') === 'qqi' ? 'selected' : '' }}>QQI Programmes</option>
                                </select>
                            </div>

                            {{-- Filter Button --}}
                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-toc-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Filter
                                </button>
                            </div>
                        </div>

                        @if(request()->hasAny(['search', 'category', 'status', 'programme_type']))
                            <div class="flex justify-start">
                                <a href="{{ route('policies.manage') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors cursor-pointer">
                                    Clear Filters
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </x-card>

            {{-- Policies Table --}}
            <x-card>
                <div class="overflow-x-auto">
                    @if($policies->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Policy</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programme Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stats</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($policies as $policy)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('policies.show', $policy) }}" class="hover:text-toc-600 cursor-pointer">
                                                        {{ $policy->title }}
                                                    </a>
                                                </div>
                                                @if($policy->description)
                                                    <div class="text-sm text-gray-500 mt-1">{{ Str::limit($policy->description, 60) }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $policy->category->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $policy->getProgrammeTypeLabel() }}</div>
                                            <div class="text-xs text-gray-500">{{ $policy->getScopeLabel() }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusColors = [
                                                    'published' => 'bg-green-100 text-green-800',
                                                    'draft' => 'bg-yellow-100 text-yellow-800',
                                                    'archived' => 'bg-gray-100 text-gray-800',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$policy->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($policy->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($policy->hasFile())
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <span class="text-xs">{{ $policy->getFileSizeHuman() }}</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">No file</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="text-xs">
                                                <div>{{ $policy->view_count }} views</div>
                                                <div>{{ $policy->download_count }} downloads</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ $policy->created_at->format('M j, Y') }}</div>
                                            <div class="text-xs">{{ $policy->creator->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('policies.show', $policy) }}" 
                                                   class="text-toc-600 hover:text-toc-900 cursor-pointer">View</a>
                                                <a href="{{ route('policies.edit', $policy) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 cursor-pointer">Edit</a>
                                                @if($policy->hasFile())
                                                    <a href="{{ route('policies.download', $policy) }}" 
                                                       class="text-green-600 hover:text-green-900 cursor-pointer">Download</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No policies found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Get started by creating your first policy.
                            </p>
                            <div class="mt-6">
                                <a href="{{ route('policies.create') }}" 
                                   class="inline-flex items-center px-4 py-2 rounded-lg font-medium text-sm transition-colors cursor-pointer"
                                   style="background-color: #2563eb; color: #ffffff; border: none;"
                                   onmouseover="this.style.backgroundColor='#1d4ed8'"
                                   onmouseout="this.style.backgroundColor='#2563eb'">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create Policy
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Pagination --}}
                @if($policies->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $policies->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>