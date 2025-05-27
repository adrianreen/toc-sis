{{-- resources/views/assessments/grade.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Grade Assessment
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $studentAssessment->assessmentComponent->name }} - 
                    {{ $studentAssessment->studentModuleEnrolment->student->full_name }}
                </p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('assessments.module-instance', $studentAssessment->studentModuleEnrolment->moduleInstance) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Module
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Student & Assessment Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Student Information -->
                        <div>
                            <h3 class="text-lg font-semibold mb-3">Student Information</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Name:</span>
                                    <span class="ml-2 text-sm text-gray-900">{{ $studentAssessment->studentModuleEnrolment->student->full_name }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Student Number:</span>
                                    <span class="ml-2 text-sm text-gray-900">{{ $studentAssessment->studentModuleEnrolment->student->student_number }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Email:</span>
                                    <span class="ml-2 text-sm text-gray-900">{{ $studentAssessment->studentModuleEnrolment->student->email }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Enrolment Status:</span>
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($studentAssessment->studentModuleEnrolment->status === 'active') bg-green-100 text-green-800
                                        @elseif($studentAssessment->studentModuleEnrolment->status === 'completed') bg-blue-100 text-blue-800
                                        @elseif($studentAssessment->studentModuleEnrolment->status === 'failed') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($studentAssessment->studentModuleEnrolment->status) }}
                                    </span>
                                </div>
                                @if($studentAssessment->studentModuleEnrolment->attempt_number > 1)
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">Attempt:</span>
                                        <span class="ml-2 text-sm text-yellow-600 font-medium">{{ $studentAssessment->studentModuleEnrolment->attempt_number }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Assessment Information -->
                        <div>
                            <h3 class="text-lg font-semibold mb-3">Assessment Information</h3>
                            <div class="space-y-2">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Module:</span>
                                    <span class="ml-2 text-sm text-gray-900">{{ $studentAssessment->studentModuleEnrolment->moduleInstance->module->code }} - {{ $studentAssessment->studentModuleEnrolment->moduleInstance->module->title }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Assessment:</span>
                                    <span class="ml-2 text-sm text-gray-900">{{ $studentAssessment->assessmentComponent->name }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Type:</span>
                                    <span class="ml-2 text-sm text-gray-900">{{ ucfirst($studentAssessment->assessmentComponent->type) }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Weight:</span>
                                    <span class="ml-2 text-sm font-semibold text-blue-600">{{ $studentAssessment->assessmentComponent->weight }}%</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Due Date:</span>
                                    <span class="ml-2 text-sm {{ $studentAssessment->due_date->isPast() && $studentAssessment->status === 'pending' ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                        {{ $studentAssessment->due_date->format('d M Y') }}
                                        @if($studentAssessment->due_date->isPast() && $studentAssessment->status === 'pending')
                                            (Overdue)
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Status:</span>
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($studentAssessment->status === 'passed') bg-green-100 text-green-800
                                        @elseif($studentAssessment->status === 'failed') bg-red-100 text-red-800
                                        @elseif($studentAssessment->status === 'submitted') bg-orange-100 text-orange-800
                                        @elseif($studentAssessment->status === 'graded') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($studentAssessment->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Grade Display (if exists) -->
            @if($studentAssessment->grade !== null)
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Current Grade:</strong> 
                                <span class="text-lg font-bold {{ $studentAssessment->grade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($studentAssessment->grade, 1) }}%
                                </span>
                                ({{ $studentAssessment->grade >= 40 ? 'PASS' : 'FAIL' }})
                                @if($studentAssessment->graded_date)
                                    - Graded {{ $studentAssessment->graded_date->format('d M Y') }}
                                    @if($studentAssessment->gradedBy)
                                        by {{ $studentAssessment->gradedBy->name }}
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Grading Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">
                        {{ $studentAssessment->grade !== null ? 'Update Grade' : 'Enter Grade' }}
                    </h3>

                    <form method="POST" action="{{ route('assessments.store-grade', $studentAssessment) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Grade Input -->
                            <div>
                                <label for="grade" class="block text-sm font-medium text-gray-700">
                                    Grade (%) *
                                </label>
                                <input type="number" 
                                       name="grade" 
                                       id="grade" 
                                       min="0" 
                                       max="100" 
                                       step="0.1"
                                       value="{{ old('grade', $studentAssessment->grade) }}" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-lg font-medium">
                                @error('grade')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                
                                <!-- Grade Helper -->
                                <div class="mt-2 text-sm">
                                    <div id="grade-indicator" class="font-medium text-gray-600">
                                        Enter grade to see pass/fail status
                                    </div>
                                    <div class="text-gray-500 mt-1">
                                        Pass: ≥40% | Fail: <40%
                                    </div>
                                </div>
                            </div>

                            <!-- Submission Date -->
                            <div>
                                <label for="submission_date" class="block text-sm font-medium text-gray-700">
                                    Submission Date
                                </label>
                                <input type="date" 
                                       name="submission_date" 
                                       id="submission_date" 
                                       value="{{ old('submission_date', $studentAssessment->submission_date?->format('Y-m-d') ?? ($studentAssessment->status === 'submitted' ? now()->format('Y-m-d') : '')) }}"
                                       max="{{ now()->format('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('submission_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Leave blank if not yet submitted</p>
                            </div>
                        </div>

                        <!-- Feedback -->
                        <div class="mt-6">
                            <label for="feedback" class="block text-sm font-medium text-gray-700">
                                Feedback for Student
                            </label>
                            <textarea name="feedback" 
                                      id="feedback" 
                                      rows="6" 
                                      placeholder="Provide constructive feedback to help the student understand their performance and areas for improvement..."
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('feedback', $studentAssessment->feedback) }}</textarea>
                            @error('feedback')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">This feedback will be visible to the student</p>
                        </div>

                        <!-- Grade Impact Preview -->
                        @php
                            $otherAssessments = $studentAssessment->studentModuleEnrolment->studentAssessments
                                ->where('id', '!=', $studentAssessment->id)
                                ->whereNotNull('grade');
                            $currentWeight = $otherAssessments->sum(function($assessment) {
                                return ($assessment->grade * $assessment->assessmentComponent->weight) / 100;
                            });
                            $totalWeight = $otherAssessments->sum('assessmentComponent.weight') + $studentAssessment->assessmentComponent->weight;
                        @endphp
                        
                        @if($otherAssessments->count() > 0)
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Impact on Final Grade</h4>
                                <div class="text-sm text-gray-600">
                                    <div>Other assessments contribute: <span class="font-medium">{{ number_format($currentWeight, 1) }}%</span></div>
                                    <div>This assessment weight: <span class="font-medium">{{ $studentAssessment->assessmentComponent->weight }}%</span></div>
                                    <div id="projected-final" class="mt-2 font-medium">
                                        Enter grade to see projected final grade
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="mt-6 flex justify-between">
                            <div>
                                @if($studentAssessment->status === 'pending' && !$studentAssessment->isSubmitted())
                                    <form action="{{ route('assessments.mark-submitted', $studentAssessment) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                onclick="return confirm('Mark this assessment as submitted? This will notify that the student has completed their work.')"
                                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                            Mark as Submitted
                                        </button>
                                    </form>
                                @endif
                            </div>
                            
                            <div class="space-x-2">
                                <a href="{{ route('assessments.module-instance', $studentAssessment->studentModuleEnrolment->moduleInstance) }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    {{ $studentAssessment->grade !== null ? 'Update Grade' : 'Save Grade' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Previous Feedback History -->
            @if($studentAssessment->grade !== null && $studentAssessment->feedback)
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Previous Feedback</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $studentAssessment->feedback }}</p>
                            @if($studentAssessment->graded_date)
                                <p class="text-xs text-gray-500 mt-2">
                                    Given on {{ $studentAssessment->graded_date->format('d M Y H:i') }}
                                    @if($studentAssessment->gradedBy)
                                        by {{ $studentAssessment->gradedBy->name }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Real-time grade feedback
        document.getElementById('grade').addEventListener('input', function() {
            const grade = parseFloat(this.value);
            const indicator = document.getElementById('grade-indicator');
            const projectedFinal = document.getElementById('projected-final');
            
            if (!isNaN(grade)) {
                // Update pass/fail indicator
                if (grade >= 40) {
                    indicator.innerHTML = '<span class="text-green-600 font-bold">PASS</span> (≥40%)';
                } else {
                    indicator.innerHTML = '<span class="text-red-600 font-bold">FAIL</span> (<40%)';
                }
                
                // Calculate projected final grade
                @if($otherAssessments->count() > 0)
                    const currentWeight = {{ $currentWeight }};
                    const thisWeight = {{ $studentAssessment->assessmentComponent->weight }};
                    const totalWeight = {{ $totalWeight }};
                    
                    const thisContribution = (grade * thisWeight) / 100;
                    const projectedGrade = currentWeight + thisContribution;
                    
                    if (projectedFinal) {
                        const finalPercentage = (projectedGrade / totalWeight) * 100;
                        projectedFinal.innerHTML = `Projected final grade: <span class="${finalPercentage >= 40 ? 'text-green-600' : 'text-red-600'} font-bold">${finalPercentage.toFixed(1)}%</span>`;
                    }
                @endif
            } else {
                indicator.textContent = 'Enter grade to see pass/fail status';
                if (projectedFinal) {
                    projectedFinal.textContent = 'Enter grade to see projected final grade';
                }
            }
        });
        
        // Trigger on page load if grade exists
        if (document.getElementById('grade').value) {
            document.getElementById('grade').dispatchEvent(new Event('input'));
        }
    </script>
</x-app-layout>