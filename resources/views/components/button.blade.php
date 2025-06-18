@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left'
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden';

$variants = [
    'primary' => 'bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 focus:ring-blue-500 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0',
    'secondary' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 hover:border-slate-400 focus:ring-toc-500 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0',
    'danger' => 'bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700 focus:ring-red-500 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0',
    'success' => 'bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700 focus:ring-green-500 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0',
    'warning' => 'bg-gradient-to-r from-amber-500 to-amber-600 text-white hover:from-amber-600 hover:to-amber-700 focus:ring-amber-500 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0',
    'info' => 'bg-gradient-to-r from-cyan-500 to-cyan-600 text-white hover:from-cyan-600 hover:to-cyan-700 focus:ring-cyan-500 shadow-sm hover:shadow-md hover:-translate-y-0.5 active:translate-y-0',
    'ghost' => 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:ring-toc-500 hover:shadow-sm',
    'outline' => 'border-2 border-toc-500 text-toc-600 hover:bg-toc-50 hover:text-toc-700 hover:border-toc-600 focus:ring-toc-500',
    'link' => 'text-toc-600 hover:text-toc-700 underline-offset-4 hover:underline focus:ring-toc-500 px-0',
    'glass' => 'bg-white/80 backdrop-blur-sm text-slate-700 border border-white/20 hover:bg-white/90 focus:ring-toc-500 shadow-lg',
];

$sizes = [
    'xs' => 'px-2.5 py-1.5 text-xs gap-1.5',
    'sm' => 'px-3 py-2 text-sm gap-2',
    'md' => 'px-4 py-2.5 text-sm gap-2',
    'lg' => 'px-6 py-3 text-base gap-2.5',
    'xl' => 'px-8 py-4 text-lg gap-3',
    '2xl' => 'px-10 py-5 text-xl gap-3',
];

$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);

if ($loading) {
    $classes .= ' cursor-wait pointer-events-none';
}

// Special link variant handling
if ($variant === 'link') {
    $classes = str_replace($baseClasses, 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed', $classes);
}
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if($disabled) aria-disabled="true" @endif>
        <!-- Shimmer effect for loading -->
        @if($loading)
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
        @endif
        
        @if($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <x-icon :name="$icon" class="w-4 h-4" />
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right' && !$loading)
            <x-icon :name="$icon" class="w-4 h-4" />
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @if($disabled || $loading) disabled @endif>
        <!-- Shimmer effect for loading -->
        @if($loading)
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
        @endif
        
        @if($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <x-icon :name="$icon" class="w-4 h-4" />
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right' && !$loading)
            <x-icon :name="$icon" class="w-4 h-4" />
        @endif
    </button>
@endif