{{-- resources/views/repeat-assessments/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('repeat-assessments.show', $repeatAssessment) }}" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Edit Repeat Assessment
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $repeatAssessment->student->full_name }} - {{ $repeatAssessment->moduleInstance->module->title }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('repeat-assessments.update', $repeatAssessment) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Student & Assessment Information (Read-only) -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Student & Assessment Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Student Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Student</label>
                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                <p class="text-sm text-gray-900">{{ $repeatAssessment->student->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ $repeatAssessment->student->student_number }} • {{ $repeatAssessment->student->email }}</p>
                            </div>
                        </div>

                        <!-- Assessment Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Original Assessment</label>
                            <div class="mt-1 p-3 bg-gray-50 rounded-md">
                                <p class="text-sm text-gray-900">{{ $repeatAssessment->moduleInstance->module->title }}</p>
                                <p class="text-xs text-gray-500">
                                    Grade: {{ $repeatAssessment->studentAssessment->grade ?? 'N/A' }}% • 
                                    Status: {{ ucfirst($repeatAssessment->studentAssessment->status ?? 'Unknown') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Repeat Assessment Details -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Repeat Assessment Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending" {{ $repeatAssessment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $repeatAssessment->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="submitted" {{ $repeatAssessment->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="graded" {{ $repeatAssessment->status == 'graded' ? 'selected' : '' }}>Graded</option>
                                <option value="passed" {{ $repeatAssessment->status == 'passed' ? 'selected' : '' }}>Passed</option>
                                <option value="failed" {{ $repeatAssessment->status == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Grade Cap -->
                        <div>
                            <label for="cap_grade" class="block text-sm font-medium text-gray-700">Maximum Grade (%)</label>
                            <input type="number" name="cap_grade" id="cap_grade" min="0" max="100" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('cap_grade', $repeatAssessment->cap_grade) }}">
                            @error('cap_grade')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="repeat_due_date" class="block text-sm font-medium text-gray-700">Repeat Due Date</label>
                            <input type="date" name="repeat_due_date" id="repeat_due_date" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('repeat_due_date', $repeatAssessment->repeat_due_date?->format('Y-m-d')) }}">
                            @error('repeat_due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" id="priority" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="normal" {{ $repeatAssessment->priority == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ $repeatAssessment->priority == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ $repeatAssessment->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reason -->
                        <div class="md:col-span-2">
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Repeat</label>
                            <textarea name="reason" id="reason" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('reason', $repeatAssessment->reason) }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Payment Amount -->
                        <div>
                            <label for="payment_amount" class="block text-sm font-medium text-gray-700">Payment Amount (€)</label>
                            <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('payment_amount', $repeatAssessment->payment_amount) }}">
                            @error('payment_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Status -->
                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700">Payment Status</label>
                            <select name="payment_status" id="payment_status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending" {{ $repeatAssessment->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $repeatAssessment->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="waived" {{ $repeatAssessment->payment_status == 'waived' ? 'selected' : '' }}>Waived</option>
                            </select>
                            @error('payment_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" id="payment_method" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select method...</option>
                                <option value="online" {{ $repeatAssessment->payment_method == 'online' ? 'selected' : '' }}>Online Payment</option>
                                <option value="bank_transfer" {{ $repeatAssessment->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cheque" {{ $repeatAssessment->payment_method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="cash" {{ $repeatAssessment->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Date -->
                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date</label>
                            <input type="date" name="payment_date" id="payment_date" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('payment_date', $repeatAssessment->payment_date?->format('Y-m-d')) }}">
                            @error('payment_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Notes -->
                        <div class="md:col-span-2">
                            <label for="payment_notes" class="block text-sm font-medium text-gray-700">Payment Notes</label>
                            <textarea name="payment_notes" id="payment_notes" rows="2" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                      placeholder="Add any notes about the payment...">{{ old('payment_notes', $repeatAssessment->payment_notes) }}</textarea>
                            @error('payment_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Communication & Management -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Communication & Management</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Assigned Staff -->
                        <div>
                            <label for="assigned_staff" class="block text-sm font-medium text-gray-700">Assigned Staff</label>
                            <input type="text" name="assigned_staff" id="assigned_staff" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('assigned_staff', $repeatAssessment->assigned_staff) }}" 
                                   placeholder="Enter staff member name">
                            @error('assigned_staff')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notification Method -->
                        <div>
                            <label for="notification_method" class="block text-sm font-medium text-gray-700">Preferred Contact Method</label>
                            <select name="notification_method" id="notification_method" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="email" {{ $repeatAssessment->notification_method == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="post" {{ $repeatAssessment->notification_method == 'post' ? 'selected' : '' }}>Post</option>
                                <option value="phone" {{ $repeatAssessment->notification_method == 'phone' ? 'selected' : '' }}>Phone</option>
                                <option value="in_person" {{ $repeatAssessment->notification_method == 'in_person' ? 'selected' : '' }}>In Person</option>
                            </select>
                            @error('notification_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Student Notified -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" name="student_notified" id="student_notified" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       {{ $repeatAssessment->student_notified ? 'checked' : '' }}>
                                <label for="student_notified" class="ml-2 block text-sm text-gray-900">
                                    Student has been notified
                                </label>
                            </div>
                        </div>

                        <!-- Last Contact Date -->
                        <div>
                            <label for="last_contact_date" class="block text-sm font-medium text-gray-700">Last Contact Date</label>
                            <input type="date" name="last_contact_date" id="last_contact_date" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('last_contact_date', $repeatAssessment->last_contact_date?->format('Y-m-d')) }}">
                            @error('last_contact_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Staff Notes -->
                        <div class="md:col-span-2">
                            <label for="staff_notes" class="block text-sm font-medium text-gray-700">Staff Notes</label>
                            <textarea name="staff_notes" id="staff_notes" rows="4" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                      placeholder="Add any relevant notes for this repeat assessment...">{{ old('staff_notes', $repeatAssessment->staff_notes) }}</textarea>
                            @error('staff_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Student Response -->
                        <div class="md:col-span-2">
                            <label for="student_response" class="block text-sm font-medium text-gray-700">Student Response</label>
                            <textarea name="student_response" id="student_response" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                      placeholder="Record any response or communication from the student...">{{ old('student_response', $repeatAssessment->student_response) }}</textarea>
                            @error('student_response')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Moodle Integration -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Moodle Integration</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Moodle Setup Completed -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" name="moodle_setup_completed" id="moodle_setup_completed" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       {{ $repeatAssessment->moodle_setup_completed ? 'checked' : '' }}>
                                <label for="moodle_setup_completed" class="ml-2 block text-sm text-gray-900">
                                    Moodle setup completed
                                </label>
                            </div>
                        </div>

                        <!-- Moodle Setup Date -->
                        <div>
                            <label for="moodle_setup_date" class="block text-sm font-medium text-gray-700">Moodle Setup Date</label>
                            <input type="date" name="moodle_setup_date" id="moodle_setup_date" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('moodle_setup_date', $repeatAssessment->moodle_setup_date?->format('Y-m-d')) }}">
                            @error('moodle_setup_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Moodle Notes -->
                        <div class="md:col-span-2">
                            <label for="moodle_notes" class="block text-sm font-medium text-gray-700">Moodle Setup Notes</label>
                            <textarea name="moodle_notes" id="moodle_notes" rows="2" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                      placeholder="Add any notes about the Moodle setup...">{{ old('moodle_notes', $repeatAssessment->moodle_notes) }}</textarea>
                            @error('moodle_notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('repeat-assessments.show', $repeatAssessment) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>
                        Update Repeat Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-set payment date when payment status is changed to 'paid'
        document.getElementById('payment_status').addEventListener('change', function() {
            const paymentDateField = document.getElementById('payment_date');
            
            if (this.value === 'paid' && !paymentDateField.value) {
                paymentDateField.value = new Date().toISOString().split('T')[0];
            }
        });

        // Auto-set Moodle setup date when checkbox is checked
        document.getElementById('moodle_setup_completed').addEventListener('change', function() {
            const setupDateField = document.getElementById('moodle_setup_date');
            
            if (this.checked && !setupDateField.value) {
                setupDateField.value = new Date().toISOString().split('T')[0];
            }
        });

        // Auto-set last contact date when student notified is checked
        document.getElementById('student_notified').addEventListener('change', function() {
            const contactDateField = document.getElementById('last_contact_date');
            
            if (this.checked && !contactDateField.value) {
                contactDateField.value = new Date().toISOString().split('T')[0];
            }
        });
    </script>
</x-app-layout>