<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Edit Student</h1>
                <p class="mt-2 text-gray-600">Update {{ $student->full_name }}'s information</p>
            </div>

            <div class="bg-white shadow-soft rounded-xl p-6">
                <form method="POST" action="{{ route('students.update', $student) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Student Number (Read-only) -->
                        <x-form.input
                            label="Student Number"
                            name="student_number"
                            :value="$student->student_number"
                            disabled
                        />

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-slate-700 mb-2">
                                Status
                                <span class="text-danger-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                class="block w-full rounded-lg border border-slate-300 text-slate-900 placeholder-slate-400 focus:border-toc-500 focus:ring-toc-500 px-4 py-2.5 text-sm transition-all duration-200 focus:outline-none bg-white">
                                <option value="enquiry" {{ old('status', $student->status) === 'enquiry' ? 'selected' : '' }}>Enquiry</option>
                                <option value="enrolled" {{ old('status', $student->status) === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                                <option value="active" {{ old('status', $student->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="deferred" {{ old('status', $student->status) === 'deferred' ? 'selected' : '' }}>Deferred</option>
                                <option value="completed" {{ old('status', $student->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $student->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- First Name -->
                        <x-form.input
                            label="First Name"
                            name="first_name"
                            :value="old('first_name', $student->first_name)"
                            required
                        />

                        <!-- Last Name -->
                        <x-form.input
                            label="Last Name"
                            name="last_name"
                            :value="old('last_name', $student->last_name)"
                            required
                        />

                        <!-- Email -->
                        <x-form.input
                            type="email"
                            label="Email"
                            name="email"
                            :value="old('email', $student->email)"
                            required
                        />

                        <!-- Phone -->
                        <x-form.input
                            label="Phone"
                            name="phone"
                            :value="old('phone', $student->phone)"
                        />

                        <!-- Date of Birth -->
                        <x-form.input
                            type="date"
                            label="Date of Birth"
                            name="date_of_birth"
                            :value="old('date_of_birth', $student->date_of_birth?->format('Y-m-d'))"
                        />

                        <!-- County -->
                        <x-form.input
                            label="County"
                            name="county"
                            :value="old('county', $student->county)"
                        />
                    </div>

                    <!-- Address -->
                    <div class="mt-6">
                        <x-form.input
                            label="Address"
                            name="address"
                            :value="old('address', $student->address)"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <!-- City -->
                        <x-form.input
                            label="City"
                            name="city"
                            :value="old('city', $student->city)"
                        />

                        <!-- Eircode -->
                        <x-form.input
                            label="Eircode"
                            name="eircode"
                            :value="old('eircode', $student->eircode)"
                        />
                    </div>

                    <!-- Notes -->
                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-slate-700 mb-2">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="block w-full rounded-lg border border-slate-300 text-slate-900 placeholder-slate-400 focus:border-toc-500 focus:ring-toc-500 px-4 py-2.5 text-sm transition-all duration-200 focus:outline-none bg-white">{{ old('notes', $student->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Metadata -->
                    <div class="mt-8 p-6 bg-slate-50 rounded-xl">
                        <h4 class="text-sm font-medium text-slate-700 mb-4">Record Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-slate-500">Created:</span>
                                <span class="font-medium ml-1 text-slate-900">{{ $student->created_at->format('d M Y H:i') }}</span>
                                @if($student->createdBy)
                                    <span class="text-slate-500">by {{ $student->createdBy->name }}</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-slate-500">Last Updated:</span>
                                <span class="font-medium ml-1 text-slate-900">{{ $student->updated_at->format('d M Y H:i') }}</span>
                                @if($student->updatedBy)
                                    <span class="text-slate-500">by {{ $student->updatedBy->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex items-center justify-end space-x-4">
                        <a href="{{ route('students.show', $student) }}" 
                           class="px-6 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2.5 text-sm font-medium text-white bg-toc-600 rounded-lg hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-toc-500 focus:ring-offset-2 transition-colors">
                            Update Student
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>