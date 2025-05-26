{{-- resources/views/programmes/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Programme: {{ $programme->code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('programmes.update', $programme) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Code (Read-only) -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700">Programme Code</label>
                                <input type="text" value="{{ $programme->code }}" disabled
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                                <p class="mt-1 text-xs text-gray-500">Code cannot be changed after creation</p>
                            </div>

                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Programme Title *</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $programme->title) }}" required
                                    placeholder="e.g., Early Learning & Care Level 5"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Enrolment Type -->
                        <div class="mt-6">
                            <label for="enrolment_type" class="block text-sm font-medium text-gray-700">Enrolment Type *</label>
                            <select name="enrolment_type" id="enrolment_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="cohort" {{ old('enrolment_type', $programme->enrolment_type) === 'cohort' ? 'selected' : '' }}>
                                    Cohort Based (e.g., ELC programmes)
                                </option>
                                <option value="rolling" {{ old('enrolment_type', $programme->enrolment_type) === 'rolling' ? 'selected' : '' }}>
                                    Rolling Enrolment
                                </option>
                                <option value="academic_term" {{ old('enrolment_type', $programme->enrolment_type) === 'academic_term' ? 'selected' : '' }}>
                                    Academic Term
                                </option>
                            </select>
                            @error('enrolment_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Warning: Changing enrolment type may affect existing cohorts and enrolments</p>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $programme->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                    {{ old('is_active', $programme->is_active) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="is_active" class="ml-2 text-sm text-gray-600">
                                    Programme is active
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Inactive programmes cannot accept new enrolments</p>
                        </div>

                        <!-- Current Statistics -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Current Programme Statistics</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Total Enrolments:</span>
                                    <span class="font-medium ml-1">{{ $programme->enrolments->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Active Students:</span>
                                    <span class="font-medium ml-1">{{ $programme->enrolments->where('status', 'active')->count() }}</span>
                                </div>
                                @if($programme->isCohortBased())
                                    <div>
                                        <span class="text-gray-600">Active Cohorts:</span>
                                        <span class="font-medium ml-1">{{ $programme->cohorts->where('status', 'active')->count() }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('programmes.show', $programme) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Programme
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>