{{-- resources/views/enquiries/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Enquiry {{ $enquiry->enquiry_number }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('enquiries.show', $enquiry) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    View Enquiry
                </a>
                <a href="{{ route('enquiries.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Enquiries
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl">
                <div class="p-6">
                    <form method="POST" action="{{ route('enquiries.update', $enquiry) }}" x-data="enquiryForm()">
                        @csrf
                        @method('PUT')

                        <!-- Personal Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $enquiry->first_name) }}" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('first_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $enquiry->last_name) }}" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('last_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $enquiry->email) }}" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone', $enquiry->phone) }}"
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $enquiry->date_of_birth?->format('Y-m-d')) }}"
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('date_of_birth')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                    <textarea id="address" name="address" rows="3"
                                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $enquiry->address) }}</textarea>
                                    @error('address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                        <input type="text" id="city" name="city" value="{{ old('city', $enquiry->city) }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('city')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="county" class="block text-sm font-medium text-gray-700 mb-2">County</label>
                                        <input type="text" id="county" name="county" value="{{ old('county', $enquiry->county) }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('county')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="eircode" class="block text-sm font-medium text-gray-700 mb-2">Eircode</label>
                                        <input type="text" id="eircode" name="eircode" value="{{ old('eircode', $enquiry->eircode) }}"
                                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('eircode')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Programme Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Programme Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="programme_id" class="block text-sm font-medium text-gray-700 mb-2">Programme *</label>
                                    <select id="programme_id" name="programme_id" required x-model="selectedProgramme" @change="filterCohorts()"
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select a programme</option>
                                        @foreach($programmes as $programme)
                                            <option value="{{ $programme->id }}" {{ old('programme_id', $enquiry->programme_id) == $programme->id ? 'selected' : '' }}>
                                                {{ $programme->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('programme_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="prospective_programme_instance_id" class="block text-sm font-medium text-gray-700 mb-2">Prospective Programme Instance</label>
                                    <select id="prospective_programme_instance_id" name="prospective_programme_instance_id"
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select a programme instance (optional)</option>
                                        @foreach($programmeInstances as $instance)
                                            <option value="{{ $instance->id }}" data-programme="{{ $instance->programme_id }}" 
                                                    {{ old('prospective_programme_instance_id', $enquiry->prospective_programme_instance_id) == $instance->id ? 'selected' : '' }}>
                                                {{ $instance->label }} ({{ $instance->programme->title }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('prospective_programme_instance_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                    <select id="status" name="status" required
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="enquiry" {{ old('status', $enquiry->status) == 'enquiry' ? 'selected' : '' }}>Enquiry</option>
                                        <option value="application" {{ old('status', $enquiry->status) == 'application' ? 'selected' : '' }}>Application</option>
                                        <option value="accepted" {{ old('status', $enquiry->status) == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                        <option value="converted" {{ old('status', $enquiry->status) == 'converted' ? 'selected' : '' }}>Converted</option>
                                        <option value="rejected" {{ old('status', $enquiry->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="withdrawn" {{ old('status', $enquiry->status) == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Payment Status *</label>
                                    <select id="payment_status" name="payment_status" required
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="pending" {{ old('payment_status', $enquiry->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="deposit_paid" {{ old('payment_status', $enquiry->payment_status) == 'deposit_paid' ? 'selected' : '' }}>Deposit Paid</option>
                                        <option value="paid" {{ old('payment_status', $enquiry->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="overdue" {{ old('payment_status', $enquiry->payment_status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    </select>
                                    @error('payment_status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="amount_due" class="block text-sm font-medium text-gray-700 mb-2">Amount Due (€) *</label>
                                    <input type="number" id="amount_due" name="amount_due" value="{{ old('amount_due', $enquiry->amount_due) }}" step="0.01" min="0" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('amount_due')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="amount_paid" class="block text-sm font-medium text-gray-700 mb-2">Amount Paid (€) *</label>
                                    <input type="number" id="amount_paid" name="amount_paid" value="{{ old('amount_paid', $enquiry->amount_paid) }}" step="0.01" min="0" required
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('amount_paid')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="payment_due_date" class="block text-sm font-medium text-gray-700 mb-2">Payment Due Date</label>
                                    <input type="date" id="payment_due_date" name="payment_due_date" value="{{ old('payment_due_date', $enquiry->payment_due_date?->format('Y-m-d')) }}"
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('payment_due_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                            <div class="space-y-4">
                                <div class="flex space-x-6">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="microsoft_account_required" value="1" 
                                               {{ old('microsoft_account_required', $enquiry->microsoft_account_required) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Microsoft 365 account required</span>
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" name="microsoft_account_created" value="1" 
                                               {{ old('microsoft_account_created', $enquiry->microsoft_account_created) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Microsoft 365 account created</span>
                                    </label>
                                </div>
                                @error('microsoft_account_required')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('microsoft_account_created')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea id="notes" name="notes" rows="4" placeholder="Any additional notes or comments..."
                                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $enquiry->notes) }}</textarea>
                                    @error('notes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('enquiries.show', $enquiry) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                                Update Enquiry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function enquiryForm() {
            return {
                selectedProgramme: '{{ old('programme_id', $enquiry->programme_id) }}',
                filterCohorts() {
                    const cohortSelect = document.getElementById('prospective_cohort_id');
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