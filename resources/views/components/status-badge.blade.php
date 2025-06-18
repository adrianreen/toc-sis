@props([
    'status' => 'default',
    'size' => 'md',
    'variant' => 'subtle',
    'animate' => false,
    'icon' => null
])

@php
$baseClasses = 'inline-flex items-center font-medium rounded-full transition-all duration-200';

$sizes = [
    'xs' => 'px-2 py-0.5 text-xs',
    'sm' => 'px-2.5 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
    'lg' => 'px-4 py-2 text-sm',
    'xl' => 'px-5 py-2.5 text-base',
];

$gaps = [
    'xs' => 'gap-1',
    'sm' => 'gap-1.5',
    'md' => 'gap-1.5',
    'lg' => 'gap-2',
    'xl' => 'gap-2',
];

$statusColors = [
    // Student/Assessment Status
    'active' => [
        'subtle' => 'bg-green-100 text-green-800 border border-green-200 hover:bg-green-200',
        'solid' => 'bg-green-600 text-white shadow-sm hover:bg-green-700',
        'outline' => 'border-2 border-green-500 text-green-700 bg-transparent hover:bg-green-50',
        'dot' => 'bg-green-500',
        'lucide' => 'check-circle'
    ],
    'inactive' => [
        'subtle' => 'bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200',
        'solid' => 'bg-slate-600 text-white shadow-sm hover:bg-slate-700',
        'outline' => 'border-2 border-slate-400 text-slate-700 bg-transparent hover:bg-slate-50',
        'dot' => 'bg-slate-400',
        'lucide' => 'x-circle'
    ],
    'enrolled' => [
        'subtle' => 'bg-blue-100 text-blue-800 border border-blue-200 hover:bg-blue-200',
        'solid' => 'bg-blue-600 text-white shadow-sm hover:bg-blue-700',
        'outline' => 'border-2 border-blue-500 text-blue-700 bg-transparent hover:bg-blue-50',
        'dot' => 'bg-blue-500',
        'lucide' => 'graduation-cap'
    ],
    'completed' => [
        'subtle' => 'bg-toc-100 text-toc-800 border border-toc-200 hover:bg-toc-200',
        'solid' => 'bg-toc-600 text-white shadow-sm hover:bg-toc-700',
        'outline' => 'border-2 border-toc-500 text-toc-700 bg-transparent hover:bg-toc-50',
        'dot' => 'bg-toc-500',
        'lucide' => 'award'
    ],
    'failed' => [
        'subtle' => 'bg-red-100 text-red-800 border border-red-200 hover:bg-red-200',
        'solid' => 'bg-red-600 text-white shadow-sm hover:bg-red-700',
        'outline' => 'border-2 border-red-500 text-red-700 bg-transparent hover:bg-red-50',
        'dot' => 'bg-red-500',
        'lucide' => 'x-circle'
    ],
    'pending' => [
        'subtle' => 'bg-amber-100 text-amber-800 border border-amber-200 hover:bg-amber-200',
        'solid' => 'bg-amber-600 text-white shadow-sm hover:bg-amber-700',
        'outline' => 'border-2 border-amber-500 text-amber-700 bg-transparent hover:bg-amber-50',
        'dot' => 'bg-amber-500',
        'lucide' => 'clock'
    ],
    'deferred' => [
        'subtle' => 'bg-yellow-100 text-yellow-800 border border-yellow-200 hover:bg-yellow-200',
        'solid' => 'bg-yellow-600 text-white shadow-sm hover:bg-yellow-700',
        'outline' => 'border-2 border-yellow-500 text-yellow-700 bg-transparent hover:bg-yellow-50',
        'dot' => 'bg-yellow-500',
        'lucide' => 'pause-circle'
    ],
    'cancelled' => [
        'subtle' => 'bg-red-100 text-red-800 border border-red-200 hover:bg-red-200',
        'solid' => 'bg-red-600 text-white shadow-sm hover:bg-red-700',
        'outline' => 'border-2 border-red-500 text-red-700 bg-transparent hover:bg-red-50',
        'dot' => 'bg-red-500',
        'lucide' => 'x-circle'
    ],
    'submitted' => [
        'subtle' => 'bg-cyan-100 text-cyan-800 border border-cyan-200 hover:bg-cyan-200',
        'solid' => 'bg-cyan-600 text-white shadow-sm hover:bg-cyan-700',
        'outline' => 'border-2 border-cyan-500 text-cyan-700 bg-transparent hover:bg-cyan-50',
        'dot' => 'bg-cyan-500',
        'lucide' => 'upload'
    ],
    'graded' => [
        'subtle' => 'bg-purple-100 text-purple-800 border border-purple-200 hover:bg-purple-200',
        'solid' => 'bg-purple-600 text-white shadow-sm hover:bg-purple-700',
        'outline' => 'border-2 border-purple-500 text-purple-700 bg-transparent hover:bg-purple-50',
        'dot' => 'bg-purple-500',
        'lucide' => 'clipboard-check'
    ],
    'passed' => [
        'subtle' => 'bg-green-100 text-green-800 border border-green-200 hover:bg-green-200',
        'solid' => 'bg-green-600 text-white shadow-sm hover:bg-green-700',
        'outline' => 'border-2 border-green-500 text-green-700 bg-transparent hover:bg-green-50',
        'dot' => 'bg-green-500',
        'lucide' => 'check-circle'
    ],
    
    // Enquiry Status
    'enquiry' => [
        'subtle' => 'bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200',
        'solid' => 'bg-slate-600 text-white shadow-sm hover:bg-slate-700',
        'outline' => 'border-2 border-slate-400 text-slate-700 bg-transparent hover:bg-slate-50',
        'dot' => 'bg-slate-400',
        'lucide' => 'help-circle'
    ],
    'application' => [
        'subtle' => 'bg-indigo-100 text-indigo-800 border border-indigo-200 hover:bg-indigo-200',
        'solid' => 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700',
        'outline' => 'border-2 border-indigo-500 text-indigo-700 bg-transparent hover:bg-indigo-50',
        'dot' => 'bg-indigo-500',
        'lucide' => 'file-text'
    ],
    'accepted' => [
        'subtle' => 'bg-green-100 text-green-800 border border-green-200 hover:bg-green-200',
        'solid' => 'bg-green-600 text-white shadow-sm hover:bg-green-700',
        'outline' => 'border-2 border-green-500 text-green-700 bg-transparent hover:bg-green-50',
        'dot' => 'bg-green-500',
        'lucide' => 'check'
    ],
    'rejected' => [
        'subtle' => 'bg-red-100 text-red-800 border border-red-200 hover:bg-red-200',
        'solid' => 'bg-red-600 text-white shadow-sm hover:bg-red-700',
        'outline' => 'border-2 border-red-500 text-red-700 bg-transparent hover:bg-red-50',
        'dot' => 'bg-red-500',
        'lucide' => 'x'
    ],
    
    // Payment Status
    'paid' => [
        'subtle' => 'bg-green-100 text-green-800 border border-green-200 hover:bg-green-200',
        'solid' => 'bg-green-600 text-white shadow-sm hover:bg-green-700',
        'outline' => 'border-2 border-green-500 text-green-700 bg-transparent hover:bg-green-50',
        'dot' => 'bg-green-500',
        'lucide' => 'dollar-sign'
    ],
    'overdue' => [
        'subtle' => 'bg-red-100 text-red-800 border border-red-200 hover:bg-red-200',
        'solid' => 'bg-red-600 text-white shadow-sm hover:bg-red-700',
        'outline' => 'border-2 border-red-500 text-red-700 bg-transparent hover:bg-red-50',
        'dot' => 'bg-red-500',
        'lucide' => 'alert-circle'
    ],
    
    // Default
    'default' => [
        'subtle' => 'bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200',
        'solid' => 'bg-slate-600 text-white shadow-sm hover:bg-slate-700',
        'outline' => 'border-2 border-slate-400 text-slate-700 bg-transparent hover:bg-slate-50',
        'dot' => 'bg-slate-400',
        'lucide' => 'help-circle'
    ],
];

