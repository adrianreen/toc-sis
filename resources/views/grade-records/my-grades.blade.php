{{-- Student: My Grades with New Architecture --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Grades
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if($gradeRecords->count() > 0)
                {{-- Grade Records by Module Instance --}}
                @foreach($gradeRecords as $moduleInstanceId => $records)
                    @php
                        $moduleInstance = $records->first()->moduleInstance;
                        $module = $moduleInstance->module;
                    @endphp
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                        <div class="p-6 bg-gray-50 border-b border-gray-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $module->title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <strong>Code:</strong> {{ $module->module_code }} |
                                        <strong>Credits:</strong> {{ $module->credit_value }} |
                                        <strong>Delivery:</strong> {{ ucfirst($moduleInstance->delivery_style) }}
                                        @if($moduleInstance->tutor)
                                            | <strong>Tutor:</strong> {{ $moduleInstance->tutor->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    @php
                                        $totalWeightedScore = 0;
                                        $totalWeighting = 0;
                                        $hasGrades = false;
                                        
                                        foreach($records as $record) {
                                            if($record->grade !== null) {
                                                $hasGrades = true;
                                                $component = collect($module->assessment_strategy)->firstWhere('component_name', $record->assessment_component_name);
                                                if($component) {
                                                    $totalWeightedScore += $record->percentage * ($component['weighting'] / 100);
                                                    $totalWeighting += $component['weighting'];
                                                }
                                            }
                                        }
                                        
                                        $overallPercentage = $totalWeighting > 0 ? round($totalWeightedScore, 1) : null;
                                    @endphp
                                    
                                    @if($hasGrades && $overallPercentage !== null)
                                        <div class="text-right">
                                            <div class="text-2xl font-bold 
                                                @if($overallPercentage >= 40) text-green-600 
                                                @else text-red-600 @endif">
                                                {{ $overallPercentage }}%
                                            </div>
                                            <div class="text-sm text-gray-600">Overall Grade</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Assessment Component
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Weighting
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Grade
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Percentage
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Submitted
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($module->assessment_strategy as $component)
                                            @php
                                                $gradeRecord = $records->firstWhere('assessment_component_name', $component['component_name']);
                                                $passMark = $component['component_pass_mark'] ?? 40;
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $component['component_name'] }}
                                                        @if($component['is_must_pass'])
                                                            <span class="text-red-600 font-bold" title="Must pass component">*</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $component['weighting'] }}%
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if($gradeRecord && $gradeRecord->grade !== null)
                                                        {{ $gradeRecord->grade }} / {{ $gradeRecord->max_grade }}
                                                    @else
                                                        <span class="text-gray-400">Not graded</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($gradeRecord && $gradeRecord->percentage !== null)
                                                        <span class="text-sm font-medium
                                                            @if($gradeRecord->percentage >= $passMark) text-green-600 
                                                            @else text-red-600 @endif">
                                                            {{ round($gradeRecord->percentage, 1) }}%
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($gradeRecord && $gradeRecord->percentage !== null)
                                                        @if($gradeRecord->percentage >= $passMark)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Pass
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                                @if($component['is_must_pass']) bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif">
                                                                @if($component['is_must_pass']) Must Pass Fail @else Fail @endif
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    @if($gradeRecord && $gradeRecord->submission_date)
                                                        {{ $gradeRecord->submission_date->format('M d, Y') }}
                                                    @else
                                                        <span class="text-gray-400">Not submitted</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Feedback Section --}}
                            @php
                                $recordsWithFeedback = $records->filter(function($record) {
                                    return $record->feedback && trim($record->feedback) !== '';
                                });
                            @endphp
                            
                            @if($recordsWithFeedback->count() > 0)
                                <div class="mt-6 bg-gray-50 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Feedback</h4>
                                    @foreach($recordsWithFeedback as $record)
                                        <div class="mb-3 last:mb-0">
                                            <div class="text-sm font-medium text-gray-700">{{ $record->assessment_component_name }}:</div>
                                            <div class="text-sm text-gray-600 mt-1">{{ $record->feedback }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            {{-- Pass Mark Information --}}
                            <div class="mt-4 text-xs text-gray-500">
                                <p><span class="text-red-600 font-bold">*</span> Must pass components are required for overall module completion</p>
                                <p>Pass mark: 40% (unless otherwise specified for individual components)</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- No Grades State --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <div class="text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Grades Available</h3>
                        <p class="text-gray-500">You don't have any released grades yet. Grades will appear here once they are made visible by your tutors.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>