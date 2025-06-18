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
    'iconPosition' => 'left',
    'variant' => 'default',
    'loading' => false,
    'clearable' => false
])

@php
$inputId = $name ?? $attributes->get('id', 'input_' . uniqid());
$hasError = $error || $errors->has($name);
$errorMessage = $error ?? ($name ? $errors->first($name) : null);

$baseClasses = 'block w-full rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1';

$sizeClasses = [
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-4 py-3 text-base',
    'xl' => 'px-5 py-4 text-lg',
];

$variants = [
    'default' => 'bg-white',
    'filled' => 'bg-slate-50 border-slate-200 focus:bg-white',
    'outlined' => 'bg-transparent border-2',
    'underlined' => 'bg-transparent border-0 border-b-2 rounded-none px-0',
];

$stateClasses = $hasError 
    ? 'border-red-300 text-red-900 placeholder-red-400 focus:border-red-500 focus:ring-red-500' 
    : 'border-slate-300 text-slate-900 placeholder-slate-400 focus:border-toc-500 focus:ring-toc-500 hover:border-slate-400';

$classes = $baseClasses . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . $stateClasses;

if ($disabled) {
    $classes .= ' bg-slate-100 cursor-not-allowed opacity-60';
} elseif ($readonly) {
    $classes .= ' bg-slate-50 cursor-default';
}

$iconAdjustment = '';
if ($icon) {
    $iconAdjustment = $iconPosition === 'left' ? 'pl-10' : 'pr-10';
}

if ($clearable && !$disabled && !$readonly) {
    $iconAdjustment .= ' pr-10';
}

if ($iconAdjustment) {
    $classes .= ' ' . $iconAdjustment;
}
@endphp

<div {{ $attributes->only(['class', 'x-data', 'x-show', 'wire:ignore']) }} 
     @if($clearable) x-data="{ value: '{{ $value }}', showClear: false }" @endif>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-slate-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1" title="Required field">*</span>
            @endif
        </label>
    @endif

    <div class="relative group">
        @if($icon && $iconPosition === 'left')
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <x-icon :name="$icon" class="h-5 w-5 {{ $hasError ? 'text-red-400' : 'text-slate-400 group-focus-within:text-toc-500' }} transition-colors duration-200" />
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
            @if($clearable) 
                x-model="value" 
                @input="showClear = $event.target.value.length > 0"
                @focus="showClear = $event.target.value.length > 0"
                @blur="setTimeout(() => showClear = false, 150)"
            @endif
            {{ $attributes->except(['class', 'x-data', 'x-show', 'wire:ignore'])->merge(['class' => $classes]) }}
            @if($loading) readonly @endif
        />

        @if($loading)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="animate-spin h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        @elseif($clearable && !$disabled && !$readonly)
            <button 
                type="button"
                x-show="showClear"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click="value = ''; $refs.input.value = ''; $refs.input.focus(); showClear = false"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none focus:text-slate-600 transition-colors duration-200"
                title="Clear input"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        @elseif($icon && $iconPosition === 'right' && !$loading)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <x-icon :name="$icon" class="h-5 w-5 {{ $hasError ? 'text-red-400' : 'text-slate-400 group-focus-within:text-toc-500' }} transition-colors duration-200" />
            </div>
        @endif

        <!-- Enhanced focus ring -->
        <div class="absolute inset-0 rounded-lg pointer-events-none opacity-0 group-focus-within:opacity-100 transition-opacity duration-200 ring-2 ring-toc-500 ring-offset-2"></div>
    </div>

    @if($errorMessage)
        <div class="mt-2 flex items-start space-x-2 animate-slide-down">
            <svg class="h-4 w-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-red-600" id="{{ $inputId }}-error">
                {{ $errorMessage }}
            </p>
        </div>
    @endif

    @if($help && !$errorMessage)
        <p class="mt-2 text-sm text-slate-500 flex items-start space-x-2" id="{{ $inputId }}-help">
            <svg class="h-4 w-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ $help }}</span>
        </p>
    @endif
</div>