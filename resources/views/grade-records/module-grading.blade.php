{{-- Module Instance Grading Interface --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Grading: {{ $moduleInstance->module->title }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('grade-records.export', $moduleInstance) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                    Export Grades
                </a>
                <a href="{{ route('module-instances.show', $moduleInstance) }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                    Back to Module
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Module Overview --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-blue-50 border-b border-blue-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-blue-900">{{ $moduleInstance->module->title }}</h3>
                            <p class="text-blue-700 mt-1">
                                {{ $moduleInstance->module->module_code }} • 
                                {{ $moduleInstance->module->credit_value }} credits • 
                                {{ ucfirst($moduleInstance->delivery_style) }}
                                @if($moduleInstance->tutor)
                                    • Tutor: {{ $moduleInstance->tutor->name }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-blue-700">
                                <div>Students: {{ $studentGrades->count() }}</div>
                                <div>Components: {{ count($moduleInstance->module->assessment_strategy) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assessment Components Overview --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-green-50 border-b border-green-200">
                    <h3 class="text-lg font-semibold text-green-900">Assessment Components</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($moduleInstance->module->assessment_strategy as $component)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900">
                                    {{ $component['component_name'] }}
                                    @if($component['is_must_pass'])
                                        <span class="text-red-600 text-sm">*</span>
                                    @endif
                                </h4>
                                <div class="text-sm text-gray-600 mt-1">
                                    <div>Weighting: {{ $component['weighting'] }}%</div>
                                    <div>Pass Mark: {{ $component['component_pass_mark'] ?? 40 }}%</div>
                                    @if($component['is_must_pass'])
                                        <div class="text-red-600 font-medium">Must Pass Component</div>
                                    @endif
                                </div>
                                <div class="mt-3 flex space-x-2">
                                    <button onclick="toggleComponentVisibility('{{ $component['component_name'] }}', true)" 
                                            class="text-xs bg-green-100 hover:bg-green-200 text-green-800 px-2 py-1 rounded">
                                        Show All
                                    </button>
                                    <button onclick="toggleComponentVisibility('{{ $component['component_name'] }}', false)" 
                                            class="text-xs bg-red-100 hover:bg-red-200 text-red-800 px-2 py-1 rounded">
                                        Hide All
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Student Grades Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-purple-50 border-b border-purple-200">
                    <h3 class="text-lg font-semibold text-purple-900">Student Grades</h3>
                </div>
                <div class="p-6">
                    @if($studentGrades->count() > 0)
                        <form id="bulk-grading-form" method="POST" action="{{ route('grade-records.bulk-update', $moduleInstance) }}">
                            @csrf
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Student
                                            </th>
                                            @foreach($moduleInstance->module->assessment_strategy as $component)
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ $component['component_name'] }}
                                                    <br><span class="text-xs normal-case">({{ $component['weighting'] }}%)</span>
                                                </th>
                                            @endforeach
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Overall
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($studentGrades as $studentId => $grades)
                                            @php
                                                $student = $grades->first()->student;
                                                $totalWeightedScore = 0;
                                                $totalWeighting = 0;
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $student->full_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $student->student_number }}</div>
                                                </td>
                                                
                                                @foreach($moduleInstance->module->assessment_strategy as $component)
                                                    @php
                                                        $gradeRecord = $grades->get($component['component_name']);
                                                        if ($gradeRecord && $gradeRecord->grade !== null) {
                                                            $totalWeightedScore += $gradeRecord->percentage * ($component['weighting'] / 100);
                                                            $totalWeighting += $component['weighting'];
                                                        }
                                                    @endphp
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($gradeRecord)
                                                            <div class="space-y-2">
                                                                <input type="hidden" name="grades[{{ $gradeRecord->id }}][id]" value="{{ $gradeRecord->id }}">
                                                                <div class="flex space-x-2">
                                                                    <input type="number" 
                                                                           name="grades[{{ $gradeRecord->id }}][grade]" 
                                                                           value="{{ $gradeRecord->grade }}" 
                                                                           placeholder="Grade"
                                                                           min="0" 
                                                                           max="{{ $gradeRecord->max_grade ?? 100 }}"
                                                                           step="0.1"
                                                                           class="w-20 text-xs rounded border-gray-300">
                                                                    <span class="text-xs text-gray-500 self-center">/{{ $gradeRecord->max_grade ?? 100 }}</span>
                                                                </div>
                                                                @if($gradeRecord->percentage !== null)
                                                                    <div class="text-xs font-medium {{ $gradeRecord->percentage >= ($component['component_pass_mark'] ?? 40) ? 'text-green-600' : 'text-red-600' }}">
                                                                        {{ round($gradeRecord->percentage, 1) }}%
                                                                    </div>
                                                                @endif
                                                                <div class="flex items-center space-x-1">
                                                                    <input type="checkbox" 
                                                                           name="grades[{{ $gradeRecord->id }}][is_visible_to_student]" 
                                                                           value="1"
                                                                           {{ $gradeRecord->is_visible_to_student ? 'checked' : '' }}
                                                                           class="text-xs">
                                                                    <span class="text-xs text-gray-500">Visible</span>
                                                                </div>
                                                                <textarea name="grades[{{ $gradeRecord->id }}][feedback]" 
                                                                          placeholder="Feedback..."
                                                                          rows="2"
                                                                          class="w-full text-xs rounded border-gray-300">{{ $gradeRecord->feedback }}</textarea>
                                                            </div>
                                                        @else
                                                            <span class="text-xs text-gray-400">No record</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($totalWeighting > 0)
                                                        @php $overallPercentage = round($totalWeightedScore, 1); @endphp
                                                        <div class="text-sm font-semibold {{ $overallPercentage >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                            {{ $overallPercentage }}%
                                                        </div>
                                                    @else
                                                        <span class="text-sm text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('grade-records.module-completion', [$student, $moduleInstance]) }}" 
                                                       class="text-blue-600 hover:text-blue-900">View Progress</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-6 flex justify-between items-center">
                                <div class="flex space-x-4">
                                    <button type="button" onclick="showAllGrades()" 
                                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                        Show All Grades
                                    </button>
                                    <button type="button" onclick="hideAllGrades()" 
                                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                        Hide All Grades
                                    </button>
                                </div>
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Save All Grades
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Students Enrolled</h3>
                            <p class="text-gray-500">Students will appear here once they are enrolled in this module instance.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleComponentVisibility(componentName, visible) {
            fetch(`{{ route('grade-records.toggle-visibility', $moduleInstance) }}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    visible: visible,
                    component_name: componentName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function showAllGrades() {
            if (confirm('Make all grades visible to students immediately? This will save to the database.')) {
                toggleAllVisibility(true);
            }
        }

        function hideAllGrades() {
            if (confirm('Hide all grades from students immediately? This will save to the database.')) {
                toggleAllVisibility(false);
            }
        }

        function toggleAllVisibility(visible) {
            // Update UI first
            document.querySelectorAll('input[type="checkbox"][name*="[is_visible_to_student]"]').forEach(checkbox => {
                checkbox.checked = visible;
            });

            // Save to database
            fetch(`{{ route('grade-records.toggle-visibility', $moduleInstance) }}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    visible: visible
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                } else {
                    showNotification('Failed to update visibility', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while updating grades', 'error');
            });
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-md text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    </script>
</x-app-layout>