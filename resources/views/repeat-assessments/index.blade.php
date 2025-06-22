{{-- resources/views/repeat-assessments/index.blade.php --}}
<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Header Section --}}
            <div class="mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Repeat Assessments</h1>
                        <p class="mt-2 text-gray-600">Manage repeat assessment requirements and tracking</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('repeat-assessments.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-toc-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Repeat Assessment
                        </a>
                        
                        {{-- Auto-populate button --}}
                        <button type="button" 
                                onclick="showAutoPopulateModal()"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Auto-Populate
                        </button>
                    </div>
                </div>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </x-card>

                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Payment</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_payment'] }}</p>
                        </div>
                    </div>
                </x-card>

                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Overdue</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['overdue'] }}</p>
                        </div>
                    </div>
                </x-card>

                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M15 17h5l-5 5v-5zM9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Due Soon</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['due_soon'] }}</p>
                        </div>
                    </div>
                </x-card>

                <x-card class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['active'] }}</p>
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- Filter and Search Section --}}
            <x-card class="mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('repeat-assessments.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            {{-- Search --}}
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" 
                                       name="search" 
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="Student name, number, email..."
                                       class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                            </div>

                            {{-- Workflow Stage --}}
                            <div>
                                <label for="workflow_stage" class="block text-sm font-medium text-gray-700 mb-1">Workflow Stage</label>
                                <select name="workflow_stage" id="workflow_stage" class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                                    <option value="">All Stages</option>
                                    @foreach($workflowStages as $value => $label)
                                        <option value="{{ $value }}" {{ request('workflow_stage') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Payment Status --}}
                            <div>
                                <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                                <select name="payment_status" id="payment_status" class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                                    <option value="">All Statuses</option>
                                    @foreach($paymentStatuses as $value => $label)
                                        <option value="{{ $value }}" {{ request('payment_status') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Priority Level --}}
                            <div>
                                <label for="priority_level" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select name="priority_level" id="priority_level" class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                                    <option value="">All Priorities</option>
                                    @foreach($priorities as $value => $label)
                                        <option value="{{ $value }}" {{ request('priority_level') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Assigned To --}}
                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                                <select name="assigned_to" id="assigned_to" class="w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                                    <option value="">All Staff</option>
                                    @foreach($staff as $staffMember)
                                        <option value="{{ $staffMember->id }}" {{ request('assigned_to') == $staffMember->id ? 'selected' : '' }}>
                                            {{ $staffMember->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Quick Filters --}}
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }} 
                                           class="rounded border-gray-300 text-toc-600 focus:border-toc-500 focus:ring-toc-500">
                                    <span class="ml-2 text-sm text-gray-700">Overdue</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" name="due_soon" value="7" {{ request('due_soon') ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-toc-600 focus:border-toc-500 focus:ring-toc-500">
                                    <span class="ml-2 text-sm text-gray-700">Due Soon</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-toc-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Filter
                            </button>
                            
                            @if(request()->hasAny(['search', 'workflow_stage', 'payment_status', 'priority_level', 'assigned_to', 'overdue', 'due_soon']))
                                <a href="{{ route('repeat-assessments.index') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                    Clear Filters
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </x-card>

            {{-- Bulk Actions --}}
            @if($repeats->count() > 0)
            <x-card class="mb-6">
                <div class="p-6">
                    <form id="bulk-action-form" method="POST" action="{{ route('repeat-assessments.bulk-action') }}">
                        @csrf
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-toc-600 focus:border-toc-500 focus:ring-toc-500">
                                    <span class="ml-2 text-sm text-gray-700">Select All</span>
                                </label>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <select name="action" required class="rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                                    <option value="">Select Action</option>
                                    <option value="assign">Assign to Staff</option>
                                    <option value="update_priority">Update Priority</option>
                                    <option value="send_reminders">Send Reminders</option>
                                </select>

                                <select name="assigned_to" class="rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500" style="display:none;" id="assign-select">
                                    <option value="">Select Staff Member</option>
                                    @foreach($staff as $staffMember)
                                        <option value="{{ $staffMember->id }}">{{ $staffMember->name }}</option>
                                    @endforeach
                                </select>

                                <select name="priority_level" class="rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500" style="display:none;" id="priority-select">
                                    <option value="">Select Priority</option>
                                    @foreach($priorities as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>

                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-toc-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors">
                                    Apply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </x-card>
            @endif

            {{-- Repeat Assessments Table --}}
            <x-card>
                <div class="overflow-x-auto">
                    @if($repeats->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input type="checkbox" id="select-all-header" class="rounded border-gray-300 text-toc-600 focus:border-toc-500 focus:ring-toc-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assessment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow Stage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($repeats as $repeat)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <input type="checkbox" name="repeat_assessment_ids[]" value="{{ $repeat->id }}" 
                                                   class="repeat-checkbox rounded border-gray-300 text-toc-600 focus:border-toc-500 focus:ring-toc-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <a href="{{ route('students.show', $repeat->student) }}" class="hover:text-toc-600">
                                                            {{ $repeat->student->full_name }}
                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500">{{ $repeat->student->student_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $repeat->studentGradeRecord->assessment_component_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $repeat->moduleInstance->module->title }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-status-badge :status="$repeat->workflow_stage" :type="'workflow'"/>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-status-badge :status="$repeat->payment_status" :type="'payment'"/>
                                            @if($repeat->payment_amount)
                                                <div class="text-xs text-gray-500 mt-1">€{{ number_format($repeat->payment_amount, 2) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-status-badge :status="$repeat->priority_level" :type="'priority'"/>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $repeat->repeat_due_date->format('M j, Y') }}
                                            @if($repeat->isOverdue())
                                                <span class="text-red-600 text-xs">(Overdue)</span>
                                            @elseif($repeat->isDueSoon())
                                                <span class="text-orange-600 text-xs">(Due Soon)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $repeat->assignedTo->name ?? 'Unassigned' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('repeat-assessments.show', $repeat) }}" 
                                                   class="text-toc-600 hover:text-toc-900">View</a>
                                                <a href="{{ route('repeat-assessments.edit', $repeat) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No repeat assessments</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a repeat assessment.</p>
                            <div class="mt-6">
                                <a href="{{ route('repeat-assessments.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-toc-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-toc-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Create Repeat Assessment
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Pagination --}}
                @if($repeats->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $repeats->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    {{-- Auto-populate Modal --}}
    <div id="auto-populate-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Auto-Populate Repeat Assessments</h3>
                <form id="auto-populate-form" method="POST" action="{{ route('repeat-assessments.auto-populate') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="deadline_days" class="block text-sm font-medium text-gray-700">Deadline (Days from now)</label>
                            <input type="number" name="deadline_days" id="deadline_days" value="30" min="1" max="365" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                        </div>
                        <div>
                            <label for="payment_amount" class="block text-sm font-medium text-gray-700">Payment Amount (€)</label>
                            <input type="number" name="payment_amount" id="payment_amount" value="50.00" step="0.01" min="0" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-toc-500 focus:ring-toc-500">
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" name="dry_run" id="dry_run" value="1" checked
                                   class="rounded border-gray-300 text-toc-600 focus:border-toc-500 focus:ring-toc-500">
                            <label for="dry_run" class="text-sm text-gray-700">Preview only (don't create)</label>
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-6 space-x-3">
                        <button type="button" onclick="hideAutoPopulateModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-toc-600 text-white rounded-lg hover:bg-toc-700 focus:outline-none focus:ring-2 focus:ring-toc-500">
                            Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Bulk actions functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const selectAllHeader = document.getElementById('select-all-header');
            const checkboxes = document.querySelectorAll('.repeat-checkbox');
            const actionSelect = document.querySelector('select[name="action"]');
            const assignSelect = document.getElementById('assign-select');
            const prioritySelect = document.getElementById('priority-select');

            // Select all functionality
            function updateSelectAll() {
                const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
                const indeterminate = checked > 0 && checked < checkboxes.length;
                
                if (selectAll) {
                    selectAll.checked = checked === checkboxes.length;
                    selectAll.indeterminate = indeterminate;
                }
                if (selectAllHeader) {
                    selectAllHeader.checked = checked === checkboxes.length;
                    selectAllHeader.indeterminate = indeterminate;
                }
            }

            [selectAll, selectAllHeader].forEach(sa => {
                if (sa) {
                    sa.addEventListener('change', function() {
                        checkboxes.forEach(cb => cb.checked = this.checked);
                        updateSelectAll();
                    });
                }
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectAll);
            });

            // Show/hide additional selects based on action
            if (actionSelect) {
                actionSelect.addEventListener('change', function() {
                    assignSelect.style.display = this.value === 'assign' ? 'block' : 'none';
                    prioritySelect.style.display = this.value === 'update_priority' ? 'block' : 'none';
                });
            }

            // Bulk action form submission
            const bulkForm = document.getElementById('bulk-action-form');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    const selectedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked);
                    if (selectedCheckboxes.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one repeat assessment.');
                    }

                    // Append selected IDs to form
                    selectedCheckboxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'repeat_assessment_ids[]';
                        input.value = cb.value;
                        this.appendChild(input);
                    });
                });
            }
        });

        // Auto-populate modal functions
        function showAutoPopulateModal() {
            document.getElementById('auto-populate-modal').classList.remove('hidden');
        }

        function hideAutoPopulateModal() {
            document.getElementById('auto-populate-modal').classList.add('hidden');
        }

        // Auto-populate form submission
        document.getElementById('auto-populate-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isDryRun = formData.get('dry_run');
            
            if (isDryRun) {
                // Preview mode - show AJAX results
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.count > 0) {
                        const message = `Found ${data.count} failed assessments that can be auto-populated:\\n\\n` +
                                      data.assessments.slice(0, 10).map(a => `• ${a.student_name} - ${a.assessment_name} (${a.module_name})`).join('\\n') +
                                      (data.assessments.length > 10 ? `\\n... and ${data.assessments.length - 10} more` : '');
                        
                        if (confirm(message + '\\n\\nProceed to create these repeat assessments?')) {
                            formData.delete('dry_run');
                            this.submit();
                        }
                    } else {
                        alert('No failed assessments found that need repeat assessment creation.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while previewing. Please try again.');
                });
            } else {
                this.submit();
            }
        });
    </script>
    @endpush
</x-app-layout>