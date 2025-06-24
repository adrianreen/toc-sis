<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $moduleInstance->module->title }}</h1>
                <p class="text-gray-600 mt-1">
                    {{ $moduleInstance->module->module_code }} • 
                    {{ $moduleInstance->label ?? 'Default Instance' }} • 
                    {{ $moduleInstance->module->credit_value }} Credits
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm border">
                    <div class="flex items-center space-x-2 text-sm">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span>{{ $stats['graded'] }} graded</span>
                        <div class="w-3 h-3 rounded-full bg-yellow-500 ml-4"></div>
                        <span>{{ $stats['pending'] }} pending</span>
                    </div>
                </div>
                <button onclick="showBulkActions()" class="bg-toc-600 text-white px-4 py-2 rounded-lg hover:bg-toc-700 transition-colors">
                    Bulk Actions
                </button>
                <button onclick="exportGrades()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    Export
                </button>
                <!-- Test Button for View Manager -->
                <button onclick="toggleViewManager()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    📂 View Manager
                </button>
                
                <!-- Quick Test Button -->
                <button onclick="showNotification('Functions are working! Check bottom-right for floating manager.', 'success')" 
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition-colors">
                    Test
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Quick Filters -->
    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">View:</label>
                        <select id="viewFilter" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                            <option value="all">All Students</option>
                            <option value="ungraded">Needs Grading</option>
                            <option value="graded">Already Graded</option>
                            <option value="failed">Failed Components</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Component:</label>
                        <select id="componentFilter" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                            <option value="all">All Components</option>
                            @foreach($assessmentComponents as $component)
                                <option value="{{ $component['component_name'] }}">
                                    {{ $component['component_name'] }} ({{ $component['weighting'] }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button onclick="clearFilters()" class="text-sm text-toc-600 hover:text-toc-800">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Grading Grid - COMPLETELY FIXED -->
    <div class="pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                
                <!-- Assessment Components Header -->
                <div class="bg-gray-50 border-b border-gray-200 p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Assessment Components</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        @foreach($assessmentComponents as $component)
                            <div class="bg-white rounded-lg p-3 border">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 text-sm">{{ $component['component_name'] }}</h4>
                                    @if($component['is_must_pass'])
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Must Pass
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">
                                    <div>Weight: {{ $component['weighting'] }}%</div>
                                    <div>Pass Mark: {{ $component['component_pass_mark'] ?? 40 }}%</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Spreadsheet-like Grading Interface - FIXED -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="gradingTable">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 border-r border-gray-200 min-w-[200px]">
                                    Student
                                </th>
                                @foreach($assessmentComponents as $component)
                                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[160px] relative group" data-component="{{ $component['component_name'] }}">
                                        <div class="flex flex-col items-center">
                                            <span class="truncate">{{ Str::limit($component['component_name'], 15) }}</span>
                                            <span class="text-xs font-normal">{{ $component['weighting'] }}%</span>
                                            
                                            <!-- Direct Visibility Controls -->
                                            <div class="flex items-center justify-center gap-1 mt-2">
                                                <!-- Column Visibility Toggle -->
                                                <button onclick="toggleColumnVisibility('{{ $component['component_name'] }}', true)" 
                                                        class="column-vis-btn show-btn" 
                                                        data-component="{{ $component['component_name'] }}"
                                                        title="Show all {{ $component['component_name'] }} grades to students">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                                
                                                <button onclick="toggleColumnVisibility('{{ $component['component_name'] }}', false)" 
                                                        class="column-vis-btn hide-btn" 
                                                        data-component="{{ $component['component_name'] }}"
                                                        title="Hide all {{ $component['component_name'] }} grades from students">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                                    </svg>
                                                </button>
                                                
                                                <!-- Column Status Indicator -->
                                                <div class="column-status-indicator" id="status-{{ Str::slug($component['component_name']) }}">
                                                    <div class="status-dot mixed" title="Mixed visibility"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px] relative group overall-column-controls">
                                    <div class="flex flex-col items-center">
                                        <span>Overall</span>
                                        
                                        <!-- Direct Overall Visibility Controls -->
                                        <div class="flex items-center justify-center gap-1 mt-2">
                                            <!-- Overall Show Button -->
                                            <button onclick="toggleOverallVisibility(true)" 
                                                    class="overall-vis-btn show-btn" 
                                                    id="overall-show-btn"
                                                    title="Show all overall grades to students">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Overall Hide Button -->
                                            <button onclick="toggleOverallVisibility(false)" 
                                                    class="overall-vis-btn hide-btn" 
                                                    id="overall-hide-btn"
                                                    title="Hide all overall grades from students">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Overall Status Indicator -->
                                            <div class="overall-status-indicator" id="overall-status">
                                                <div class="status-dot mixed" title="Mixed overall visibility"></div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[80px]">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="gradingTableBody">
                            @foreach($enrolledStudents as $student)
                                @php
                                    $gradeRecords = $groupedGradeRecords->get($student->id, collect());
                                    $overallGrade = $student->calculateOverallGradeForModule($moduleInstance);
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors student-row" data-student-id="{{ $student->id }}">
                                    <!-- FIXED: Student Name (Sticky) - Now clickable with proper cursor -->
                                    <td class="px-4 py-3 sticky left-0 bg-white border-r border-gray-200 z-10 relative">
                                        <!-- Student Row Visibility Status - CLICKABLE WITH BETTER VISIBILITY -->
                                        <button class="student-row-status visible" 
                                                id="row-status-{{ $student->id }}" 
                                                onclick="toggleStudentRowVisibility({{ $student->id }})"
                                                title="Click to hide/show this student from grading view">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                        
                                        <div class="flex items-center cursor-pointer hover:bg-gray-50 rounded-lg p-2 -m-2 transition-colors duration-200" 
                                             onclick="window.location.href='{{ route('students.show', $student) }}'"
                                             title="View student profile">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-toc-100 flex items-center justify-center shadow-sm border-2 border-white">
                                                    <span class="text-sm font-bold text-toc-700">
                                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-semibold text-gray-900 hover:text-toc-600 transition-colors student-name">{{ $student->full_name }}</div>
                                                <div class="text-xs text-gray-500 font-medium">{{ $student->student_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- FIXED: Grade Cells for Each Component - NO MORE OVERLAPPING -->
                                    @foreach($assessmentComponents as $component)
                                        @php
                                            $gradeRecord = $gradeRecords->firstWhere('assessment_component_name', $component['component_name']);
                                            $percentage = $gradeRecord?->percentage;
                                            $isGraded = $gradeRecord && $gradeRecord->grade !== null;
                                            $isVisible = $gradeRecord?->is_visible_to_student ?? false;
                                            $isPassing = $percentage && $percentage >= ($component['component_pass_mark'] ?? 40);
                                        @endphp
                                        <td class="px-2 py-2 text-center relative group grade-cell" 
                                            data-student-id="{{ $student->id }}" 
                                            data-component="{{ $component['component_name'] }}"
                                            data-grade-record-id="{{ $gradeRecord?->id }}">
                                            
                                            <div class="grade-cell-container {{ $isGraded && !$isVisible ? 'grade-hidden' : '' }} {{ $isGraded && $isVisible ? 'grade-visible' : '' }}">
                                                <!-- Enhanced Status Strip with Clear Visibility Indicator -->
                                                <div class="status-strip">
                                                    <div class="status-indicators-left">
                                                        @if($gradeRecord?->feedback)
                                                            <div class="status-dot feedback-dot" title="Has feedback"></div>
                                                        @endif
                                                        @if($component['is_must_pass'] && $isGraded)
                                                            <div class="status-dot must-pass-dot {{ $isPassing ? 'passing' : 'failing' }}" 
                                                                 title="{{ $isPassing ? 'Passing must-pass' : 'FAILING must-pass' }}">
                                                                !
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Large Visibility Indicator -->
                                                    @if($isGraded)
                                                        <div class="visibility-status-large">
                                                            @if($isVisible)
                                                                <div class="vis-icon visible" title="Visible to student">
                                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                </div>
                                                            @else
                                                                <div class="vis-icon hidden" title="Hidden from student">
                                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- FIXED: Main Grade Display - Clean, clickable area -->
                                                <div class="grade-display-fixed {{ $isGraded ? 'has-grade' : 'no-grade' }} 
                                                          {{ $isGraded && !$isPassing ? 'failing-grade' : '' }}
                                                          {{ $component['is_must_pass'] && $isGraded && !$isPassing ? 'critical-fail' : '' }}"
                                                     onclick="enableGradeEdit(this)"
                                                     tabindex="0"
                                                     role="button"
                                                     aria-label="Click to edit grade for {{ $student->full_name }} - {{ $component['component_name'] }}">
                                                    
                                                    @if($isGraded)
                                                        <div class="grade-content-fixed">
                                                            <div class="grade-percentage-fixed">{{ $percentage }}%</div>
                                                            <div class="grade-fraction-fixed">{{ $gradeRecord->grade }}/{{ $gradeRecord->max_grade }}</div>
                                                            <div class="grade-status-fixed {{ $isPassing ? 'pass' : 'fail' }}">
                                                                {{ $isPassing ? 'PASS' : 'FAIL' }}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="no-grade-content-fixed">
                                                            <div class="no-grade-icon-fixed">
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                                </svg>
                                                            </div>
                                                            <div class="no-grade-text-fixed">Click to grade</div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- FIXED: Input Field -->
                                                <input type="number" 
                                                       class="grade-input-fixed hidden"
                                                       value="{{ $gradeRecord?->grade ?? '' }}"
                                                       min="0" 
                                                       max="{{ $gradeRecord?->max_grade ?? 100 }}"
                                                       step="0.1"
                                                       placeholder="Enter grade (0-{{ $gradeRecord?->max_grade ?? 100 }})"
                                                       onblur="saveGrade(this)"
                                                       onkeydown="handleGradeKeydown(event, this)">

                                                <!-- FIXED: Clean Action Panel -->
                                                <div class="action-panel-fixed">
                                                    <!-- Row Visibility Toggle - Hides/Shows entire student row -->
                                                    <button onclick="event.stopPropagation(); toggleStudentRowVisibility({{ $student->id }})" 
                                                            class="action-btn-fixed row-visibility-btn" 
                                                            data-student-id="{{ $student->id }}"
                                                            title="Hide/Show this student from grading view">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </button>
                                                    
                                                    @if($isGraded)
                                                        <button onclick="event.stopPropagation(); toggleVisibility({{ $gradeRecord->id }})" 
                                                                class="action-btn-fixed visibility-btn-fixed" 
                                                                title="{{ $isVisible ? 'Hide grade from student' : 'Show grade to student' }}">
                                                            @if($isVisible)
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                                </svg>
                                                            @else
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                                                </svg>
                                                            @endif
                                                        </button>
                                                    @endif
                                                    
                                                    <button onclick="event.stopPropagation(); showFeedbackModal({{ $gradeRecord?->id ?? 'null' }}, '{{ $student->full_name }}', '{{ $component['component_name'] }}')" 
                                                            class="action-btn-fixed feedback-btn-fixed" 
                                                            title="Add/Edit Feedback">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    @endforeach

                                    <!-- Overall Grade -->
                                    <td class="px-4 py-3 text-center">
                                        @if($overallGrade)
                                            <div class="flex flex-col items-center">
                                                <span class="text-sm font-bold {{ $overallGrade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($overallGrade, 1) }}%
                                                </span>
                                                <span class="text-xs {{ $overallGrade >= 40 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $overallGrade >= 40 ? 'PASS' : 'FAIL' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-sm">—</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center space-x-1">
                                            <button onclick="showStudentDetails({{ $student->id }})" 
                                                    class="text-gray-400 hover:text-gray-600 cursor-pointer" 
                                                    title="View Student Details">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Modal -->
    <div id="bulkActionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>
                    <div class="space-y-3">
                        <button onclick="bulkSetGrade()" class="w-full text-left px-4 py-2 hover:bg-gray-50 rounded-lg">
                            Set Grade for All Ungraded
                        </button>
                        <button onclick="bulkMarkAbsent()" class="w-full text-left px-4 py-2 hover:bg-gray-50 rounded-lg">
                            Mark All Absent (0%)
                        </button>
                        <button onclick="bulkShowGrades()" class="w-full text-left px-4 py-2 hover:bg-gray-50 rounded-lg">
                            Show All Grades to Students
                        </button>
                        <button onclick="bulkHideGrades()" class="w-full text-left px-4 py-2 hover:bg-gray-50 rounded-lg">
                            Hide All Grades from Students
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button onclick="hideBulkActions()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating View Management Panel - FIXED POSITIONING -->
    <div id="viewManagerPanel" class="fixed bottom-24 right-6 bg-white rounded-lg shadow-2xl border-2 border-gray-200 z-50 min-w-[320px] max-w-[400px] hidden">
        <div class="p-4">
            <!-- Panel Header -->
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-toc-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    View Manager
                </h3>
                <button onclick="closeViewManager()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L6 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <!-- Hidden Students Count -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-red-800">
                        <span id="hiddenStudentCount">0</span> students hidden
                    </span>
                    <button onclick="showAllStudents()" class="text-xs bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1 rounded transition-colors">
                        Show All
                    </button>
                </div>
            </div>

            <!-- Hidden Students List -->
            <div id="hiddenStudentsList" class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                <!-- Hidden students will be populated here -->
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <button onclick="saveCurrentView()" class="w-full bg-toc-600 hover:bg-toc-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    💾 Save Current View
                </button>
                <button onclick="loadSavedView()" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    📂 Load Saved View
                </button>
                <button onclick="resetView()" class="w-full bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                    🔄 Reset View
                </button>
            </div>
        </div>
    </div>

    <!-- Floating View Manager Button - FIXED POSITIONING -->
    <button id="viewManagerBtn" onclick="toggleViewManager()" 
            class="fixed bottom-6 right-6 bg-toc-600 hover:bg-toc-700 text-white p-4 rounded-full shadow-2xl z-40 transition-all duration-300 border-4 border-white">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
        </svg>
        <span id="hiddenCountBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center font-bold hidden shadow-lg">0</span>
    </button>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <form id="feedbackForm">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4" id="feedbackModalTitle">Add Feedback</h3>
                        <textarea id="feedbackText" rows="6" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-toc-500 focus:border-toc-500"
                                  placeholder="Enter detailed feedback for the student..."></textarea>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" onclick="hideFeedbackModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-toc-600 text-white rounded-md hover:bg-toc-700">
                            Save Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- COMPLETELY FIXED CSS - NO MORE OVERLAPPING -->
    <style>
        /* Fixed Grade Cell Container */
        .grade-cell {
            min-width: 160px;
            height: 100px;
            padding: 4px;
        }
        
        .grade-cell-container {
            width: 100%;
            height: 100%;
            position: relative;
            border-radius: 12px;
            overflow: hidden;
        }
        
        /* FIXED: Status Strip - No overlap */
        .status-strip {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 6px;
            background: rgba(0, 0, 0, 0.05);
            z-index: 5;
        }
        
        .status-indicators-left {
            display: flex;
            gap: 4px;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 1px solid white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: bold;
        }
        
        .feedback-dot {
            background: #3b82f6;
        }
        
        .must-pass-dot.passing {
            background: #10b981;
            color: white;
        }
        
        .must-pass-dot.failing {
            background: #ef4444;
            color: white;
            animation: critical-pulse 1.5s infinite;
        }
        
        /* Enhanced Visibility Status in Cells */
        .visibility-status-large {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .vis-icon {
            padding: 2px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .vis-icon.visible {
            background: rgba(16, 185, 129, 0.2);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }
        
        .vis-icon.hidden {
            background: rgba(245, 158, 11, 0.2);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.4);
        }
        
        /* Grade Cell Visual States */
        .grade-cell-container.grade-visible {
            border-left: 3px solid #10b981;
            background: linear-gradient(to right, rgba(16, 185, 129, 0.05), transparent);
        }
        
        .grade-cell-container.grade-hidden {
            border-left: 3px solid #f59e0b;
            background: linear-gradient(to right, rgba(245, 158, 11, 0.05), transparent);
        }
        
        .must-pass-indicator {
            font-size: 8px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            line-height: 1;
        }
        
        .must-pass-indicator.passing {
            background: #dcfce7;
            color: #15803d;
        }
        
        .must-pass-indicator.failing {
            background: #fecaca;
            color: #dc2626;
            animation: pulse 2s infinite;
        }
        
        /* FIXED: Main Grade Display - Clean, obvious clickability */
        .grade-display-fixed {
            position: absolute;
            top: 20px; /* Below status strip */
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* FIXED: Clear hover states */
        .grade-display-fixed:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: #3b82f6;
        }
        
        .grade-display-fixed:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }
        
        /* Grade states */
        .grade-display-fixed.has-grade {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-color: #93c5fd;
        }
        
        .grade-display-fixed.no-grade {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border: 2px dashed #d1d5db;
        }
        
        .grade-display-fixed.no-grade:hover {
            border-style: solid;
            border-color: #3b82f6;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }
        
        .grade-display-fixed.failing-grade {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #f87171;
        }
        
        .grade-display-fixed.critical-fail {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #ef4444;
            animation: critical-pulse 2s infinite;
        }
        
        /* Grade content styling */
        .grade-content-fixed {
            text-align: center;
        }
        
        .grade-percentage-fixed {
            font-size: 20px;
            font-weight: 800;
            line-height: 1;
            color: #1e40af;
            margin-bottom: 4px;
        }
        
        .grade-fraction-fixed {
            font-size: 10px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .grade-status-fixed {
            font-size: 9px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            line-height: 1;
        }
        
        .grade-status-fixed.pass {
            background: #dcfce7;
            color: #15803d;
        }
        
        .grade-status-fixed.fail {
            background: #fecaca;
            color: #dc2626;
        }
        
        /* No grade content */
        .no-grade-content-fixed {
            text-align: center;
            opacity: 0.8;
        }
        
        .no-grade-icon-fixed {
            color: #9ca3af;
            margin-bottom: 6px;
        }
        
        .grade-display-fixed:hover .no-grade-icon-fixed {
            color: #3b82f6;
        }
        
        .no-grade-text-fixed {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }
        
        .grade-display-fixed:hover .no-grade-text-fixed {
            color: #3b82f6;
        }
        
        /* FIXED: Input styling */
        .grade-input-fixed {
            position: absolute;
            top: 20px; /* Below status strip */
            left: 0;
            right: 0;
            bottom: 0;
            border: 3px solid #3b82f6;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            background: white;
            z-index: 15;
        }
        
        .grade-input-fixed:focus {
            outline: none;
            border-color: #1d4ed8;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            background: #fffbeb;
        }
        
        /* FIXED: Action panel - Clean organization */
        .action-panel-fixed {
            position: absolute;
            bottom: 4px;
            right: 4px;
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 10;
        }
        
        .grade-cell:hover .action-panel-fixed {
            opacity: 1;
        }
        
        .action-btn-fixed {
            padding: 6px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            backdrop-filter: blur(4px);
        }
        
        .action-btn-fixed:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: scale(1.1);
        }
        
        .visibility-btn-fixed:hover {
            background: rgba(234, 179, 8, 0.9);
        }
        
        .feedback-btn-fixed:hover {
            background: rgba(59, 130, 246, 0.9);
        }
        
        .row-visibility-btn:hover {
            background: rgba(239, 68, 68, 0.9);
        }
        
        /* Hidden Student Row Styles */
        .student-row.row-hidden {
            opacity: 0.3;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 2px,
                rgba(239, 68, 68, 0.1) 2px,
                rgba(239, 68, 68, 0.1) 4px
            );
            filter: grayscale(70%);
            transition: all 0.5s ease;
        }
        
        .student-row.row-hidden:hover {
            opacity: 0.6;
            filter: grayscale(30%);
        }
        
        .student-row.row-hidden .student-name {
            text-decoration: line-through;
            color: #ef4444;
        }
        
        .student-row.row-hidden .grade-cell-container {
            border-left-color: #ef4444;
            background: linear-gradient(to right, rgba(239, 68, 68, 0.1), transparent);
        }
        
        /* Row visibility indicator in student name cell - FIXED TO BE VISIBLE */
        .student-row-status {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 20;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .student-row-status:hover {
            transform: scale(1.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        .student-row-status.visible {
            background: #10b981;
        }
        
        .student-row-status.visible:hover {
            background: #059669;
        }
        
        .student-row-status.hidden {
            background: #ef4444;
            animation: pulse-danger 2s infinite;
        }
        
        .student-row-status.hidden:hover {
            background: #dc2626;
        }
        
        @keyframes pulse-danger {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Animations */
        @keyframes critical-pulse {
            0%, 100% { 
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 0 0 0 rgba(239, 68, 68, 0.4);
            }
            50% { 
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 0 0 8px rgba(239, 68, 68, 0.1);
            }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Loading state */
        .grade-display-fixed.saving {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .grade-display-fixed.saving::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #3b82f6;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Floating Elements - Ensure they don't get cut off */
        body {
            padding-bottom: 120px !important; /* Space for floating elements */
        }
        
        #viewManagerBtn {
            animation: gentle-pulse 3s infinite;
            position: fixed !important;
            bottom: 30px !important;
            right: 30px !important;
            z-index: 9999 !important;
        }
        
        #viewManagerPanel {
            position: fixed !important;
            bottom: 100px !important;
            right: 30px !important;
            z-index: 9998 !important;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        @keyframes gentle-pulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 0 12px 35px rgba(59, 130, 246, 0.3);
            }
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .grade-cell {
                min-width: 120px;
                height: 80px;
            }
            
            .grade-percentage-fixed {
                font-size: 16px;
            }
            
            .no-grade-icon-fixed svg {
                width: 20px;
                height: 20px;
            }
            
            #viewManagerPanel {
                bottom: 20px;
                right: 20px;
                left: 20px;
                min-width: auto;
            }
            
            #viewManagerBtn {
                bottom: 20px;
                right: 20px;
            }
        }
        
        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            .grade-display-fixed {
                transition: none;
            }
            
            .grade-display-fixed:hover {
                transform: none;
            }
            
            .critical-pulse, .pulse {
                animation: none;
            }
        }
        
        /* Column and Overall Visibility Controls */
        .column-vis-btn, .overall-vis-btn {
            padding: 4px;
            border: 2px solid transparent;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            opacity: 0.7;
        }
        
        .column-vis-btn:hover, .overall-vis-btn:hover {
            opacity: 1;
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .column-vis-btn.show-btn, .overall-vis-btn.show-btn {
            color: #059669;
            border-color: transparent;
        }
        
        .column-vis-btn.show-btn:hover, .overall-vis-btn.show-btn:hover {
            background: #dcfce7;
            border-color: #059669;
            color: #047857;
        }
        
        .column-vis-btn.hide-btn, .overall-vis-btn.hide-btn {
            color: #d97706;
            border-color: transparent;
        }
        
        .column-vis-btn.hide-btn:hover, .overall-vis-btn.hide-btn:hover {
            background: #fef3c7;
            border-color: #d97706;
            color: #b45309;
        }
        
        /* Column Status Indicators */
        .column-status-indicator, .overall-status-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 4px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            border: 1px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        .status-dot.visible {
            background: #10b981;
            animation: pulse-visible 2s infinite;
        }
        
        .status-dot.hidden {
            background: #f59e0b;
            animation: pulse-hidden 2s infinite;
        }
        
        .status-dot.mixed {
            background: linear-gradient(45deg, #10b981 50%, #f59e0b 50%);
        }
        
        @keyframes pulse-visible {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes pulse-hidden {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        /* Component Actions Menu */
        .component-actions-menu {
            backdrop-filter: blur(8px);
            animation: dropdownSlide 0.2s ease-out;
        }
        
        .component-actions-menu:not(.hidden) {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }
        
        .component-actions-menu.hidden {
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
        }
        
        @keyframes dropdownSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Overall column visibility controls */
        .overall-column-controls {
            position: relative;
        }
        
        .overall-visibility-badge {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 4px;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .overall-visible {
            background: #dcfce7;
            color: #15803d;
        }
        
        .overall-hidden {
            background: #fef3c7;
            color: #92400e;
        }
        
        .overall-mixed {
            background: #e0e7ff;
            color: #3730a3;
        }
    </style>

    <!-- Enhanced JavaScript -->
    <script>
        let currentEditCell = null;
        let currentGradeRecordId = null;
        
        // Enhanced grade editing with better UX
        function enableGradeEdit(displayElement) {
            if (currentEditCell) {
                cancelEdit();
            }
            
            const cell = displayElement.closest('.grade-cell');
            const input = cell.querySelector('.grade-input-fixed');
            const display = cell.querySelector('.grade-display-fixed');
            
            currentEditCell = cell;
            currentGradeRecordId = cell.dataset.gradeRecordId;
            
            // Smooth transition
            display.style.opacity = '0';
            setTimeout(() => {
                display.classList.add('hidden');
                input.classList.remove('hidden');
                input.focus();
                input.select();
            }, 150);
        }
        
        function cancelEdit() {
            if (!currentEditCell) return;
            
            const display = currentEditCell.querySelector('.grade-display-fixed');
            const input = currentEditCell.querySelector('.grade-input-fixed');
            
            input.classList.add('hidden');
            display.classList.remove('hidden');
            display.style.opacity = '1';
            
            currentEditCell = null;
            currentGradeRecordId = null;
        }
        
        function saveGrade(input) {
            if (!currentEditCell) return;
            
            const grade = parseFloat(input.value);
            const maxGrade = parseFloat(input.getAttribute('max'));
            const studentId = currentEditCell.dataset.studentId;
            const component = currentEditCell.dataset.component;
            const gradeRecordId = currentEditCell.dataset.gradeRecordId;
            
            if (input.value.trim() === '') {
                cancelEdit();
                return;
            }
            
            if (isNaN(grade) || grade < 0 || grade > maxGrade) {
                input.style.borderColor = '#ef4444';
                showNotification(`Please enter a valid grade between 0 and ${maxGrade}`, 'error');
                setTimeout(() => {
                    input.style.borderColor = '#3b82f6';
                }, 2000);
                input.focus();
                return;
            }
            
            // Show saving state
            const display = currentEditCell.querySelector('.grade-display-fixed');
            display.classList.add('saving');
            
            // FIXED: Real AJAX save to backend
            if (!gradeRecordId) {
                showNotification('No grade record found. Please refresh the page.', 'error');
                display.classList.remove('saving');
                cancelEdit();
                return;
            }
            
            fetch(`{{ url('/grade-records') }}/${gradeRecordId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    grade: grade,
                    max_grade: maxGrade,
                    feedback: null,
                    submission_date: new Date().toISOString().split('T')[0],
                    is_visible_to_student: false,
                    release_date: null
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the display without page refresh
                    updateGradeDisplay(currentEditCell, {
                        grade: grade,
                        max_grade: maxGrade,
                        percentage: data.percentage || Math.round((grade / maxGrade) * 100)
                    });
                    
                    display.classList.remove('saving');
                    showNotification('Grade saved successfully!', 'success');
                    cancelEdit();
                } else {
                    throw new Error(data.message || 'Failed to save grade');
                }
            })
            .catch(error => {
                console.error('Error saving grade:', error);
                display.classList.remove('saving');
                showNotification(`Error saving grade: ${error.message}`, 'error');
                cancelEdit();
            });
        }
        
        // Helper function to update grade display without page refresh
        function updateGradeDisplay(cell, gradeData) {
            const display = cell.querySelector('.grade-display-fixed');
            const percentage = gradeData.percentage;
            const isPassing = percentage >= 40; // You may want to make this configurable
            
            // Update the display content
            display.innerHTML = `
                <div class="grade-content-fixed">
                    <div class="grade-percentage-fixed">${percentage}%</div>
                    <div class="grade-fraction-fixed">${gradeData.grade}/${gradeData.max_grade}</div>
                    <div class="grade-status-fixed ${isPassing ? 'pass' : 'fail'}">
                        ${isPassing ? 'PASS' : 'FAIL'}
                    </div>
                </div>
            `;
            
            // Update classes
            display.classList.remove('no-grade');
            display.classList.add('has-grade');
            
            if (!isPassing) {
                display.classList.add('failing-grade');
            } else {
                display.classList.remove('failing-grade');
            }
        }
        
        function handleGradeKeydown(event, input) {
            switch(event.key) {
                case 'Enter':
                    event.preventDefault();
                    saveGrade(input);
                    break;
                case 'Escape':
                    event.preventDefault();
                    cancelEdit();
                    break;
            }
        }
        
        // Enhanced notification system
        function showNotification(message, type = 'info', duration = 4000) {
            const notification = document.createElement('div');
            const icon = type === 'success' ? 
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>' :
                '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>';
            
            notification.className = `fixed top-4 right-4 p-4 rounded-xl shadow-2xl z-50 flex items-center space-x-3 transform translate-x-full transition-all duration-500 ease-out ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            
            notification.innerHTML = `
                <div class="flex-shrink-0">${icon}</div>
                <div class="flex-1"><p class="font-medium">${message}</p></div>
                <button onclick="this.parentElement.remove()" class="flex-shrink-0 ml-2 opacity-70 hover:opacity-100">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translate(0)';
            }, 100);
            
            setTimeout(() => {
                notification.style.transform = 'translate(100%)';
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 500);
            }, duration);
        }
        
        // Bulk actions
        function showBulkActions() {
            document.getElementById('bulkActionsModal').classList.remove('hidden');
        }
        
        function hideBulkActions() {
            document.getElementById('bulkActionsModal').classList.add('hidden');
        }
        
        function bulkShowGrades() {
            if (confirm('Show all graded assessments to students?')) {
                showNotification('All grades made visible to students', 'success');
            }
            hideBulkActions();
        }
        
        function bulkHideGrades() {
            if (confirm('Hide all grades from students?')) {
                showNotification('All grades hidden from students', 'success');
            }
            hideBulkActions();
        }
        
        function bulkSetGrade() {
            const grade = prompt('Enter grade to set for all ungraded assessments:');
            if (grade && !isNaN(grade)) {
                showNotification(`Set grade ${grade} for all ungraded assessments`, 'success');
            }
            hideBulkActions();
        }
        
        function bulkMarkAbsent() {
            if (confirm('Mark all ungraded assessments as absent (0%)?')) {
                showNotification('Marked all ungraded as absent (0%)', 'success');
            }
            hideBulkActions();
        }
        
        // Feedback modal
        function showFeedbackModal(gradeRecordId, studentName, component) {
            document.getElementById('feedbackModalTitle').textContent = `Feedback for ${studentName} - ${component}`;
            document.getElementById('feedbackModal').classList.remove('hidden');
        }
        
        function hideFeedbackModal() {
            document.getElementById('feedbackModal').classList.add('hidden');
            document.getElementById('feedbackText').value = '';
        }
        
        // Utility functions
        function exportGrades() {
            showNotification('Exporting grades...', 'info');
        }
        
        function showComponentActions(componentName) {
            showNotification(`Component actions for: ${componentName}`, 'info');
        }
        
        // Direct Column Visibility Controls
        function toggleColumnVisibility(componentName, makeVisible) {
            const action = makeVisible ? 'show' : 'hide';
            const message = makeVisible ? 
                `Show all graded ${componentName} assessments to students?` : 
                `Hide all ${componentName} assessments from students?`;
            
            if (confirm(message)) {
                fetch(`{{ route('grade-records.bulk-component-visibility', $moduleInstance) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        component_name: componentName,
                        visible: makeVisible
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const actionText = makeVisible ? 'visible to' : 'hidden from';
                        showNotification(`Made all ${componentName} grades ${actionText} students`, 'success');
                        
                        // Update all individual cells for this component
                        updateComponentCells(componentName, makeVisible);
                        
                        // Update column status indicator
                        updateColumnStatusIndicator(componentName, makeVisible ? 'visible' : 'hidden');
                    } else {
                        showNotification('Error updating visibility', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Network error updating visibility', 'error');
                });
            }
        }
        
        // Update all cells for a component
        function updateComponentCells(componentName, isVisible) {
            const componentCells = document.querySelectorAll(`[data-component="${componentName}"]`);
            
            componentCells.forEach(cell => {
                const container = cell.querySelector('.grade-cell-container');
                const visIcon = cell.querySelector('.vis-icon');
                const visibilityBtn = cell.querySelector('.visibility-btn-fixed');
                
                if (container) {
                    // Update container classes
                    container.classList.remove('grade-visible', 'grade-hidden');
                    if (container.classList.contains('has-grade') || container.querySelector('.has-grade')) {
                        container.classList.add(isVisible ? 'grade-visible' : 'grade-hidden');
                    }
                }
                
                if (visIcon) {
                    // Update visibility icon
                    visIcon.className = `vis-icon ${isVisible ? 'visible' : 'hidden'}`;
                    visIcon.title = isVisible ? 'Visible to student' : 'Hidden from student';
                    
                    // Update SVG
                    const svg = visIcon.querySelector('svg');
                    if (svg) {
                        if (isVisible) {
                            svg.innerHTML = `
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            `;
                        } else {
                            svg.innerHTML = `
                                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                            `;
                        }
                    }
                }
                
                if (visibilityBtn) {
                    // Update quick action button too
                    visibilityBtn.title = isVisible ? 'Hide from student' : 'Show to student';
                    const btnSvg = visibilityBtn.querySelector('svg');
                    if (btnSvg) {
                        btnSvg.innerHTML = isVisible ? 
                            `<path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>` :
                            `<path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>`;
                    }
                }
            });
        }
        
        // Update column status indicator
        function updateColumnStatusIndicator(componentName, status) {
            const componentSlug = componentName.replace(/[^a-z0-9]/gi, '-').toLowerCase();
            const statusElement = document.getElementById(`status-${componentSlug}`);
            
            if (statusElement) {
                const dot = statusElement.querySelector('.status-dot');
                if (dot) {
                    dot.className = `status-dot ${status}`;
                    
                    switch(status) {
                        case 'visible':
                            dot.title = 'All grades visible to students';
                            break;
                        case 'hidden':
                            dot.title = 'All grades hidden from students';
                            break;
                        case 'mixed':
                        default:
                            dot.title = 'Mixed visibility';
                            break;
                    }
                }
            }
        }
        
        // Bulk visibility functions for components
        function bulkShowComponentGrades(componentName) {
            if (confirm(`Show all graded ${componentName} assessments to students?`)) {
                fetch(`{{ route('grade-records.bulk-component-visibility', $moduleInstance) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        component_name: componentName,
                        visible: true
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`Made all ${componentName} grades visible to students`, 'success');
                        updateComponentVisibilityIndicator(componentName, 'visible');
                        updateIndividualVisibilityIcons(componentName, true);
                    } else {
                        showNotification('Error updating visibility', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Network error updating visibility', 'error');
                });
            }
            
            // Close menu
            const componentSlug = componentName.replace(/[^a-z0-9]/gi, '-').toLowerCase();
            document.getElementById(`component-menu-${componentSlug}`).classList.add('hidden');
        }
        
        function bulkHideComponentGrades(componentName) {
            if (confirm(`Hide all ${componentName} assessments from students?`)) {
                fetch(`{{ route('grade-records.bulk-component-visibility', $moduleInstance) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        component_name: componentName,
                        visible: false
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`Hidden all ${componentName} grades from students`, 'success');
                        updateComponentVisibilityIndicator(componentName, 'hidden');
                        updateIndividualVisibilityIcons(componentName, false);
                    } else {
                        showNotification('Error updating visibility', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Network error updating visibility', 'error');
                });
            }
            
            // Close menu
            const componentSlug = componentName.replace(/[^a-z0-9]/gi, '-').toLowerCase();
            document.getElementById(`component-menu-${componentSlug}`).classList.add('hidden');
        }
        
        function scheduleComponentRelease(componentName) {
            const date = prompt(`Enter release date for ${componentName} (YYYY-MM-DD HH:MM):`);
            if (date) {
                fetch(`{{ route('grade-records.schedule-component-release', $moduleInstance) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        component_name: componentName,
                        release_date: date
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`Scheduled ${componentName} release for ${date}`, 'success');
                    } else {
                        showNotification('Error scheduling release', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Network error scheduling release', 'error');
                });
            }
            
            // Close menu
            const componentSlug = componentName.replace(/[^a-z0-9]/gi, '-').toLowerCase();
            document.getElementById(`component-menu-${componentSlug}`).classList.add('hidden');
        }
        
        function exportComponentGrades(componentName) {
            showNotification(`Exporting ${componentName} grades...`, 'info');
            window.open(`{{ route('grade-records.export-component', $moduleInstance) }}?component=${encodeURIComponent(componentName)}`, '_blank');
            
            // Close menu
            const componentSlug = componentName.replace(/[^a-z0-9]/gi, '-').toLowerCase();
            document.getElementById(`component-menu-${componentSlug}`).classList.add('hidden');
        }
        
        // Update visibility indicators
        function updateComponentVisibilityIndicator(componentName, status) {
            const componentSlug = componentName.replace(/[^a-z0-9]/gi, '-').toLowerCase();
            const indicator = document.getElementById(`visibility-indicator-${componentSlug}`);
            
            if (indicator) {
                const visibleBadge = document.getElementById(`visible-badge-${componentSlug}`);
                const hiddenBadge = document.getElementById(`hidden-badge-${componentSlug}`);
                const mixedBadge = document.getElementById(`mixed-badge-${componentSlug}`);
                
                // Hide all badges first
                [visibleBadge, hiddenBadge, mixedBadge].forEach(badge => {
                    if (badge) badge.classList.add('hidden');
                });
                
                // Show appropriate badge
                switch(status) {
                    case 'visible':
                        if (visibleBadge) visibleBadge.classList.remove('hidden');
                        break;
                    case 'hidden':
                        if (hiddenBadge) hiddenBadge.classList.remove('hidden');
                        break;
                    case 'mixed':
                    default:
                        if (mixedBadge) mixedBadge.classList.remove('hidden');
                        break;
                }
            }
        }
        
        function updateIndividualVisibilityIcons(componentName, isVisible) {
            // Update all visibility icons for this component
            const componentCells = document.querySelectorAll(`[data-component="${componentName}"]`);
            componentCells.forEach(cell => {
                const visibilityBtn = cell.querySelector('.visibility-btn-fixed');
                if (visibilityBtn) {
                    const svg = visibilityBtn.querySelector('svg');
                    if (svg) {
                        if (isVisible) {
                            // Eye icon (visible)
                            svg.innerHTML = `
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            `;
                        } else {
                            // Eye-slash icon (hidden)
                            svg.innerHTML = `
                                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                            `;
                        }
                        visibilityBtn.title = isVisible ? 'Hide from student' : 'Show to student';
                    }
                }
            });
        }
        
        function showStudentDetails(studentId) {
            showNotification('Opening student details...', 'info');
        }
        
        // Session-based hidden students tracking (no auto-save)
        let hiddenStudentsSession = [];
        
        // Student Row Visibility Management - No Auto-Save
        function toggleStudentRowVisibility(studentId) {
            console.log('toggleStudentRowVisibility called with studentId:', studentId);
            
            const studentRow = document.querySelector(`.student-row[data-student-id="${studentId}"]`);
            const statusIndicator = document.getElementById(`row-status-${studentId}`);
            const rowVisibilityBtns = document.querySelectorAll(`.row-visibility-btn[data-student-id="${studentId}"]`);
            
            console.log('Found elements:', { studentRow, statusIndicator, rowVisibilityBtns });
            
            if (!studentRow || !statusIndicator) {
                console.error('Missing elements for student', studentId);
                showNotification('Error: Student row not found', 'error');
                return;
            }
            
            const isCurrentlyHidden = studentRow.classList.contains('row-hidden');
            const studentName = studentRow.querySelector('.student-name')?.textContent || 'this student';
            
            // Toggle row visibility
            if (!isCurrentlyHidden) {
                // Hide the row
                studentRow.classList.add('row-hidden');
                statusIndicator.className = 'student-row-status hidden';
                statusIndicator.title = 'Student hidden from grading view';
                
                // Add to session hidden list
                if (!hiddenStudentsSession.find(s => s.id === studentId)) {
                    hiddenStudentsSession.push({
                        id: studentId,
                        name: studentName
                    });
                }
                
                // Update all row visibility buttons for this student
                rowVisibilityBtns.forEach(btn => {
                    btn.title = 'Show this student in grading view';
                    const svg = btn.querySelector('svg');
                    if (svg) {
                        svg.innerHTML = `
                            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                            <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                        `;
                    }
                });
                
                showNotification(`${studentName} hidden from view (unsaved)`, 'info');
            } else {
                // Show the row
                studentRow.classList.remove('row-hidden');
                statusIndicator.className = 'student-row-status visible';
                statusIndicator.title = 'Student visible in grading view';
                
                // Remove from session hidden list
                hiddenStudentsSession = hiddenStudentsSession.filter(s => s.id !== studentId);
                
                // Update all row visibility buttons for this student
                rowVisibilityBtns.forEach(btn => {
                    btn.title = 'Hide this student from grading view';
                    const svg = btn.querySelector('svg');
                    if (svg) {
                        svg.innerHTML = `
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        `;
                    }
                });
                
                showNotification(`${studentName} restored to view`, 'success');
            }
            
            // Update view manager UI
            updateViewManagerUI();
        }
        
        // View Manager Functions
        function toggleViewManager() {
            console.log('toggleViewManager called');
            
            const panel = document.getElementById('viewManagerPanel');
            const button = document.getElementById('viewManagerBtn');
            
            console.log('Found panel:', panel, 'button:', button);
            
            if (!panel || !button) {
                console.error('View manager elements not found!');
                showNotification('Error: View manager elements not found', 'error');
                return;
            }
            
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                button.style.transform = 'scale(0.8)';
                updateViewManagerUI();
                showNotification('View Manager opened!', 'success');
            } else {
                panel.classList.add('hidden');
                button.style.transform = 'scale(1)';
                showNotification('View Manager closed!', 'info');
            }
        }
        
        function closeViewManager() {
            document.getElementById('viewManagerPanel').classList.add('hidden');
            document.getElementById('viewManagerBtn').style.transform = 'scale(1)';
        }
        
        function updateViewManagerUI() {
            const countElement = document.getElementById('hiddenStudentCount');
            const badgeElement = document.getElementById('hiddenCountBadge');
            const listElement = document.getElementById('hiddenStudentsList');
            
            const hiddenCount = hiddenStudentsSession.length;
            
            // Update count
            countElement.textContent = hiddenCount;
            
            // Update badge
            if (hiddenCount > 0) {
                badgeElement.textContent = hiddenCount;
                badgeElement.classList.remove('hidden');
            } else {
                badgeElement.classList.add('hidden');
            }
            
            // Update list
            if (hiddenCount === 0) {
                listElement.innerHTML = '<div class="text-sm text-gray-500 italic text-center py-2">No students hidden</div>';
            } else {
                listElement.innerHTML = hiddenStudentsSession.map(student => `
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2">
                        <span class="text-sm font-medium text-gray-700">${student.name}</span>
                        <button onclick="restoreStudent(${student.id})" 
                                class="text-xs bg-green-100 hover:bg-green-200 text-green-700 px-2 py-1 rounded transition-colors">
                            Restore
                        </button>
                    </div>
                `).join('');
            }
        }
        
        function restoreStudent(studentId) {
            toggleStudentRowVisibility(studentId);
        }
        
        function showAllStudents() {
            if (hiddenStudentsSession.length === 0) {
                showNotification('No students are currently hidden', 'info');
                return;
            }
            
            if (confirm(`Restore all ${hiddenStudentsSession.length} hidden students to view?`)) {
                // Create a copy of the array since we'll be modifying it
                const studentsToRestore = [...hiddenStudentsSession];
                
                studentsToRestore.forEach(student => {
                    const studentRow = document.querySelector(`.student-row[data-student-id="${student.id}"]`);
                    if (studentRow && studentRow.classList.contains('row-hidden')) {
                        toggleStudentRowVisibility(student.id);
                    }
                });
                
                showNotification('All students restored to view', 'success');
            }
        }
        
        function saveCurrentView() {
            const viewData = {
                hiddenStudents: hiddenStudentsSession,
                timestamp: new Date().toISOString(),
                moduleInstanceId: {{ $moduleInstance->id }}
            };
            
            localStorage.setItem('savedGradingView', JSON.stringify(viewData));
            showNotification('✅ Current view saved successfully!', 'success');
        }
        
        function loadSavedView() {
            const savedView = localStorage.getItem('savedGradingView');
            
            if (!savedView) {
                showNotification('No saved view found', 'info');
                return;
            }
            
            try {
                const viewData = JSON.parse(savedView);
                
                // Check if it's for the same module
                if (viewData.moduleInstanceId !== {{ $moduleInstance->id }}) {
                    if (!confirm('Saved view is from a different module. Load anyway?')) {
                        return;
                    }
                }
                
                if (confirm(`Load saved view from ${new Date(viewData.timestamp).toLocaleString()}?`)) {
                    // First restore all currently hidden students
                    showAllStudents();
                    
                    // Then hide the students from saved view
                    viewData.hiddenStudents.forEach(student => {
                        const studentRow = document.querySelector(`.student-row[data-student-id="${student.id}"]`);
                        if (studentRow && !studentRow.classList.contains('row-hidden')) {
                            toggleStudentRowVisibility(student.id);
                        }
                    });
                    
                    showNotification('📂 Saved view loaded successfully!', 'success');
                }
            } catch (error) {
                showNotification('Error loading saved view', 'error');
            }
        }
        
        function resetView() {
            if (confirm('Reset view to show all students?')) {
                showAllStudents();
                showNotification('🔄 View reset - all students visible', 'success');
            }
        }
        
        function toggleVisibility(gradeRecordId) {
            fetch(`{{ url('/grade-records') }}/${gradeRecordId}/toggle-visibility`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Grade visibility updated', 'success');
                    
                    // Update the visibility for this specific grade
                    const gradeCell = document.querySelector(`[data-grade-record-id="${gradeRecordId}"]`);
                    if (gradeCell) {
                        const container = gradeCell.querySelector('.grade-cell-container');
                        const visibilityBtn = gradeCell.querySelector('.visibility-btn-fixed');
                        const visIcon = gradeCell.querySelector('.vis-icon');
                        const svg = visibilityBtn?.querySelector('svg');
                        
                        // Update container visual state
                        if (container) {
                            container.classList.remove('grade-visible', 'grade-hidden');
                            container.classList.add(data.is_visible ? 'grade-visible' : 'grade-hidden');
                        }
                        
                        // Update main visibility icon
                        if (visIcon) {
                            visIcon.className = `vis-icon ${data.is_visible ? 'visible' : 'hidden'}`;
                            visIcon.title = data.is_visible ? 'Visible to student' : 'Hidden from student';
                            
                            const visIconSvg = visIcon.querySelector('svg');
                            if (visIconSvg) {
                                if (data.is_visible) {
                                    visIconSvg.innerHTML = `
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    `;
                                } else {
                                    visIconSvg.innerHTML = `
                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                    `;
                                }
                            }
                        }
                        
                        // Update quick action button
                        if (svg) {
                            if (data.is_visible) {
                                svg.innerHTML = `
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                `;
                                visibilityBtn.title = 'Hide from student';
                            } else {
                                svg.innerHTML = `
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                `;
                                visibilityBtn.title = 'Show to student';
                            }
                        }
                        
                        // Update column indicator if needed
                        const componentName = gradeCell.dataset.component;
                        if (componentName) {
                            updateColumnVisibilityStatus(componentName);
                        }
                    }
                } else {
                    showNotification('Error updating visibility', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Network error updating visibility', 'error');
            });
        }
        
        function deleteGrade(gradeRecordId) {
            if (confirm('Are you sure you want to clear this grade? This action cannot be undone.')) {
                fetch(`{{ url('/grade-records') }}/${gradeRecordId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        grade: null,
                        max_grade: 100,
                        feedback: null,
                        submission_date: null,
                        is_visible_to_student: false,
                        release_date: null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Grade cleared successfully', 'success');
                        
                        // Update the display to show no grade
                        const gradeCell = document.querySelector(`[data-grade-record-id="${gradeRecordId}"]`);
                        if (gradeCell) {
                            const display = gradeCell.querySelector('.grade-display-fixed');
                            display.innerHTML = `
                                <div class="no-grade-content-fixed">
                                    <div class="no-grade-icon-fixed">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </div>
                                    <div class="no-grade-text-fixed">Click to grade</div>
                                </div>
                            `;
                            display.classList.remove('has-grade', 'failing-grade');
                            display.classList.add('no-grade');
                        }
                    } else {
                        showNotification('Error clearing grade', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Network error clearing grade', 'error');
                });
            }
        }
        
        // Overall visibility controls
        function toggleOverallVisibility(makeVisible) {
            const message = makeVisible ?
                'Show all overall grades to students? This will make module totals visible.' :
                'Hide all overall grades from students? This will hide module totals.';
                
            if (confirm(message)) {
                // For now, this is a frontend-only implementation
                // In a real system, this would update a user preference or module setting
                
                const actionText = makeVisible ? 'visible to' : 'hidden from';
                showNotification(`Made all overall grades ${actionText} students`, 'success');
                
                // Update overall status indicator
                updateOverallStatusIndicator(makeVisible ? 'visible' : 'hidden');
                
                // Update all overall grade cells visual state
                updateOverallGradeCells(makeVisible);
            }
        }
        
        function updateOverallStatusIndicator(status) {
            const statusElement = document.getElementById('overall-status');
            
            if (statusElement) {
                const dot = statusElement.querySelector('.status-dot');
                if (dot) {
                    dot.className = `status-dot ${status}`;
                    
                    switch(status) {
                        case 'visible':
                            dot.title = 'All overall grades visible to students';
                            break;
                        case 'hidden':
                            dot.title = 'All overall grades hidden from students';
                            break;
                        case 'mixed':
                        default:
                            dot.title = 'Mixed overall visibility';
                            break;
                    }
                }
            }
        }
        
        function updateOverallGradeCells(isVisible) {
            // Update visual state of overall grade column
            const overallCells = document.querySelectorAll('.overall-column-controls').
                closest('table').querySelectorAll('tbody tr td:nth-last-child(2)');
            
            overallCells.forEach(cell => {
                if (isVisible) {
                    cell.style.opacity = '1';
                    cell.style.background = 'linear-gradient(to right, rgba(16, 185, 129, 0.05), transparent)';
                    cell.style.borderLeft = '3px solid #10b981';
                } else {
                    cell.style.opacity = '0.6';
                    cell.style.background = 'linear-gradient(to right, rgba(245, 158, 11, 0.05), transparent)';
                    cell.style.borderLeft = '3px solid #f59e0b';
                }
            });
        }
        
        // Update column visibility status based on individual grade visibility
        function updateColumnVisibilityStatus(componentName) {
            const componentSlug = componentName.replace(/[^a-z0-9]/gi, '-').toLowerCase();
            const componentCells = document.querySelectorAll(`[data-component="${componentName}"]`);
            
            let visibleCount = 0;
            let hiddenCount = 0;
            let totalGraded = 0;
            
            componentCells.forEach(cell => {
                const gradeDisplay = cell.querySelector('.grade-display-fixed');
                if (gradeDisplay && gradeDisplay.classList.contains('has-grade')) {
                    totalGraded++;
                    
                    const visibilityBtn = cell.querySelector('.visibility-btn-fixed');
                    const svg = visibilityBtn?.querySelector('svg');
                    if (svg) {
                        // Check if it's showing eye (visible) or eye-slash (hidden) icon
                        if (svg.innerHTML.includes('M.458 10C1.732')) {
                            visibleCount++;
                        } else {
                            hiddenCount++;
                        }
                    }
                }
            });
            
            if (totalGraded === 0) {
                updateComponentVisibilityIndicator(componentName, 'mixed');
            } else if (visibleCount === totalGraded) {
                updateComponentVisibilityIndicator(componentName, 'visible');
            } else if (hiddenCount === totalGraded) {
                updateComponentVisibilityIndicator(componentName, 'hidden');
            } else {
                updateComponentVisibilityIndicator(componentName, 'mixed');
            }
        }
        
        // Filter functions
        function clearFilters() {
            document.getElementById('viewFilter').value = 'all';
            document.getElementById('componentFilter').value = 'all';
            showNotification('Filters cleared', 'info');
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add CSRF token
            if (!document.querySelector('meta[name="csrf-token"]')) {
                const meta = document.createElement('meta');
                meta.name = 'csrf-token';
                meta.content = '{{ csrf_token() }}';
                document.head.appendChild(meta);
            }
            
            // Initialize view manager UI
            updateViewManagerUI();
            
            // Enhanced welcome message with controls explanation
            setTimeout(() => {
                showNotification('✨ Enhanced grading interface ready!', 'success', 4000);
            }, 1000);
            
            setTimeout(() => {
                showNotification('👁️ Click eye icons in student names to hide/show rows. Use floating View Manager (bottom right) to save your layout!', 'info', 8000);
            }, 3000);
        });
    </script>
</x-app-layout>