{{-- resources/views/programmes/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New Programme
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('programmes.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Programme Title *</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                    placeholder="e.g., Early Learning & Care Level 5"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Awarding Body -->
                            <div>
                                <label for="awarding_body" class="block text-sm font-medium text-gray-700">Awarding Body *</label>
                                <input type="text" name="awarding_body" id="awarding_body" value="{{ old('awarding_body') }}" required
                                    placeholder="e.g., QQI, FETAC"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('awarding_body')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NFQ Level -->
                            <div>
                                <label for="nfq_level" class="block text-sm font-medium text-gray-700">NFQ Level *</label>
                                <select name="nfq_level" id="nfq_level" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select NFQ Level</option>
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ old('nfq_level') == $i ? 'selected' : '' }}>
                                            Level {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                @error('nfq_level')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Total Credits -->
                            <div>
                                <label for="total_credits" class="block text-sm font-medium text-gray-700">Total Credits *</label>
                                <input type="number" name="total_credits" id="total_credits" value="{{ old('total_credits') }}" required min="1"
                                    placeholder="e.g., 120"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('total_credits')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                placeholder="Brief description of the programme"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Learning Outcomes -->
                        <div class="mt-6">
                            <label for="learning_outcomes" class="block text-sm font-medium text-gray-700">Learning Outcomes</label>
                            <textarea name="learning_outcomes" id="learning_outcomes" rows="4"
                                placeholder="Key learning outcomes for this programme"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('learning_outcomes') }}</textarea>
                            @error('learning_outcomes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('programmes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Programme
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>