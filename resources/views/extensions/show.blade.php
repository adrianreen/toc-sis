{{-- resources/views/extensions/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Extension Request Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Extension Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Student Information</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="text-sm text-gray-900">{{ $extension->student->full_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Student Number</dt>
                                    <dd class="text-sm text-gray-900">{{ $extension->student->student_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900">{{ $extension->student->email }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-4">Assessment Information</h3>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Module</dt>
                                    <dd class="text-sm text-gray-900">{{ $extension->studentGradeRecord->moduleInstance->module->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Module Code</dt>
                                    <dd class="text-sm text-gray-900">{{ $extension->studentGradeRecord->moduleInstance->module->module_code }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Assessment Component</dt>
                                    <dd class="text-sm text-gray-900">{{ $extension->studentGradeRecord->assessment_component_name }}</dd>
                                </div>
                                @if($extension->studentGradeRecord->moduleInstance->tutor)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tutor</dt>
                                    <dd class="text-sm text-gray-900">{{ $extension->studentGradeRecord->moduleInstance->tutor->name }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Extension Request Details -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Extension Request Details</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Original Due Date</dt>
                                <dd class="text-sm text-gray-900">{{ $extension->original_due_date->format('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Requested New Due Date</dt>
                                <dd class="text-sm text-gray-900">{{ $extension->new_due_date->format('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Extension Period</dt>
                                <dd class="text-sm text-gray-900">{{ $extension->days_extended }} days</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($extension->status === 'approved') bg-green-100 text-green-800
                                        @elseif($extension->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($extension->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Reason</dt>
                                <dd class="text-sm text-gray-900">{{ $extension->reason }}</dd>
                            </div>
                            @if($extension->admin_notes)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Admin Notes</dt>
                                <dd class="text-sm text-gray-900">{{ $extension->admin_notes }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Request History -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Request History</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Requested By</dt>
                                <dd class="text-sm text-gray-900">
                                    {{ $extension->requestedBy->name }} on {{ $extension->created_at->format('d M Y H:i') }}
                                </dd>
                            </div>
                            @if($extension->approved_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    {{ $extension->status === 'approved' ? 'Approved' : 'Processed' }} By
                                </dt>
                                <dd class="text-sm text-gray-900">
                                    {{ $extension->approvedBy->name }} on {{ $extension->approved_at->format('d M Y H:i') }}
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Actions -->
                    @if($extension->status === 'pending' && (auth()->user()->role === 'manager' || auth()->user()->role === 'student_services'))
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold mb-4">Actions</h3>
                        <div class="flex space-x-2">
                            <form method="POST" action="{{ route('extensions.approve', $extension) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                        onclick="return confirm('Are you sure you want to approve this extension?')">
                                    Approve Extension
                                </button>
                            </form>

                            <button type="button" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                    onclick="document.getElementById('rejectModal').style.display='block'">
                                Reject Extension
                            </button>
                        </div>
                    </div>

                    <!-- Reject Modal -->
                    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" style="display: none;">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <h3 class="text-lg font-semibold mb-4">Reject Extension Request</h3>
                            <form method="POST" action="{{ route('extensions.reject', $extension) }}">
                                @csrf
                                @method('PATCH')
                                <div class="mb-4">
                                    <label for="admin_notes" class="block text-sm font-medium text-gray-700">
                                        Reason for Rejection *
                                    </label>
                                    <textarea name="admin_notes" id="admin_notes" rows="3" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="Please provide a reason for rejection..."></textarea>
                                </div>
                                <div class="flex justify-end space-x-2">
                                    <button type="button" onclick="document.getElementById('rejectModal').style.display='none'"
                                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Cancel
                                    </button>
                                    <button type="submit" 
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Reject Extension
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Back Button -->
                    <div class="border-t pt-6">
                        <a href="{{ route('extensions.index') }}" 
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Back to Extensions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>