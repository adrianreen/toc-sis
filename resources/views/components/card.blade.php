@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'md',
    'shadow' => 'sm',
    'rounded' => 'xl',
    'header' => null,
    'footer' => null,
    'variant' => 'default',
    'hover' => true,
    'loading' => false
])

@php
$paddingClasses = [
    'none' => '',
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8',
    'xl' => 'p-10',
    '2xl' => 'p-12',
];

$shadowClasses = [
    'none' => '',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
    'xl' => 'shadow-xl',
    '2xl' => 'shadow-2xl',
    'soft' => 'shadow-soft',
    'medium' => 'shadow-medium',
    'large' => 'shadow-large',
    'glow' => 'shadow-glow',
    'raised' => 'shadow-raised',
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

$variants = [
    'default' => 'bg-white border border-slate-200',
    'elevated' => 'bg-white border border-slate-200 hover:border-slate-300',
    'glass' => 'bg-white/80 backdrop-blur-sm border border-white/20',
    'gradient' => 'bg-gradient-to-br from-white to-slate-50 border border-slate-200',
    'primary' => 'bg-gradient-to-br from-primary-50 to-primary-100 border border-primary-200',
    'success' => 'bg-gradient-to-br from-green-50 to-green-100 border border-green-200',
    'warning' => 'bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200',
    'danger' => 'bg-gradient-to-br from-red-50 to-red-100 border border-red-200',
    'info' => 'bg-gradient-to-br from-cyan-50 to-cyan-100 border border-cyan-200',
];

$baseClasses = $variants[$variant] ?? $variants['default'];
$classes = $baseClasses . ' ' . ($shadowClasses[$shadow] ?? $shadowClasses['sm']) . ' ' . ($roundedClasses[$rounded] ?? $roundedClasses['xl']);

if ($hover && $variant !== 'glass') {
    $classes .= ' transition-all duration-200 hover:shadow-' . ($shadow === 'sm' ? 'md' : 'lg');
}

if ($loading) {
    $classes .= ' overflow-hidden relative';
}
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($loading)
        <!-- Loading overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-slate-200 via-slate-300 to-slate-200 animate-shimmer opacity-50 z-10"></div>
    @endif

    @if($title || $subtitle || $header)
        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-25 to-slate-50 {{ $paddingClasses[$padding] ?? $paddingClasses['md'] }} {{ $roundedClasses[$rounded] ? 'rounded-t-' . $rounded : '' }}">
            @if($header)
                {{ $header }}
            @else
                <div class="flex items-start justify-between">
                    <div class="min-w-0 flex-1">
                        @if($title)
                            <h3 class="text-lg font-semibold text-slate-900 truncate">{{ $title }}</h3>
                        @endif
                        @if($subtitle)
                            <p class="mt-1 text-sm text-slate-600 line-clamp-2">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if(isset($actions))
                        <div class="ml-4 flex-shrink-0">
                            {{ $actions }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $paddingClasses[$padding] ?? $paddingClasses['md'] }} relative">
        {{ $slot }}
    </div>

    @if($footer)
        <div class="border-t border-slate-200 bg-gradient-to-r from-slate-25 to-slate-50 {{ $paddingClasses[$padding] ?? $paddingClasses['md'] }} {{ $roundedClasses[$rounded] ? 'rounded-b-' . $rounded : '' }}">
            {{ $footer }}
        </div>
    @endif
</div>