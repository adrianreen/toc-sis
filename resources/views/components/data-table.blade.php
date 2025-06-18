@props([
    'headers' => [],
    'rows' => [],
    'searchable' => false,
    'sortable' => false,
    'pagination' => null,
    'striped' => false,
    'hover' => true,
    'compact' => false,
    'loading' => false,
    'emptyMessage' => 'No data available',
    'emptyIcon' => 'table',
    'actions' => null
])

@php
$tableClasses = 'min-w-full divide-y divide-slate-200 table-enhanced';
$headerClasses = 'px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider bg-gradient-to-b from-slate-50 to-slate-75';
$cellClasses = 'px-6 py-4 whitespace-nowrap text-sm';

if ($compact) {
    $headerClasses = str_replace('py-4', 'py-3', $headerClasses);
    $cellClasses = str_replace('py-4', 'py-3', $cellClasses);
}

$rowClasses = $hover ? 'hover:bg-toc-25 transition-colors duration-150' : '';
if ($striped) {
    $rowClasses .= ' odd:bg-slate-25';
}
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden']) }}>
    @if($searchable || $actions)
        <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-25 to-slate-50">
            <div class="flex items-center justify-between">
                @if($searchable)
                    <div class="relative flex-1 max-w-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            placeholder="Search..." 
                            class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-toc-500 focus:border-toc-500"
                        />
                    </div>
                @endif
                
                @if($actions)
                    <div class="flex items-center space-x-3">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        @if($loading)
            <!-- Loading skeleton -->
            <div class="animate-pulse">
                <table class="{{ $tableClasses }}">
                    <thead>
                        <tr>
                            @foreach(range(1, count($headers) ?: 4) as $i)
                                <th class="{{ $headerClasses }}">
                                    <div class="h-4 bg-slate-300 rounded skeleton"></div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @foreach(range(1, 5) as $row)
                            <tr>
                                @foreach(range(1, count($headers) ?: 4) as $col)
                                    <td class="{{ $cellClasses }}">
                                        <div class="h-4 bg-slate-200 rounded skeleton" style="width: {{ rand(60, 100) }}%"></div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif(count($rows) > 0)
            <table class="{{ $tableClasses }}">
                @if(count($headers) > 0)
                    <thead>
                        <tr>
                            @foreach($headers as $header)
                                <th scope="col" class="{{ $headerClasses }}">
                                    @if(is_array($header))
                                        <div class="flex items-center space-x-2">
                                            @if(isset($header['icon']))
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    {!! $header['icon'] !!}
                                                </svg>
                                            @endif
                                            <span>{{ $header['label'] ?? $header['title'] ?? 'Column' }}</span>
                                            @if($sortable && ($header['sortable'] ?? true))
                                                <svg class="w-4 h-4 text-slate-300 hover:text-slate-500 cursor-pointer transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    @else
                                        {{ $header }}
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                @endif
                
                <tbody class="bg-white divide-y divide-slate-200">
                    @foreach($rows as $index => $row)
                        <tr class="{{ $rowClasses }}" data-row-index="{{ $index }}">
                            @if(is_array($row))
                                @foreach($row as $cell)
                                    <td class="{{ $cellClasses }} text-slate-900">
                                        {!! $cell !!}
                                    </td>
                                @endforeach
                            @else
                                <td colspan="{{ count($headers) }}" class="{{ $cellClasses }} text-slate-900">
                                    {!! $row !!}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <!-- Empty state -->
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-slate-400 mb-4">
                    @if($emptyIcon === 'table')
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-full w-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 4h6"/>
                        </svg>
                    @elseif($emptyIcon === 'users')
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-full w-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    @elseif($emptyIcon === 'search')
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-full w-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    @else
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-full w-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    @endif
                </div>
                <h3 class="text-sm font-medium text-slate-900 mb-1">{{ $emptyMessage }}</h3>
                @if($searchable)
                    <p class="text-sm text-slate-500">Try adjusting your search terms</p>
                @endif
            </div>
        @endif
    </div>

    @if($pagination && count($rows) > 0)
        <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
            {{ $pagination }}
        </div>
    @endif
</div>

<style>
/* Enhanced table hover effects */
.table-enhanced tbody tr:hover {
    background-color: theme('colors.primary.25');
    transform: translateY(-1px);
    box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.05);
}

.table-enhanced th {
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(10px);
}

/* Smooth row animations */
.table-enhanced tbody tr {
    transition: all 0.15s ease;
}

/* Loading skeleton animation improvements */
.skeleton {
    background: linear-gradient(90deg, 
        theme('colors.slate.200') 25%, 
        theme('colors.slate.300') 50%, 
        theme('colors.slate.200') 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}
</style>