{{-- resources/views/repeat-assessments/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('repeat-assessments.index') }}" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Repeat Assessment Details
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $repeatAssessment->student->full_name }} - {{ $repeatAssessment->moduleInstance->module->title }}
                    </p>
                </div>
            </div>
            <div class="flex space-x-2">
                @if($repeatAssessment->canApprove())
                    <form action="{{ route('repeat-assessments.approve', $repeatAssessment) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <i data-lucide="check" class="w-4 h-4 inline mr-1"></i>
                            Approve
                        </button>
                    </form>
                @endif
                
                @if($repeatAssessment->canSetupMoodle())
                    <form action="{{ route('repeat-assessments.setup-moodle', $repeatAssessment) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i data-lucide="external-link" class="w-4 h-4 inline mr-1"></i>
                            Setup Moodle
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('repeat-assessments.edit', $repeatAssessment) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i data-lucide="edit" class="w-4 h-4 inline mr-1"></i>
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Status Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Status Card -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="text-2xl font-semibold text-gray-900 capitalize">
                                {{ str_replace('_', ' ', $repeatAssessment->status) }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i data-lucide="info" class="w-6 h-6 text-gray-600"></i>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Card -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Payment</p>
                            <p class="text-2xl font-semibold {{ $repeatAssessment->payment_status === 'paid' ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($repeatAssessment->payment_status) }}
                            </p>
                            @if($repeatAssessment->payment_amount)
                                <p class="text-sm text-gray-500">€{{ number_format($repeatAssessment->payment_amount, 2) }}</p>
                            @endif
                        </div>
                        <div class="w-12 h-12 {{ $repeatAssessment->payment_status === 'paid' ? 'bg-green-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center">
                            <i data-lucide="{{ $repeatAssessment->payment_status === 'paid' ? 'check-circle' : 'credit-card' }}" class="w-6 h-6 {{ $repeatAssessment->payment_status === 'paid' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                    </div>
                </div>

                <!-- Notification Status Card -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Student Notified</p>
                            <p class="text-2xl font-semibold {{ $repeatAssessment->student_notified ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ $repeatAssessment->student_notified ? 'Yes' : 'No' }}
                            </p>
                            @if($repeatAssessment->student_notified_date)
                                <p class="text-sm text-gray-500">{{ $repeatAssessment->student_notified_date->format('M j, Y') }}</p>
                            @endif
                        </div>
                        <div class="w-12 h-12 {{ $repeatAssessment->student_notified ? 'bg-green-100' : 'bg-yellow-100' }} rounded-lg flex items-center justify-center">
                            <i data-lucide="{{ $repeatAssessment->student_notified ? 'mail-check' : 'mail' }}" class="w-6 h-6 {{ $repeatAssessment->student_notified ? 'text-green-600' : 'text-yellow-600' }}"></i>
                        </div>
                    </div>
                </div>

                <!-- Moodle Status Card -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Moodle Setup</p>
                            <p class="text-2xl font-semibold {{ $repeatAssessment->moodle_setup_completed ? 'text-green-600' : 'text-gray-600' }}">
                                {{ $repeatAssessment->moodle_setup_completed ? 'Complete' : 'Pending' }}
                            </p>
                            @if($repeatAssessment->moodle_setup_date)
                                <p class="text-sm text-gray-500">{{ $repeatAssessment->moodle_setup_date->format('M j, Y') }}</p>
                            @endif
                        </div>
                        <div class="w-12 h-12 {{ $repeatAssessment->moodle_setup_completed ? 'bg-green-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center">
                            <i data-lucide="external-link" class="w-6 h-6 {{ $repeatAssessment->moodle_setup_completed ? 'text-green-600' : 'text-gray-600' }}"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Student & Assessment Details -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Student Information -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Student Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $repeatAssessment->student->full_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Student Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $repeatAssessment->student->student_number }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $repeatAssessment->student->email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Status</label>
                                <p class="mt-1 text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $repeatAssessment->student->status) }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="{{ route('students.show', $repeatAssessment->student) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                View Full Student Profile →
                            </a>
                        </div>
                    </div>

                    <!-- Assessment Details -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assessment Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Module</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $repeatAssessment->moduleInstance->module->title }}</p>
                                <p class="text-xs text-gray-500">{{ $repeatAssessment->moduleInstance->module->code }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Original Grade</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $repeatAssessment->studentAssessment->grade ?? 'N/A' }}%
                                    @if($repeatAssessment->studentAssessment->grade && $repeatAssessment->studentAssessment->grade < 40)
                                        <span class="text-red-600 text-xs">(Failed)</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Due Date</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $repeatAssessment->repeat_due_date ? $repeatAssessment->repeat_due_date->format('M j, Y') : 'Not set' }}
                                    @if($repeatAssessment->repeat_due_date && $repeatAssessment->repeat_due_date->isPast())
                                        <span class="text-red-600 text-xs">(Overdue)</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Max Grade</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $repeatAssessment->cap_grade ?? 40 }}%</p>
                            </div>
                        </div>
                        
                        @if($repeatAssessment->reason)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-500">Reason for Repeat</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $repeatAssessment->reason }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Notes and Comments -->
                    @if($repeatAssessment->staff_notes || $repeatAssessment->student_response)
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes & Comments</h3>
                            
                            @if($repeatAssessment->staff_notes)
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-500">Staff Notes</label>
                                    <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $repeatAssessment->staff_notes }}</p>
                                </div>
                            @endif
                            
                            @if($repeatAssessment->student_response)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Student Response</label>
                                    <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $repeatAssessment->student_response }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Actions & Timeline Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            
                            @if(!$repeatAssessment->payment_status === 'paid')
                                <button onclick="openPaymentModal()" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <i data-lucide="credit-card" class="w-4 h-4 inline mr-2"></i>
                                    Record Payment
                                </button>
                            @endif
                            
                            @if(!$repeatAssessment->student_notified)
                                <form action="{{ route('repeat-assessments.send-notification', $repeatAssessment) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <i data-lucide="mail" class="w-4 h-4 inline mr-2"></i>
                                        Send Notification
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('repeat-assessments.edit', $repeatAssessment) }}" class="w-full block bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                                <i data-lucide="edit" class="w-4 h-4 inline mr-2"></i>
                                Edit Details
                            </a>
                        </div>
                    </div>

                    <!-- Workflow Timeline -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Workflow Progress</h3>
                        <div class="space-y-4">
                            
                            <!-- Created -->
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">Assessment Failed</p>
                                    <p class="text-xs text-gray-500">{{ $repeatAssessment->created_at->format('M j, Y g:i A') }}</p>
                                </div>
                            </div>

                            <!-- Payment -->
                            <div class="flex items-center">
                                <div class="w-8 h-8 {{ $repeatAssessment->payment_status === 'paid' ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                    <i data-lucide="{{ $repeatAssessment->payment_status === 'paid' ? 'check' : 'clock' }}" class="w-4 h-4 {{ $repeatAssessment->payment_status === 'paid' ? 'text-green-600' : 'text-gray-400' }}"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium {{ $repeatAssessment->payment_status === 'paid' ? 'text-gray-900' : 'text-gray-500' }}">Payment Received</p>
                                    @if($repeatAssessment->payment_date)
                                        <p class="text-xs text-gray-500">{{ $repeatAssessment->payment_date->format('M j, Y g:i A') }}</p>
                                    @else
                                        <p class="text-xs text-gray-400">Pending</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Approval -->
                            <div class="flex items-center">
                                <div class="w-8 h-8 {{ $repeatAssessment->status === 'approved' ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                    <i data-lucide="{{ $repeatAssessment->status === 'approved' ? 'check' : 'clock' }}" class="w-4 h-4 {{ $repeatAssessment->status === 'approved' ? 'text-green-600' : 'text-gray-400' }}"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium {{ $repeatAssessment->status === 'approved' ? 'text-gray-900' : 'text-gray-500' }}">Approved</p>
                                    @if($repeatAssessment->approved_at)
                                        <p class="text-xs text-gray-500">{{ $repeatAssessment->approved_at->format('M j, Y g:i A') }}</p>
                                    @else
                                        <p class="text-xs text-gray-400">Pending</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Moodle Setup -->
                            <div class="flex items-center">
                                <div class="w-8 h-8 {{ $repeatAssessment->moodle_setup_completed ? 'bg-green-100' : 'bg-gray-100' }} rounded-full flex items-center justify-center">
                                    <i data-lucide="{{ $repeatAssessment->moodle_setup_completed ? 'check' : 'clock' }}" class="w-4 h-4 {{ $repeatAssessment->moodle_setup_completed ? 'text-green-600' : 'text-gray-400' }}"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium {{ $repeatAssessment->moodle_setup_completed ? 'text-gray-900' : 'text-gray-500' }}">Moodle Setup</p>
                                    @if($repeatAssessment->moodle_setup_date)
                                        <p class="text-xs text-gray-500">{{ $repeatAssessment->moodle_setup_date->format('M j, Y g:i A') }}</p>
                                    @else
                                        <p class="text-xs text-gray-400">Pending</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    @if($repeatAssessment->assigned_staff || $repeatAssessment->last_contact_date)
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                            
                            @if($repeatAssessment->assigned_staff)
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-500">Assigned Staff</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $repeatAssessment->assigned_staff }}</p>
                                </div>
                            @endif
                            
                            @if($repeatAssessment->last_contact_date)
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-500">Last Contact</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $repeatAssessment->last_contact_date->format('M j, Y') }}</p>
                                </div>
                            @endif
                            
                            @if($repeatAssessment->notification_method)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Preferred Contact Method</label>
                                    <p class="mt-1 text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $repeatAssessment->notification_method) }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Record Payment</h3>
                    <form action="{{ route('repeat-assessments.record-payment', $repeatAssessment) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="payment_amount" class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" name="payment_amount" id="payment_amount" step="0.01" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ $repeatAssessment->payment_amount ?? 150.00 }}" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" id="payment_method" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Select method...</option>
                                <option value="online">Online Payment</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="payment_notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea name="payment_notes" id="payment_notes" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closePaymentModal()" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                                Record Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPaymentModal() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePaymentModal();
            }
        });
    </script>
</x-app-layout>