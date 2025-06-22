{{-- Create Programme Instance --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Programme Instance
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('programme-instances.store') }}">
                        @csrf

                        {{-- Programme Selection --}}
                        <div class="mb-6">
                            <label for="programme_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Programme Blueprint *
                            </label>
                            <select name="programme_id" id="programme_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a programme blueprint</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}" {{ old('programme_id') == $programme->id ? 'selected' : '' }}>
                                        {{ $programme->title }} - {{ $programme->awarding_body }} (NQF {{ $programme->nfq_level }})
                                    </option>
                                @endforeach
                            </select>
                            @error('programme_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Instance Label --}}
                        <div class="mb-6">
                            <label for="label" class="block text-sm font-medium text-gray-700 mb-2">
                                Instance Label *
                            </label>
                            <input type="text" name="label" id="label" 
                                value="{{ old('label') }}" 
                                placeholder="e.g., September 2024 Intake, Spring 2025 Cohort"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('label')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                A descriptive label to distinguish this instance from others (e.g., intake period, cohort name)
                            </p>
                        </div>

                        {{-- Intake Start Date --}}
                        <div class="mb-6">
                            <label for="intake_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Intake Start Date *
                            </label>
                            <input type="date" name="intake_start_date" id="intake_start_date" 
                                value="{{ old('intake_start_date') }}" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('intake_start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Intake End Date --}}
                        <div class="mb-6">
                            <label for="intake_end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Intake End Date (Optional)
                            </label>
                            <input type="date" name="intake_end_date" id="intake_end_date" 
                                value="{{ old('intake_end_date') }}" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @error('intake_end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                Leave blank for rolling enrolments. Set a date to close enrolments after this date.
                            </p>
                        </div>

                        {{-- Default Delivery Style --}}
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Default Delivery Style *
                            </label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input type="radio" name="default_delivery_style" value="sync" id="sync"
                                        {{ old('default_delivery_style', 'sync') == 'sync' ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="sync" class="ml-3 block text-sm text-gray-700">
                                        <strong>Synchronous</strong> - Fixed schedule, cohort-based learning
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="default_delivery_style" value="async" id="async"
                                        {{ old('default_delivery_style') == 'async' ? 'checked' : '' }}
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="async" class="ml-3 block text-sm text-gray-700">
                                        <strong>Asynchronous</strong> - Self-paced, flexible learning
                                    </label>
                                </div>
                            </div>
                            @error('default_delivery_style')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('programme-instances.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                Create Programme Instance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>