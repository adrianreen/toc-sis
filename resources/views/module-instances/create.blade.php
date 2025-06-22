{{-- resources/views/module-instances/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Module Instance
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('module-instances.store') }}">
                        @csrf

                        <!-- Module -->
                        <div class="mb-6">
                            <label for="module_id" class="block text-sm font-medium text-gray-700">Module *</label>
                            <select name="module_id" id="module_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a module</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                        {{ $module->module_code }} - {{ $module->title }} ({{ $module->credit_value }} credits)
                                    </option>
                                @endforeach
                            </select>
                            @error('module_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Delivery Style -->
                        <div class="mb-6">
                            <label for="delivery_style" class="block text-sm font-medium text-gray-700">Delivery Style *</label>
                            <select name="delivery_style" id="delivery_style" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select delivery style</option>
                                <option value="sync" {{ old('delivery_style') === 'sync' ? 'selected' : '' }}>Synchronous (Fixed schedule, cohort-based)</option>
                                <option value="async" {{ old('delivery_style') === 'async' ? 'selected' : '' }}>Asynchronous (Self-paced, flexible)</option>
                            </select>
                            @error('delivery_style')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Target End Date -->
                            <div>
                                <label for="target_end_date" class="block text-sm font-medium text-gray-700">Target End Date</label>
                                <input type="date" name="target_end_date" id="target_end_date" value="{{ old('target_end_date') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('target_end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Optional - estimated completion date for planning purposes</p>
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
                                        <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                            {{ $tutor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tutor_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Max Students -->
                            <div>
                                <label for="max_students" class="block text-sm font-medium text-gray-700">Max Students</label>
                                <input type="number" name="max_students" id="max_students" value="{{ old('max_students') }}" min="1"
                                    placeholder="e.g., 25"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('max_students')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                placeholder="Optional description or notes for this module instance"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('module-instances.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Module Instance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>