$colors = $statusColors[$status] ?? $statusColors['default'];
$sizeClass = $sizes[$size] ?? $sizes['md'];

// Build classes
$variantClass = $colors[$variant] ?? $colors['subtle'];
$classes = $baseClasses . ' ' . $sizeClass . ' ' . $variantClass;

// Only add gap if we have an icon or dot
$showIcon = $icon === true || $icon === 'auto';
$customIcon = is_string($icon) && $icon !== 'auto' ? $icon : null;
$lucideIcon = $colors['lucide'] ?? null;
$defaultIcon = $colors['icon'] ?? null;
$hasDot = $variant === 'dot';
$hasIcon = $hasDot || $customIcon || ($showIcon && ($lucideIcon || $defaultIcon));

if ($hasIcon) {
    $gapClass = $gaps[$size] ?? $gaps['md'];
    $classes .= ' ' . $gapClass;
}

if ($animate) {
    $classes .= ' animate-pulse-soft';
}

// Format status text for display
$displayText = ($slot && !$slot->isEmpty()) ? $slot : ucwords(str_replace(['_', '-'], ' ', $status));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($variant === 'dot')
        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $colors['dot'] }}"></span>
    @elseif($customIcon)
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $customIcon !!}
        </svg>
    @elseif($showIcon && $lucideIcon)
        @php
        $iconSvgs = [
            'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'graduation-cap' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
            'pause-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'award' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>',
            'x-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'help-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'upload' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>',
            'clipboard-check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
            'file-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            'x' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
            'dollar-sign' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>',
            'alert-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        ];
        $svgPath = $iconSvgs[$lucideIcon] ?? $iconSvgs['help-circle'];
        @endphp
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $svgPath !!}
        </svg>
    @elseif($showIcon && $defaultIcon)
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $defaultIcon !!}
        </svg>
    @endif
    
    <span class="truncate">{{ $displayText }}</span>
</span>