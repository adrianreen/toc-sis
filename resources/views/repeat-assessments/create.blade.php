{{-- resources/views/repeat-assessments/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('repeat-assessments.index') }}" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Create Repeat Assessment
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Add a new repeat assessment for a failed student assessment
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('repeat-assessments.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Student & Assessment Selection -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Student & Assessment Selection</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Student Selection -->
                        <div>
                            <label for="student_id" class="block text-sm font-medium text-gray-700">Student</label>
                            <select name="student_id" id="student_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    required onchange="loadStudentAssessments()">
                                <option value="">Select a student...</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->full_name }} ({{ $student->student_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Grade Record Selection -->
                        <div>
                            <label for="student_grade_record_id" class="block text-sm font-medium text-gray-700">Failed Grade Record</label>
                            <select name="student_grade_record_id" id="student_grade_record_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    required disabled>
                                <option value="">Select student first...</option>
                            </select>
                            @error('student_grade_record_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Grade Record Details Display -->
                    <div id="gradeRecordDetails" class="mt-4 p-4 bg-gray-50 rounded-lg hidden">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Grade Record Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Module:</span>
                                <span id="moduleTitle" class="font-medium ml-1"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Component:</span>
                                <span id="componentName" class="font-medium ml-1"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Grade:</span>
                                <span id="gradePercentage" class="font-medium ml-1"></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Attempts:</span>
                                <span id="attempts" class="font-medium ml-1"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Repeat Assessment Details -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Repeat Assessment Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Reason -->
                        <div class="md:col-span-2">
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Repeat</label>
                            <textarea name="reason" id="reason" rows="3" 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                      placeholder="Explain why this repeat assessment is required...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="repeat_due_date" class="block text-sm font-medium text-gray-700">Repeat Due Date</label>
                            <input type="date" name="repeat_due_date" id="repeat_due_date" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('repeat_due_date', now()->addWeeks(4)->format('Y-m-d')) }}">
                            @error('repeat_due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Grade Cap -->
                        <div>
                            <label for="cap_grade" class="block text-sm font-medium text-gray-700">Maximum Grade (%)</label>
                            <input type="number" name="cap_grade" id="cap_grade" min="0" max="100" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('cap_grade', 40) }}">
                            @error('cap_grade')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Typically 40% for repeat assessments</p>
                        </div>
                    </div>
                </div>

                <!-- Payment & Administrative Details -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment & Administrative Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Payment Amount -->
                        <div>
                            <label for="payment_amount" class="block text-sm font-medium text-gray-700">Payment Amount (€)</label>
                            <input type="number" name="payment_amount" id="payment_amount" step="0.01" min="0" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('payment_amount', 150.00) }}">
                            @error('payment_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Status -->
                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700">Payment Status</label>
                            <select name="payment_status" id="payment_status" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending" {{ old('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="waived" {{ old('payment_status') == 'waived' ? 'selected' : '' }}>Waived</option>
                            </select>
                            @error('payment_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" id="priority" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Assigned Staff -->
                        <div>
                            <label for="assigned_staff" class="block text-sm font-medium text-gray-700">Assigned Staff (Optional)</label>
                            <input type="text" name="assigned_staff" id="assigned_staff" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                   value="{{ old('assigned_staff') }}" placeholder="Enter staff member name">
                            @error('assigned_staff')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Staff Notes -->
                    <div class="mt-6">
                        <label for="staff_notes" class="block text-sm font-medium text-gray-700">Staff Notes (Optional)</label>
                        <textarea name="staff_notes" id="staff_notes" rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                  placeholder="Add any relevant notes for this repeat assessment...">{{ old('staff_notes') }}</textarea>
                        @error('staff_notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notification Options -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Options</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Send Notification -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" name="send_notification" id="send_notification" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       {{ old('send_notification', true) ? 'checked' : '' }}>
                                <label for="send_notification" class="ml-2 block text-sm text-gray-900">
                                    Send notification to student
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Student will be notified about the repeat assessment requirement</p>
                        </div>

                        <!-- Notification Method -->
                        <div>
                            <label for="notification_method" class="block text-sm font-medium text-gray-700">Notification Method</label>
                            <select name="notification_method" id="notification_method" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="email" {{ old('notification_method') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="post" {{ old('notification_method') == 'post' ? 'selected' : '' }}>Post</option>
                                <option value="phone" {{ old('notification_method') == 'phone' ? 'selected' : '' }}>Phone</option>
                                <option value="in_person" {{ old('notification_method') == 'in_person' ? 'selected' : '' }}>In Person</option>
                            </select>
                            @error('notification_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('repeat-assessments.index') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>
                        Create Repeat Assessment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let gradeRecordData = {};

        async function loadStudentGradeRecords() {
            const studentId = document.getElementById('student_id').value;
            const gradeRecordSelect = document.getElementById('student_grade_record_id');
            const detailsDiv = document.getElementById('gradeRecordDetails');
            
            // Reset
            gradeRecordSelect.innerHTML = '<option value="">Loading...</option>';
            gradeRecordSelect.disabled = true;
            detailsDiv.classList.add('hidden');
            
            if (!studentId) {
                gradeRecordSelect.innerHTML = '<option value="">Select student first...</option>';
                return;
            }
            
            try {
                const response = await fetch(`/api/students/${studentId}/failed-assessments`);
                const data = await response.json();
                
                gradeRecordSelect.innerHTML = '<option value="">Select failed grade record...</option>';
                
                if (data.assessments && data.assessments.length > 0) {
                    data.assessments.forEach(assessment => {
                        gradeRecordData[assessment.module_instance_id] = assessment;
                        const option = document.createElement('option');
                        option.value = assessment.module_instance_id;
                        option.textContent = `${assessment.module_title} - ${assessment.failed_components}/${assessment.total_components} failed (${assessment.lowest_grade}%)`;
                        gradeRecordSelect.appendChild(option);
                    });
                    gradeRecordSelect.disabled = false;
                } else {
                    gradeRecordSelect.innerHTML = '<option value="">No failed grade records found</option>';
                }
            } catch (error) {
                console.error('Error loading grade records:', error);
                gradeRecordSelect.innerHTML = '<option value="">Error loading grade records</option>';
            }
        }

        document.getElementById('student_grade_record_id').addEventListener('change', function() {
            const moduleInstanceId = this.value;
            const detailsDiv = document.getElementById('gradeRecordDetails');
            
            if (moduleInstanceId && gradeRecordData[moduleInstanceId]) {
                const gradeRecord = gradeRecordData[moduleInstanceId];
                
                document.getElementById('moduleTitle').textContent = gradeRecord.module_title;
                document.getElementById('componentName').textContent = `${gradeRecord.failed_components} components failed`;
                document.getElementById('gradePercentage').textContent = `${gradeRecord.lowest_grade}%`;
                document.getElementById('attempts').textContent = 'Multiple';
                
                // Auto-populate reason if available
                const reasonField = document.getElementById('reason');
                if (!reasonField.value) {
                    reasonField.value = `Failed grade record - Lowest Grade: ${gradeRecord.lowest_grade}%`;
                }
                
                detailsDiv.classList.remove('hidden');
            } else {
                detailsDiv.classList.add('hidden');
            }
        });

        // Load grade records if student is pre-selected (e.g., from old input)
        if (document.getElementById('student_id').value) {
            loadStudentGradeRecords();
        }

        // Add event listener for student selection change
        document.getElementById('student_id').addEventListener('change', loadStudentGradeRecords);
    </script>
</x-app-layout>