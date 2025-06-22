{{-- resources/views/enquiries/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Create New Enquiry
            </h2>
            <x-button variant="secondary" href="{{ route('enquiries.index') }}">
                Back to Enquiries
            </x-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-card title="New Enquiry Registration" subtitle="Enter the prospective student's information below">
                <form method="POST" action="{{ route('enquiries.store') }}" x-data="enquiryForm()">
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
                            <x-form.textarea 
                                name="address" 
                                label="Address" 
                                :value="old('address')" 
                                rows="3"
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

                    <!-- Programme Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 pb-2 border-b border-slate-200">Programme Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form.select 
                                name="programme_id" 
                                label="Programme" 
                                :value="old('programme_id')" 
                                required 
                                placeholder="Select a programme"
                                x-model="selectedProgramme" 
                                @change="filterCohorts()"
                            >
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}">{{ $programme->title }}</option>
                                @endforeach
                            </x-form.select>

                            <x-form.select 
                                name="prospective_programme_instance_id" 
                                label="Prospective Programme Instance" 
                                :value="old('prospective_programme_instance_id')" 
                                placeholder="Select a programme instance (optional)"
                            >
                                @foreach($programmeInstances as $instance)
                                    <option value="{{ $instance->id }}" data-programme="{{ $instance->programme_id }}">
                                        {{ $instance->label }} ({{ $instance->programme->title }})
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>
                    </div>

                    <!-- Payment Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 pb-2 border-b border-slate-200">Payment Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-form.input 
                                type="number"
                                name="amount_due" 
                                label="Amount Due (€)" 
                                :value="old('amount_due', '0')" 
                                required 
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                            />

                            <x-form.input 
                                type="date"
                                name="payment_due_date" 
                                label="Payment Due Date" 
                                :value="old('payment_due_date')"
                            />
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 pb-2 border-b border-slate-200">Additional Information</h3>
                        <div class="space-y-6">
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="microsoft_account_required"
                                    name="microsoft_account_required" 
                                    value="1" 
                                    {{ old('microsoft_account_required') ? 'checked' : '' }}
                                    class="h-4 w-4 text-toc-600 focus:ring-toc-500 border-slate-300 rounded"
                                >
                                <label for="microsoft_account_required" class="ml-3 text-sm font-medium text-slate-700">
                                    Microsoft 365 account required
                                </label>
                            </div>

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
                        <x-button variant="secondary" href="{{ route('enquiries.index') }}">
                            Cancel
                        </x-button>
                        <x-button type="submit" variant="primary">
                            Create Enquiry
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    <script>
        function enquiryForm() {
            return {
                selectedProgramme: '{{ old('programme_id') }}',
                filterCohorts() {
                    const cohortSelect = document.querySelector('select[name="prospective_cohort_id"]');
                    const options = cohortSelect.querySelectorAll('option');
                    
                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = '';
                            return;
                        }
                        
                        const programmeId = option.dataset.programme;
                        if (!this.selectedProgramme || programmeId === this.selectedProgramme) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                    
                    // Reset cohort selection if currently selected cohort doesn't match programme
                    const selectedOption = cohortSelect.querySelector('option:checked');
                    if (selectedOption && selectedOption.dataset.programme && 
                        selectedOption.dataset.programme !== this.selectedProgramme) {
                        cohortSelect.value = '';
                    }
                }
            }
        }
    </script>
</x-app-layout>