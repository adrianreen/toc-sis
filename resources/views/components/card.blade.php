@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'md',
    'shadow' => 'sm',
    'rounded' => 'xl',
    'header' => null,
    'footer' => null
])

@php
$paddingClasses = [
    'none' => '',
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8',
    'xl' => 'p-10',
];

$shadowClasses = [
    'none' => '',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
    'xl' => 'shadow-xl',
    'soft' => 'shadow-soft',
    'medium' => 'shadow-medium',
    'large' => 'shadow-large',
];

$roundedClasses = [
    'none' => '',
    'sm' => 'rounded-sm',
    'md' => 'rounded-md',
    'lg' => 'rounded-lg',
    'xl' => 'rounded-xl',
    '2xl' => 'rounded-2xl',
    '3xl' => 'rounded-3xl',
];

$baseClasses = 'bg-white border border-slate-200';
$classes = $baseClasses . ' ' . ($shadowClasses[$shadow] ?? $shadowClasses['sm']) . ' ' . ($roundedClasses[$rounded] ?? $roundedClasses['xl']);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title || $subtitle || $header)
        <div class="border-b border-slate-200 {{ $paddingClasses[$padding] ?? $paddingClasses['md'] }}">
            @if($header)
                {{ $header }}
            @else
                @if($title)
                    <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif
            @endif
        </div>
    @endif

    <div class="{{ $paddingClasses[$padding] ?? $paddingClasses['md'] }}">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="border-t border-slate-200 {{ $paddingClasses[$padding] ?? $paddingClasses['md'] }}">
            {{ $footer }}
        </div>
    @endif
</div>