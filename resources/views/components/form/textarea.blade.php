@props([
    'label' => null,
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'help' => null,
    'rows' => 4,
    'size' => 'md'
])

@php
$inputId = $name ?? $attributes->get('id', 'textarea_' . uniqid());
$hasError = $error || $errors->has($name);
$errorMessage = $error ?? ($name ? $errors->first($name) : null);

$baseClasses = 'block w-full rounded-lg border transition-all duration-200 focus:outline-none resize-y';

$sizeClasses = [
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-4 py-3 text-base',
];

$stateClasses = $hasError 
    ? 'border-danger-300 text-danger-900 placeholder-danger-300 focus:border-danger-500 focus:ring-danger-500' 
    : 'border-slate-300 text-slate-900 placeholder-slate-400 focus:border-toc-500 focus:ring-toc-500';

$classes = $baseClasses . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . $stateClasses;

if ($disabled) {
    $classes .= ' bg-slate-50 cursor-not-allowed';
} elseif ($readonly) {
    $classes .= ' bg-slate-50';
} else {
    $classes .= ' bg-white';
}
@endphp

<div {{ $attributes->only(['class', 'x-data', 'x-show']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-slate-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-danger-500">*</span>
            @endif
        </label>
    @endif

    <textarea 
        id="{{ $inputId }}"
        @if($name) name="{{ $name }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        rows="{{ $rows }}"
        {{ $attributes->except(['class', 'x-data', 'x-show'])->merge(['class' => $classes]) }}
    >{{ $value ?? $slot }}</textarea>

    @if($errorMessage)
        <p class="mt-2 text-sm text-danger-600" id="{{ $inputId }}-error">
            {{ $errorMessage }}
        </p>
    @endif

    @if($help && !$errorMessage)
        <p class="mt-2 text-sm text-slate-500" id="{{ $inputId }}-help">
            {{ $help }}
        </p>
    @endif
</div>