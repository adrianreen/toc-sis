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
                            <input type="text" value="{{ $moduleInstance->module->code }} - {{ $moduleInstance->module->title }}" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">Module cannot be changed after creation</p>
                        </div>

                        <!-- Cohort (Read-only) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700">Cohort</label>
                            <input type="text" value="{{ $moduleInstance->cohort->programme->code }} - {{ $moduleInstance->cohort->code }} - {{ $moduleInstance->cohort->name }}" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">Cohort cannot be changed after creation</p>
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

                            <!-- End Date -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date *</label>
                                <input type="date" name="end_date" id="end_date" 
                                    value="{{ old('end_date', $moduleInstance->end_date->format('Y-m-d')) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Teacher -->
                            <div>
                                <label for="teacher_id" class="block text-sm font-medium text-gray-700">Teacher</label>
                                <select name="teacher_id" id="teacher_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Not Assigned</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" 
                                            {{ old('teacher_id', $moduleInstance->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if($moduleInstance->teacher_id && old('teacher_id', $moduleInstance->teacher_id) != $moduleInstance->teacher_id)
                                    <p class="mt-1 text-xs text-yellow-600">
                                        Warning: Changing the teacher will affect {{ $moduleInstance->studentEnrolments->count() }} enrolled students
                                    </p>
                                @endif
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="planned" {{ old('status', $moduleInstance->status) === 'planned' ? 'selected' : '' }}>Planned</option>
                                    <option value="active" {{ old('status', $moduleInstance->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ old('status', $moduleInstance->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                @error('status')
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
                                    <span class="font-medium ml-1">{{ $moduleInstance->studentEnrolments->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Active Students:</span>
                                    <span class="font-medium ml-1">{{ $moduleInstance->studentEnrolments->where('status', 'active')->count() }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Completed:</span>
                                    <span class="font-medium ml-1">{{ $moduleInstance->studentEnrolments->where('status', 'completed')->count() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Teacher Change History -->
                        @if($teacherChanges->count() > 0)
                            <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Teacher Assignment History</h4>
                                <div class="space-y-2 text-sm">
                                    @foreach($teacherChanges as $change)
                                        <div class="flex justify-between">
                                            <span>
                                                @if($change->properties['old_teacher_id'])
                                                    From {{ \App\Models\User::find($change->properties['old_teacher_id'])?->name ?? 'Unknown' }}
                                                @else
                                                    Initially unassigned
                                                @endif
                                                → 
                                                {{ \App\Models\User::find($change->properties['new_teacher_id'])?->name ?? 'Unknown' }}
                                            </span>
                                            <span class="text-gray-500">
                                                {{ $change->created_at->format('d M Y') }} by {{ $change->causer?->name }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

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