{{-- Help Bubble Component --}}
@props(['title' => 'Help', 'position' => 'bottom-right'])

<div x-data="helpBubble()" class="fixed {{ $position === 'bottom-right' ? 'bottom-6 right-6' : 'bottom-6 left-6' }} z-50">
    <!-- Help Button -->
    <button 
        @click="toggle()"
        class="w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center group"
        :class="{ 'bg-blue-700 shadow-xl': isOpen }"
    >
        <i data-lucide="help-circle" class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': isOpen }"></i>
    </button>

    <!-- Help Panel -->
    <div 
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 transform scale-95 translate-y-2"
        class="absolute {{ $position === 'bottom-right' ? 'bottom-16 right-0' : 'bottom-16 left-0' }} w-80 bg-white rounded-lg shadow-2xl border border-slate-200"
        @click.away="close()"
    >
        <!-- Header -->
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
            </div>
            <button @click="close()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-4 max-h-96 overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</div>

<script>
function helpBubble() {
    return {
        isOpen: false,
        
        toggle() {
            this.isOpen = !this.isOpen;
        },
        
        close() {
            this.isOpen = false;
        }
    }
}
</script>