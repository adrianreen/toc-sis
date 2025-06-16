{{-- resources/views/assessments/bulk-grade.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Bulk Grade: {{ $assessmentComponent->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $moduleInstance->instance_code }} - {{ $moduleInstance->module->title }}
                    <span class="text-blue-600 font-medium">({{ $assessmentComponent->weight }}% weighting)</span>
                </p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('assessments.module-instance', $moduleInstance) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Module
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Assessment Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Assessment</h3>
                            <p class="text-lg font-semibold">{{ $assessmentComponent->name }}</p>
                            <p class="text-sm text-gray-600">{{ ucfirst($assessmentComponent->type) }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Weight</h3>
                            <p class="text-lg font-semibold text-blue-600">{{ $assessmentComponent->weight }}%</p>
                            <p class="text-sm text-gray-600">of final grade</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Students</h3>
                            <p class="text-lg font-semibold">{{ $studentAssessments->total() }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $studentAssessments->where('grade', '!=', null)->count() }} already graded
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Pass Mark</h3>
                            <p class="text-lg font-semibold text-green-600">40%</p>
                            <p class="text-sm text-gray-600">minimum to pass</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Bulk Grading Tips:</strong> 
                            Leave grade fields empty to skip grading. Use Tab/Enter/Arrow keys to navigate between fields. 
                            Click on a student name to open detailed grading for feedback.
                            Grades are auto-saved as you type (after 2 seconds of no changes).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bulk Grading Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Enter Grades</h3>
                        <div class="flex space-x-2">
                            <button onclick="fillAllGrades()" 
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Fill All (for testing)
                            </button>
                            <button onclick="clearAllGrades()" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm">
                                Clear All
                            </button>
                            <button onclick="undoLastChange()" 
                                    id="undo-btn"
                                    class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-1 px-3 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled
                                    title="Undo last grade change">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"/>
                                    <path d="M8 15l4-4 4 4"/>
                                </svg>
                                Undo
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('assessments.bulk-grade', [$moduleInstance, $assessmentComponent]) }}" id="bulkGradeForm">
                        @csrf

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Current Status
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Grade (%)
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quick Feedback
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($studentAssessments as $assessment)
                                        <tr class="hover:bg-gray-50" data-assessment-id="{{ $assessment->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <a href="{{ route('assessments.grade', $assessment) }}" 
                                                           target="_blank"
                                                           class="text-sm font-medium text-indigo-600 hover:text-indigo-900 hover:underline">
                                                            {{ $assessment->studentModuleEnrolment->student->full_name }}
                                                        </a>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $assessment->studentModuleEnrolment->student->student_number }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($assessment->grade !== null)
                                                    <div class="text-sm font-medium {{ $assessment->grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                        Current: {{ number_format($assessment->grade, 1) }}%
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $assessment->status === 'passed' ? 'Passed' : ($assessment->status === 'failed' ? 'Failed' : ucfirst($assessment->status)) }}
                                                    </div>
                                                @elseif($assessment->status === 'submitted')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                        Submitted
                                                    </span>
                                                    @if($assessment->submission_date)
                                                        <div class="text-xs text-gray-500">
                                                            {{ $assessment->submission_date->format('d M Y') }}
                                                        </div>
                                                    @endif
                                                @elseif($assessment->due_date->isPast())
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Overdue
                                                    </span>
                                                    <div class="text-xs text-gray-500">
                                                        Due {{ $assessment->due_date->format('d M Y') }}
                                                    </div>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Pending
                                                    </span>
                                                    <div class="text-xs text-gray-500">
                                                        Due {{ $assessment->due_date->format('d M Y') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <input type="hidden" 
                                                       name="grades[{{ $loop->index }}][assessment_id]" 
                                                       value="{{ $assessment->id }}">
                                                
                                                <div class="relative">
                                                    <input type="number" 
                                                           name="grades[{{ $loop->index }}][grade]" 
                                                           value="{{ old('grades.'.$loop->index.'.grade', $assessment->grade) }}"
                                                           min="0" 
                                                           max="100" 
                                                           step="0.1"
                                                           placeholder="0-100"
                                                           class="block w-24 mx-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-center font-medium grade-input"
                                                           data-assessment-id="{{ $assessment->id }}"
                                                           data-row-index="{{ $loop->index }}"
                                                           onchange="updateGradePreview(this)"
                                                           oninput="validateAndAutoSave(this)"
                                                           onkeydown="handleKeyNavigation(event)"
                                                           autocomplete="off">
                                                    
                                                    <!-- Grade Preview with Validation -->
                                                    <div class="mt-1 text-xs grade-preview" data-assessment-id="{{ $assessment->id }}">
                                                        @if($assessment->grade !== null)
                                                            <span class="{{ $assessment->grade >= 40 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                                                {{ $assessment->grade >= 40 ? 'PASS' : 'FAIL' }}
                                                            </span>
                                                        @else
                                                            <span class="text-gray-400">Not graded</span>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Validation Error -->
                                                    <div class="validation-error hidden mt-1 text-xs text-red-500" data-assessment-id="{{ $assessment->id }}"></div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <textarea name="grades[{{ $loop->index }}][feedback]" 
                                                          placeholder="Quick feedback (optional)"
                                                          rows="2"
                                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">{{ old('grades.'.$loop->index.'.feedback', $assessment->feedback) }}</textarea>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-xs text-gray-500">
                                                    Click name for details
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($studentAssessments->hasPages())
                            <div class="mt-4">
                                {{ $studentAssessments->links() }}
                            </div>
                        @endif

                        <!-- Enhanced Auto-save Status with Progress -->
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                <div class="flex items-center space-x-2">
                                    <span id="save-status" class="text-gray-500">Ready to save</span>
                                    <span id="save-indicator" class="hidden">
                                        <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-blue-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Saving...
                                    </span>
                                    <span id="progress-indicator" class="hidden text-blue-600 font-medium"></span>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div id="progress-bar-container" class="hidden mt-2 w-64">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-x-2">
                                <button type="button" 
                                        onclick="saveDraft()"
                                        id="save-draft-btn"
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="btn-text">Save Draft</span>
                                    <span class="btn-spinner hidden">
                                        <svg class="animate-spin h-4 w-4 text-white inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Saving...
                                    </span>
                                </button>
                                <button type="submit" 
                                        id="save-final-btn"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="btn-text">Save All Grades</span>
                                    <span class="btn-spinner hidden">
                                        <svg class="animate-spin h-4 w-4 text-white inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grade Statistics -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">Grading Statistics</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="stats-container">
                        @php
                            $gradedCount = $studentAssessments->whereNotNull('grade')->count();
                            $averageGrade = $studentAssessments->whereNotNull('grade')->avg('grade');
                            $passCount = $studentAssessments->where('grade', '>=', 40)->count();
                            $failCount = $studentAssessments->where('grade', '<', 40)->whereNotNull('grade')->count();
                        @endphp
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900" id="graded-count">{{ $gradedCount }}</div>
                            <div class="text-sm text-gray-500">Students Graded</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600" id="average-grade">
                                {{ $averageGrade ? number_format($averageGrade, 1) . '%' : '-' }}
                            </div>
                            <div class="text-sm text-gray-500">Average Grade</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600" id="pass-count">{{ $passCount }}</div>
                            <div class="text-sm text-gray-500">Passed (≥40%)</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600" id="fail-count">{{ $failCount }}</div>
                            <div class="text-sm text-gray-500">Failed (<40%)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let autoSaveTimeout;
        let isAutoSaving = false;
        
        // Undo functionality
        let undoStack = [];
        const maxUndoSteps = 10;

        // Enhanced grade validation and preview updates
        function validateAndAutoSave(input) {
            // Save current state for undo before making changes
            saveUndoState(input);
            
            validateGrade(input);
            updateGradePreview(input);
            scheduleAutoSave(input);
        }

        // Undo functionality
        function saveUndoState(input) {
            const assessmentId = input.dataset.assessmentId;
            const previousValue = input.getAttribute('data-previous-value') || '';
            const currentValue = input.value;
            
            // Only save if value actually changed
            if (previousValue !== currentValue) {
                undoStack.push({
                    assessmentId: assessmentId,
                    element: input,
                    previousValue: previousValue,
                    currentValue: currentValue,
                    timestamp: Date.now()
                });
                
                // Keep only last N changes
                if (undoStack.length > maxUndoSteps) {
                    undoStack.shift();
                }
                
                // Update the data attribute for next comparison
                input.setAttribute('data-previous-value', currentValue);
                
                // Enable undo button
                document.getElementById('undo-btn').disabled = false;
                updateUndoButtonTitle();
            }
        }

        function undoLastChange() {
            if (undoStack.length === 0) return;
            
            const lastChange = undoStack.pop();
            const input = lastChange.element;
            
            // Restore previous value
            input.value = lastChange.previousValue;
            input.setAttribute('data-previous-value', lastChange.previousValue);
            
            // Update UI
            validateGrade(input);
            updateGradePreview(input);
            updateStatistics();
            
            // Disable undo button if no more changes
            if (undoStack.length === 0) {
                document.getElementById('undo-btn').disabled = true;
            }
            
            updateUndoButtonTitle();
            
            // Show feedback
            showSuccessStatus('Change undone');
            setTimeout(resetStatus, 2000);
        }

        function updateUndoButtonTitle() {
            const undoBtn = document.getElementById('undo-btn');
            if (undoStack.length > 0) {
                const lastChange = undoStack[undoStack.length - 1];
                undoBtn.title = `Undo: ${lastChange.previousValue} → ${lastChange.currentValue}`;
            } else {
                undoBtn.title = 'No changes to undo';
            }
        }

        function validateGrade(input) {
            const value = input.value.trim();
            const assessmentId = input.dataset.assessmentId;
            const errorDiv = document.querySelector(`.validation-error[data-assessment-id="${assessmentId}"]`);
            
            // Clear previous validation state
            input.classList.remove('border-red-500', 'border-green-500');
            errorDiv.classList.add('hidden');
            errorDiv.textContent = '';
            
            if (value === '') {
                return true; // Empty is valid (not graded)
            }
            
            const grade = parseFloat(value);
            
            // Validation checks
            if (isNaN(grade)) {
                showValidationError(input, errorDiv, 'Grade must be a number');
                return false;
            }
            
            if (grade < 0) {
                showValidationError(input, errorDiv, 'Grade cannot be negative');
                return false;
            }
            
            if (grade > 100) {
                showValidationError(input, errorDiv, 'Grade cannot exceed 100%');
                return false;
            }
            
            // Valid grade
            input.classList.add('border-green-500');
            return true;
        }

        function showValidationError(input, errorDiv, message) {
            input.classList.add('border-red-500');
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
        }

        function updateGradePreview(input) {
            const grade = parseFloat(input.value);
            const assessmentId = input.dataset.assessmentId;
            const preview = document.querySelector(`.grade-preview[data-assessment-id="${assessmentId}"]`);
            
            if (!isNaN(grade) && grade >= 0 && grade <= 100) {
                if (grade >= 40) {
                    preview.innerHTML = '<span class="text-green-600 font-medium">PASS</span>';
                } else {
                    preview.innerHTML = '<span class="text-red-600 font-medium">FAIL</span>';
                }
            } else if (input.value === '') {
                preview.innerHTML = '<span class="text-gray-400">Not graded</span>';
            } else {
                preview.innerHTML = '<span class="text-red-500">Invalid</span>';
            }
            
            updateStatistics();
        }

        // Auto-save functionality
        function scheduleAutoSave(input) {
            if (autoSaveTimeout) {
                clearTimeout(autoSaveTimeout);
            }
            
            autoSaveTimeout = setTimeout(() => {
                if (!isAutoSaving) {
                    saveDraft();
                }
            }, 2000); // Auto-save after 2 seconds of no changes
        }

        // Enhanced save draft function with progress indicators
        async function saveDraft() {
            if (isAutoSaving) return;
            
            isAutoSaving = true;
            setButtonLoading('save-draft-btn', true);
            showProgress('Saving draft...', 0);

            try {
                const formData = new FormData(document.getElementById('bulkGradeForm'));
                formData.append('_draft', '1');
                
                // Simulate progress for user feedback
                showProgress('Validating grades...', 25);
                
                const response = await fetch('{{ route("assessments.bulk-grade", [$moduleInstance, $assessmentComponent]) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                showProgress('Processing data...', 75);

                if (response.ok) {
                    showProgress('Draft saved successfully!', 100);
                    showSuccessStatus('Draft saved');
                    
                    setTimeout(() => {
                        hideProgress();
                        resetStatus();
                    }, 2000);
                } else {
                    throw new Error('Save failed');
                }
            } catch (error) {
                showErrorStatus('Save failed - please try again');
                hideProgress();
                console.error('Auto-save error:', error);
            } finally {
                isAutoSaving = false;
                setButtonLoading('save-draft-btn', false);
            }
        }

        // Progress indicator functions
        function showProgress(message, percentage) {
            document.getElementById('progress-indicator').textContent = message;
            document.getElementById('progress-indicator').classList.remove('hidden');
            document.getElementById('progress-bar-container').classList.remove('hidden');
            document.getElementById('progress-bar').style.width = percentage + '%';
        }

        function hideProgress() {
            document.getElementById('progress-indicator').classList.add('hidden');
            document.getElementById('progress-bar-container').classList.add('hidden');
            document.getElementById('progress-bar').style.width = '0%';
        }

        function setButtonLoading(buttonId, isLoading) {
            const button = document.getElementById(buttonId);
            const btnText = button.querySelector('.btn-text');
            const btnSpinner = button.querySelector('.btn-spinner');
            
            if (isLoading) {
                button.disabled = true;
                btnText.classList.add('hidden');
                btnSpinner.classList.remove('hidden');
            } else {
                button.disabled = false;
                btnText.classList.remove('hidden');
                btnSpinner.classList.add('hidden');
            }
        }

        function showSuccessStatus(message) {
            const statusEl = document.getElementById('save-status');
            statusEl.textContent = message;
            statusEl.className = 'text-green-600 font-medium';
        }

        function showErrorStatus(message) {
            const statusEl = document.getElementById('save-status');
            statusEl.textContent = message;
            statusEl.className = 'text-red-600 font-medium';
        }

        function resetStatus() {
            const statusEl = document.getElementById('save-status');
            statusEl.textContent = 'Ready to save';
            statusEl.className = 'text-gray-500';
        }

        // Quick fill functions
        function fillAllGrades() {
            if (!confirm('Fill all empty grade fields with random grades for testing?')) return;
            
            const gradeInputs = document.querySelectorAll('.grade-input');
            gradeInputs.forEach(input => {
                if (!input.value) {
                    // Generate random grade between 30-90 for testing
                    const randomGrade = Math.floor(Math.random() * 61) + 30;
                    input.value = randomGrade;
                    updateGradePreview(input);
                }
            });
        }

        function clearAllGrades() {
            if (!confirm('Clear all grade fields? This will remove all unsaved changes.')) return;
            
            const gradeInputs = document.querySelectorAll('.grade-input');
            gradeInputs.forEach(input => {
                input.value = '';
                updateGradePreview(input);
            });
        }

        // Enhanced keyboard navigation
        function handleKeyNavigation(e) {
            const inputs = Array.from(document.querySelectorAll('.grade-input'));
            const currentIndex = inputs.indexOf(e.target);
            
            switch(e.key) {
                case 'Enter':
                    e.preventDefault();
                    // Move to next input
                    const nextInput = inputs[currentIndex + 1];
                    if (nextInput) {
                        nextInput.focus();
                        nextInput.select();
                    } else {
                        // If last input, focus on save button
                        document.querySelector('button[type="submit"]').focus();
                    }
                    break;
                    
                case 'ArrowDown':
                    e.preventDefault();
                    const downInput = inputs[currentIndex + 1];
                    if (downInput) {
                        downInput.focus();
                        downInput.select();
                    }
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    const upInput = inputs[currentIndex - 1];
                    if (upInput) {
                        upInput.focus();
                        upInput.select();
                    }
                    break;
                    
                case 'Escape':
                    e.preventDefault();
                    e.target.blur();
                    break;
                    
                case 'Tab':
                    // Let browser handle Tab navigation naturally
                    break;
            }
        }

        // Update statistics in real-time
        function updateStatistics() {
            const gradeInputs = Array.from(document.querySelectorAll('.grade-input'));
            const grades = gradeInputs
                .map(input => parseFloat(input.value))
                .filter(grade => !isNaN(grade) && grade >= 0 && grade <= 100);
            
            const gradedCount = grades.length;
            const averageGrade = gradedCount > 0 ? grades.reduce((a, b) => a + b, 0) / gradedCount : 0;
            const passCount = grades.filter(grade => grade >= 40).length;
            const failCount = grades.filter(grade => grade < 40).length;
            
            document.getElementById('graded-count').textContent = gradedCount;
            document.getElementById('average-grade').textContent = gradedCount > 0 ? `${averageGrade.toFixed(1)}%` : '-';
            document.getElementById('pass-count').textContent = passCount;
            document.getElementById('fail-count').textContent = failCount;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all grade previews and undo states
            document.querySelectorAll('.grade-input').forEach(input => {
                updateGradePreview(input);
                // Set initial value for undo tracking
                input.setAttribute('data-previous-value', input.value || '');
            });
            
            // Enhanced keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.target.classList.contains('grade-input')) {
                    handleKeyNavigation(e);
                }
            });

            // Enhanced form submission with progress
            document.getElementById('bulkGradeForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate all grades before submission
                const gradeInputs = document.querySelectorAll('.grade-input');
                let hasErrors = false;
                
                gradeInputs.forEach(input => {
                    if (!validateGrade(input)) {
                        hasErrors = true;
                    }
                });
                
                if (hasErrors) {
                    showErrorStatus('Please fix validation errors before saving');
                    return;
                }
                
                // Show progress and submit
                setButtonLoading('save-final-btn', true);
                showProgress('Validating all grades...', 10);
                
                setTimeout(() => {
                    showProgress('Saving final grades...', 50);
                    setTimeout(() => {
                        showProgress('Finalizing submission...', 90);
                        setTimeout(() => {
                            this.submit();
                        }, 300);
                    }, 500);
                }, 300);
            });
        });
    </script>
</x-app-layout>