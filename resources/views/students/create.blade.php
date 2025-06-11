{{-- resources/views/students/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Add New Student
            </h2>
            <x-button variant="secondary" href="{{ route('students.index') }}">
                Back to Students
            </x-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-card title="New Student Registration" subtitle="Enter the student's information below">
                <form method="POST" action="{{ route('students.store') }}">
                    @csrf

                    <!-- Personal Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 pb-2 border-b border-slate-200">Personal Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form.input 
                                name="first_name" 
                                label="First Name" 
                                :value="old('first_name')" 
                                required 
                                placeholder="Enter first name"
                            />

                            <x-form.input 
                                name="last_name" 
                                label="Last Name" 
                                :value="old('last_name')" 
                                required 
                                placeholder="Enter last name"
                            />

                            <x-form.input 
                                type="email"
                                name="email" 
                                label="Email" 
                                :value="old('email')" 
                                required 
                                placeholder="Enter email address"
                            />

                            <x-form.input 
                                type="tel"
                                name="phone" 
                                label="Phone" 
                                :value="old('phone')" 
                                placeholder="Enter phone number"
                            />

                            <x-form.input 
                                type="date"
                                name="date_of_birth" 
                                label="Date of Birth" 
                                :value="old('date_of_birth')" 
                                class="md:col-span-1"
                            />
                        </div>
                    </div>

                    <!-- Address Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 pb-2 border-b border-slate-200">Address Information</h3>
                        <div class="space-y-6">
                            <x-form.input 
                                name="address" 
                                label="Address" 
                                :value="old('address')" 
                                placeholder="Enter full address"
                            />

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <x-form.input 
                                    name="city" 
                                    label="City" 
                                    :value="old('city')" 
                                    placeholder="Enter city"
                                />

                                <x-form.input 
                                    name="county" 
                                    label="County" 
                                    :value="old('county')" 
                                    placeholder="Enter county"
                                />

                                <x-form.input 
                                    name="eircode" 
                                    label="Eircode" 
                                    :value="old('eircode')" 
                                    placeholder="Enter eircode"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 pb-2 border-b border-slate-200">Additional Information</h3>
                        <div class="space-y-6">
                            <x-form.textarea 
                                name="notes" 
                                label="Notes" 
                                :value="old('notes')" 
                                rows="4"
                                placeholder="Any additional notes or comments..."
                            />
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4 pt-6 border-t border-slate-200">
                        <x-button variant="secondary" href="{{ route('students.index') }}">
                            Cancel
                        </x-button>
                        <x-button type="submit" variant="primary">
                            Create Student
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>