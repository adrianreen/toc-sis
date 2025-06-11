@props([
    'label' => null,
    'name' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
    'size' => 'md'
])

@php
$inputId = $name ?? $attributes->get('id', 'select_' . uniqid());
$hasError = $error || $errors->has($name);
$errorMessage = $error ?? ($name ? $errors->first($name) : null);

$baseClasses = 'block w-full rounded-lg border transition-all duration-200 focus:outline-none';

$sizeClasses = [
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-4 py-3 text-base',
];

$stateClasses = $hasError 
    ? 'border-danger-300 text-danger-900 focus:border-danger-500 focus:ring-danger-500' 
    : 'border-slate-300 text-slate-900 focus:border-toc-500 focus:ring-toc-500';

$classes = $baseClasses . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . $stateClasses;

if ($disabled) {
    $classes .= ' bg-slate-50 cursor-not-allowed';
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

    <select 
        id="{{ $inputId }}"
        @if($name) name="{{ $name }}" @endif
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->except(['class', 'x-data', 'x-show'])->merge(['class' => $classes]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @if(!empty($options))
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ $value == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

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