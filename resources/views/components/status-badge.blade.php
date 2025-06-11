@props([
    'status' => 'default',
    'size' => 'md',
    'variant' => 'subtle'
])

@php
$baseClasses = 'inline-flex items-center font-medium rounded-full';

$sizes = [
    'xs' => 'px-2 py-0.5 text-xs',
    'sm' => 'px-2.5 py-1 text-xs',
    'md' => 'px-3 py-1.5 text-sm',
    'lg' => 'px-4 py-2 text-sm',
];

$statusColors = [
    // Student/Assessment Status
    'active' => ['bg' => 'bg-success-100', 'text' => 'text-success-800', 'dot' => 'bg-success-500'],
    'inactive' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400'],
    'completed' => ['bg' => 'bg-toc-100', 'text' => 'text-toc-800', 'dot' => 'bg-toc-500'],
    'failed' => ['bg' => 'bg-danger-100', 'text' => 'text-danger-800', 'dot' => 'bg-danger-500'],
    'pending' => ['bg' => 'bg-warning-100', 'text' => 'text-warning-800', 'dot' => 'bg-warning-500'],
    'submitted' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'dot' => 'bg-blue-500'],
    'graded' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'dot' => 'bg-purple-500'],
    'passed' => ['bg' => 'bg-success-100', 'text' => 'text-success-800', 'dot' => 'bg-success-500'],
    
    // Enquiry Status
    'enquiry' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400'],
    'application' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'dot' => 'bg-blue-500'],
    'accepted' => ['bg' => 'bg-success-100', 'text' => 'text-success-800', 'dot' => 'bg-success-500'],
    'converted' => ['bg' => 'bg-toc-100', 'text' => 'text-toc-800', 'dot' => 'bg-toc-500'],
    'rejected' => ['bg' => 'bg-danger-100', 'text' => 'text-danger-800', 'dot' => 'bg-danger-500'],
    'withdrawn' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400'],
    
    // Payment Status
    'paid' => ['bg' => 'bg-success-100', 'text' => 'text-success-800', 'dot' => 'bg-success-500'],
    'deposit_paid' => ['bg' => 'bg-warning-100', 'text' => 'text-warning-800', 'dot' => 'bg-warning-500'],
    'overdue' => ['bg' => 'bg-danger-100', 'text' => 'text-danger-800', 'dot' => 'bg-danger-500'],
    
    // Default
    'default' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400'],
];

$colors = $statusColors[$status] ?? $statusColors['default'];
$sizeClass = $sizes[$size] ?? $sizes['md'];

$classes = $baseClasses . ' ' . $sizeClass . ' ' . $colors['bg'] . ' ' . $colors['text'];

// Format status text for display
$displayText = $slot->isEmpty() ? ucwords(str_replace(['_', '-'], ' ', $status)) : $slot;
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($variant === 'dot')
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $colors['dot'] }}"></span>
    @endif
    {{ $displayText }}
</span>