{{-- resources/views/module-instances/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Module Instance: {{ $moduleInstance->instance_code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('module-instances.update', $moduleInstance) }}">
                        @csrf
                        @method('PUT')

                        <!-- Module (Read-only) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Module</label>
                            <input type="text" value="{{ $moduleInstance->module->module_code }} - {{ $moduleInstance->module->title }}" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">Module cannot be changed after creation</p>
                        </div>

                        <!-- Programme Instances (Read-only) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Programme Instances</label>
                            <input type="text" value="@if($moduleInstance->programmeInstances->count() > 0){{ $moduleInstance->programmeInstances->pluck('label')->implode(', ') }}@else Standalone Module @endif" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">Programme instance associations cannot be changed after creation</p>
                        </div>

                        <!-- Instance Code (Read-only) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Instance Code</label>
                            <input type="text" value="{{ $moduleInstance->instance_code }}" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                                <input type="date" name="start_date" id="start_date" 
                                    value="{{ old('start_date', $moduleInstance->start_date->format('Y-m-d')) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Target End Date -->
                            <div>
                                <label for="target_end_date" class="block text-sm font-medium text-gray-700">Target End Date</label>
                                <input type="date" name="target_end_date" id="target_end_date" 
                                    value="{{ old('target_end_date', $moduleInstance->target_end_date?->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('target_end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Optional - estimated completion date</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Tutor -->
                            <div>
                                <label for="tutor_id" class="block text-sm font-medium text-gray-700">Tutor</label>
                                <select name="tutor_id" id="tutor_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Not Assigned</option>
                                    @foreach($tutors as $tutor)
                                        <option value="{{ $tutor->id }}" 
                                            {{ old('tutor_id', $moduleInstance->tutor_id) == $tutor->id ? 'selected' : '' }}>
                                            {{ $tutor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tutor_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if($moduleInstance->tutor_id)
                                    <p class="mt-1 text-xs text-yellow-600">
                                        Warning: Changing the tutor will affect {{ $moduleInstance->enrolments->count() }} enrolled students
                                    </p>
                                @endif
                            </div>

                            <!-- Delivery Style -->
                            <div>
                                <label for="delivery_style" class="block text-sm font-medium text-gray-700">Delivery Style *</label>
                                <select name="delivery_style" id="delivery_style" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="sync" {{ old('delivery_style', $moduleInstance->delivery_style) === 'sync' ? 'selected' : '' }}>Synchronous (Fixed schedule, cohort-based)</option>
                                    <option value="async" {{ old('delivery_style', $moduleInstance->delivery_style) === 'async' ? 'selected' : '' }}>Asynchronous (Self-paced, flexible)</option>
                                </select>
                                @error('delivery_style')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Current Statistics -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Instance Statistics</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Enrolled Students:</span>
                                    <span class="font-medium ml-1">{{ $moduleInstance->enrolments->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Active Students:</span>
                                    <span class="font-medium ml-1">{{ $moduleInstance->enrolments->where('status', 'active')->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Completed:</span>
                                    <span class="font-medium ml-1">{{ $moduleInstance->enrolments->where('status', 'completed')->count() }}</span>
                                </div>
                            </div>
                        </div>


                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('module-instances.show', $moduleInstance) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Module Instance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>