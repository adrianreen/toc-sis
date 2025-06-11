@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'help' => null,
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left'
])

@php
$inputId = $name ?? $attributes->get('id', 'input_' . uniqid());
$hasError = $error || $errors->has($name);
$errorMessage = $error ?? ($name ? $errors->first($name) : null);

$baseClasses = 'block w-full rounded-lg border transition-all duration-200 focus:outline-none';

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

if ($icon) {
    $classes .= $iconPosition === 'left' ? ' pl-10' : ' pr-10';
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

    <div class="relative">
        @if($icon && $iconPosition === 'left')
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <x-icon :name="$icon" class="h-5 w-5 text-slate-400" />
            </div>
        @endif

        <input 
            type="{{ $type }}"
            id="{{ $inputId }}"
            @if($name) name="{{ $name }}" @endif
            @if($value !== null) value="{{ $value }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->except(['class', 'x-data', 'x-show'])->merge(['class' => $classes]) }}
        />

        @if($icon && $iconPosition === 'right')
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <x-icon :name="$icon" class="h-5 w-5 text-slate-400" />
            </div>
        @endif
    </div>

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