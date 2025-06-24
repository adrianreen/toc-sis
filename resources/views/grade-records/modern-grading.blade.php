<x-app-layout>
<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modern Grading Interface - {{ $moduleInstance->module->title }}
        </h2>
    </div>
</x-slot>

<div class="py-6">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">

                <!-- Modern Grading Interface -->
                <div x-data="modernGradingApp()" x-init="init()">
                    
                    <!-- Header and Master Controls -->
                    <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $moduleInstance->module->title }}</h1>
                            <p class="text-sm text-gray-500">
                                {{ $moduleInstance->module->code }} • {{ $moduleInstance->tutor->name ?? 'No Tutor Assigned' }} • {{ $enrolledStudents->count() }} Students
                            </p>
                        </div>
                        <div class="flex items-center space-x-6">
                            <!-- Heatmap Toggle -->
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-600">Heatmap</span>
                                <button 
                                    @click="toggleHeatmap()" 
                                    :class="heatmapOn ? 'bg-blue-600' : 'bg-gray-200'" 
                                    type="button" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out" 
                                    role="switch">
                                    <span :class="heatmapOn ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition duration-200 ease-in-out"></span>
                                </button>
                            </div>
                            
                            <!-- Bulk Actions -->
                            <div class="flex items-center space-x-3">
                                <button 
                                    @click="saveAllGrades()" 
                                    :disabled="!hasUnsavedChanges"
                                    :class="hasUnsavedChanges ? 'bg-green-600 hover:bg-green-700 cursor-pointer' : 'bg-gray-400 cursor-not-allowed'"
                                    class="px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors">
                                    <span x-show="!saving">Save All Changes</span>
                                    <span x-show="saving">Saving...</span>
                                </button>
                                
                                <div class="relative">
                                    <button 
                                        @click="bulkMenuOpen = !bulkMenuOpen"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
                                        Bulk Actions
                                    </button>
                                    <div x-show="bulkMenuOpen" @click.away="bulkMenuOpen = false" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-10 border">
                                        <div class="py-1">
                                            <button @click="showAllGrades()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left cursor-pointer">
                                                <span class="font-medium">Show All Grades</span>
                                                <span class="text-xs text-gray-500 block">Make all graded assessments visible</span>
                                            </button>
                                            <button @click="hideAllGrades()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left cursor-pointer">
                                                <span class="font-medium">Hide All Grades</span>
                                                <span class="text-xs text-gray-500 block">Hide all grades from students</span>
                                            </button>
                                            <div class="border-t border-gray-100"></div>
                                            <button @click="showAllOverallGrades()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left cursor-pointer">
                                                <span class="font-medium">Show All Overall Grades</span>
                                                <span class="text-xs text-gray-500 block">Show calculated results to students</span>
                                            </button>
                                            <button @click="hideAllOverallGrades()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left cursor-pointer">
                                                <span class="font-medium">Hide All Overall Grades</span>
                                                <span class="text-xs text-gray-500 block">Hide calculated results from students</span>
                                            </button>
                                            <div class="border-t border-gray-100"></div>
                                            <button @click="exportGrades()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left cursor-pointer">
                                                <span class="font-medium">Export Grades</span>
                                                <span class="text-xs text-gray-500 block">Download as spreadsheet</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <!-- Statistics Bar -->
                    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white border p-4 rounded-lg shadow">
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['students'] }}</div>
                            <div class="text-sm text-gray-500">Students</div>
                        </div>
                        <div class="bg-white border p-4 rounded-lg shadow">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['graded'] }}</div>
                            <div class="text-sm text-gray-500">Graded</div>
                        </div>
                        <div class="bg-white border p-4 rounded-lg shadow">
                            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                            <div class="text-sm text-gray-500">Pending</div>
                        </div>
                        <div class="bg-white border p-4 rounded-lg shadow">
                            <div class="text-2xl font-bold text-purple-600">{{ $stats['visible'] }}</div>
                            <div class="text-sm text-gray-500">Visible to Students</div>
                        </div>
                    </div>

                    <!-- The Gradebook Table -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="sticky left-0 bg-gray-50 z-10 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[250px]">
                                        <div class="flex items-center justify-between">
                                            <span>Student</span>
                                            <button 
                                                @click="toggleAllStudentVisibility()" 
                                                title="Toggle visibility for all students"
                                                class="cursor-pointer hover:scale-110 transition-transform ml-2">
                                                <svg x-show="allStudentsVisible" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 6 0z" />
                                                </svg>
                                                <svg x-show="!allStudentsVisible" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" />
                                                </svg>
                                            </button>
                                        </div>
                                    </th>
                                    
                                    @foreach($assessmentComponents as $component)
                                    <th scope="col" class="w-48 px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-center space-x-2">
                                            <span>{{ $component['component_name'] }}</span>
                                            <button 
                                                data-component="{{ htmlspecialchars($component['component_name'], ENT_QUOTES) }}"
                                                @click="toggleComponentVisibility($el.dataset.component)" 
                                                :title="'Toggle student visibility for ' + '{{ htmlspecialchars($component['component_name'], ENT_QUOTES) }}'"
                                                class="cursor-pointer hover:scale-110 transition-transform">
                                                <svg x-show="componentVisibility['{{ $component['component_name'] }}']" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 6 0z" />
                                                </svg>
                                                <svg x-show="!componentVisibility['{{ $component['component_name'] }}']" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="font-normal text-gray-400">
                                            Weight: {{ $component['weighting'] }}% • /100
                                            @if($component['is_must_pass'])
                                                <span class="text-red-400">• Must Pass</span>
                                            @endif
                                        </div>
                                    </th>
                                    @endforeach

                                    <!-- Overall Grade Header - Always visible to admins, controls student visibility -->
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center justify-end space-x-2">
                                            <span>Overall Grade</span>
                                            <button 
                                                @click="toggleOverallVisibilityToStudents()" 
                                                title="Toggle overall grade visibility for students"
                                                class="cursor-pointer hover:scale-110 transition-transform">
                                                <svg x-show="overallVisibleToStudents" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 6 0z" />
                                                </svg>
                                                <svg x-show="!overallVisibleToStudents" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="font-normal text-gray-400">
                                            <span x-show="overallVisibleToStudents" class="text-green-600">Students Can See</span>
                                            <span x-show="!overallVisibleToStudents" class="text-red-500">Hidden from Students</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            
                            <tbody class="bg-white divide-y divide-gray-200" :class="{ 'heatmap-on': heatmapOn }">
                                @foreach($enrolledStudents as $student)
                                @php
                                    $studentGrades = $groupedGradeRecords->get($student->id, collect());
                                    $overallGrade = calculateOverallGrade($studentGrades, $assessmentComponents);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    
                                    <!-- Student Name (Sticky) with Row Visibility Control -->
                                    <td class="sticky left-0 bg-white px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 z-10">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium">{{ $student->full_name }}</div>
                                                <div class="text-gray-500 text-xs">{{ $student->student_number }}</div>
                                            </div>
                                            <button 
                                                @click="toggleStudentRowVisibility({{ $student->id }})" 
                                                title="Toggle all grades visibility for this student"
                                                class="cursor-pointer hover:scale-110 transition-transform ml-2">
                                                <svg x-show="studentRowVisibility[{{ $student->id }}] || false" class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 6 0z" />
                                                </svg>
                                                <svg x-show="!studentRowVisibility[{{ $student->id }}]" class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    
                                    @foreach($assessmentComponents as $component)
                                    @php
                                        $gradeRecord = $studentGrades->where('assessment_component_name', $component['component_name'])->first();
                                        $percentage = $gradeRecord?->percentage ?? null;
                                        $gradeClass = getGradeClass($percentage);
                                    @endphp
                                    <td class="grade-cell {{ $gradeClass }}" 
                                        :title="getGradeTooltip({{ $percentage ?? 'null' }})">
                                        <div class="relative">
                                            <!-- Individual Component Visibility Control - Top Left -->
                                            <button 
                                                data-student-id="{{ $student->id }}"
                                                data-component="{{ htmlspecialchars($component['component_name'], ENT_QUOTES) }}"
                                                @click="toggleIndividualComponentVisibility($el.dataset.studentId, $el.dataset.component)"
                                                title="Toggle visibility of this grade for this student"
                                                class="absolute top-1 left-1 z-10 cursor-pointer hover:scale-110 transition-transform bg-white rounded-full p-1 shadow-sm border border-gray-200">
                                                <svg x-show="individualComponentVisibility[{{ $student->id }}]?.['{{ $component['component_name'] }}'] || false" class="h-3 w-3 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 6 0z" />
                                                </svg>
                                                <svg x-show="!individualComponentVisibility[{{ $student->id }}]?.['{{ $component['component_name'] }}']" class="h-3 w-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" />
                                                </svg>
                                            </button>
                                            <input 
                                                type="number" 
                                                min="0" 
                                                max="100"
                                                step="0.1"
                                                value="{{ $gradeRecord?->grade ?? '' }}"
                                                data-student-id="{{ $student->id }}"
                                                data-component="{{ htmlspecialchars($component['component_name'], ENT_QUOTES) }}"
                                                data-grade-record-id="{{ $gradeRecord?->id ?? 'null' }}"
                                                @input="updateGrade($el.dataset.studentId, $el.dataset.component, $event.target.value, $el.dataset.gradeRecordId)"
                                                class="w-full h-full p-2 pl-8 text-center bg-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white rounded-md"
                                                placeholder="—">
                                        </div>
                                    </td>
                                    @endforeach

                                    <!-- Overall Grade - Always visible to admins -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold">
                                        <div class="flex items-center justify-end space-x-3 group">
                                            <!-- Admin always sees the grade -->
                                            <div class="text-gray-900">
                                                @if($overallGrade)
                                                    {{ number_format($overallGrade['percentage'], 1) }}% 
                                                    <span class="{{ $overallGrade['class'] }}">{{ $overallGrade['band'] }}</span>
                                                @else
                                                    <span class="text-gray-400">Not Graded</span>
                                                @endif
                                            </div>
                                            <!-- Individual student overall grade visibility control -->
                                            <button 
                                                @click="toggleIndividualOverallVisibility({{ $student->id }})"
                                                title="Toggle overall grade visibility for this student"
                                                class="opacity-70 group-hover:opacity-100 transition-opacity cursor-pointer hover:scale-110">
                                                <svg x-show="individualOverallVisibility[{{ $student->id }}] || false" class="h-4 w-4 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 6 0z" />
                                                </svg>
                                                <svg x-show="!individualOverallVisibility[{{ $student->id }}]" class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243l-4.243-4.243" />
                                                </svg>
                                            </button>
                                            <!-- Global visibility indicator (smaller) -->
                                            <div class="text-xs opacity-50">
                                                <span x-show="overallVisibleToStudents" class="text-green-600" title="Global: Students can see overall grades">👁️</span>
                                                <span x-show="!overallVisibleToStudents" class="text-red-500" title="Global: Students cannot see overall grades">🚫</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- QQI Legend -->
                    <footer class="mt-6 text-sm text-gray-600">
                        <h3 class="font-semibold text-gray-700 mb-2">QQI FET Grading Bands</h3>
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                            <div class="flex items-center space-x-2">
                                <span class="block w-4 h-4 grade-distinction heatmap-on rounded"></span>
                                <span>Distinction (80% - 100%)</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="block w-4 h-4 grade-merit heatmap-on rounded"></span>
                                <span>Merit (65% - 79%)</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="block w-4 h-4 grade-pass heatmap-on rounded"></span>
                                <span>Pass (50% - 64%)</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="block w-4 h-4 grade-unsuccessful heatmap-on rounded"></span>
                                <span>Unsuccessful (0% - 49%)</span>
                            </div>
                        </div>
                    </footer>

                    <!-- Loading Overlay -->
                    <div x-show="saving" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg">
                            <div class="flex items-center space-x-3">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <span class="text-lg font-medium">Saving grades...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <div x-show="showMessage" x-transition class="fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50"
                         :class="messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'">
                        <span x-text="messageText"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* QQI Grade Band Colors */
    .grade-cell { 
        position: relative; 
        transition: background-color 0.2s ease-in-out; 
    }

    /* Distinction: 80-100% */
    .grade-distinction { border-left: 4px solid #22c55e; }
    .heatmap-on .grade-distinction { background-color: #f0fdf4; }

    /* Merit: 65-79% */
    .grade-merit { border-left: 4px solid #3b82f6; }
    .heatmap-on .grade-merit { background-color: #eff6ff; }

    /* Pass: 50-64% */
    .grade-pass { border-left: 4px solid #f59e0b; }
    .heatmap-on .grade-pass { background-color: #fffbeb; }

    /* Unsuccessful: < 50% */
    .grade-unsuccessful { border-left: 4px solid #ef4444; }
    .heatmap-on .grade-unsuccessful { background-color: #fef2f2; }

    /* Ungraded */
    .grade-ungraded { border-left: 4px solid transparent; }

    /* Sticky header improvements */
    .sticky {
        position: sticky;
        z-index: 20;
    }
</style>

<script>
function modernGradingApp() {
    return {
        // State
        heatmapOn: true,
        saving: false,
        hasUnsavedChanges: false,
        pendingChanges: {},
        bulkMenuOpen: false,
        componentVisibility: {},
        overallVisibleToStudents: false, // Controls student visibility of overall grades
        studentRowVisibility: {}, // Controls all grades visibility per student
        individualComponentVisibility: {}, // Controls individual component visibility per student
        individualOverallVisibility: {}, // Controls individual overall grade visibility per student
        allStudentsVisible: true, // Master control for all students
        showMessage: false,
        messageText: '',
        messageType: 'success',
        
        init() {
            console.log('Modern grading app initialized');
            
            // Initialize component visibility from server data
            @foreach($assessmentComponents as $component)
                this.componentVisibility['{{ $component['component_name'] }}'] = this.calculateComponentVisibility('{{ $component['component_name'] }}');
            @endforeach
            
            // Initialize overall grade visibility to students based on whether any grades are visible
            const gradeRecords = @json($groupedGradeRecords);
            let anyVisible = false;
            
            // Initialize per-student visibility controls
            @foreach($enrolledStudents as $student)
                @php $studentGrades = $groupedGradeRecords->get($student->id, collect()); @endphp
                
                // Initialize student row visibility (based on whether any grades are visible for this student)
                this.studentRowVisibility[{{ $student->id }}] = {{ $studentGrades->where('is_visible_to_student', true)->count() > 0 ? 'true' : 'false' }};
                
                // Initialize individual component visibility for this student
                this.individualComponentVisibility[{{ $student->id }}] = {};
                @foreach($assessmentComponents as $component)
                    @php 
                        $componentRecord = $studentGrades->where('assessment_component_name', $component['component_name'])->first();
                        $isComponentVisible = $componentRecord ? $componentRecord->is_visible_to_student : false;
                    @endphp
                    this.individualComponentVisibility[{{ $student->id }}]['{{ $component['component_name'] }}'] = {{ $isComponentVisible ? 'true' : 'false' }};
                @endforeach
                
                // Initialize individual overall visibility (same as row visibility for now)
                this.individualOverallVisibility[{{ $student->id }}] = {{ $studentGrades->where('is_visible_to_student', true)->count() > 0 ? 'true' : 'false' }};
                
                // Check if any student has visible grades for global state
                if ({{ $studentGrades->where('is_visible_to_student', true)->count() > 0 ? 'true' : 'false' }}) {
                    anyVisible = true;
                }
            @endforeach
            
            this.overallVisibleToStudents = anyVisible;
            
            // Calculate if all students are visible
            this.allStudentsVisible = Object.values(this.studentRowVisibility).every(visible => visible);

            console.log('Component visibility initialized:', this.componentVisibility);
            console.log('Student row visibility initialized:', this.studentRowVisibility);
            console.log('Individual component visibility initialized:', this.individualComponentVisibility);
            console.log('Individual overall visibility initialized:', this.individualOverallVisibility);
            console.log('Overall visibility to students:', this.overallVisibleToStudents);
        },

        toggleHeatmap() {
            this.heatmapOn = !this.heatmapOn;
            console.log('Heatmap toggled:', this.heatmapOn);
        },

        calculateComponentVisibility(componentName) {
            const gradeRecords = @json($groupedGradeRecords);
            for (const studentId in gradeRecords) {
                const grades = gradeRecords[studentId];
                for (const grade of grades) {
                    if (grade.assessment_component_name === componentName && grade.is_visible_to_student) {
                        return true;
                    }
                }
            }
            return false;
        },

        updateGrade(studentId, componentName, value, gradeRecordId) {
            const key = `${studentId}-${componentName}`;
            this.pendingChanges[key] = {
                student_id: studentId,
                component_name: componentName,
                grade: value,
                grade_record_id: gradeRecordId
            };
            this.hasUnsavedChanges = true;
            console.log('Grade updated:', key, value);
        },

        async saveAllGrades() {
            if (!this.hasUnsavedChanges) return;
            
            console.log('Starting grade save process...');
            console.log('Pending changes:', this.pendingChanges);
            
            this.saving = true;
            this.bulkMenuOpen = false;
            
            try {
                const requestData = {
                    grades: Object.values(this.pendingChanges)
                };
                
                console.log('Sending request data:', requestData);
                
                const response = await fetch('{{ route("grade-records.modern-bulk-update", $moduleInstance) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Response data:', data);
                
                if (data.success) {
                    this.pendingChanges = {};
                    this.hasUnsavedChanges = false;
                    this.showNotification('Grades saved successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    this.showNotification('Error saving grades: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error saving grades:', error);
                this.showNotification('Network error while saving grades: ' + error.message, 'error');
            }
            
            this.saving = false;
        },

        async toggleComponentVisibility(componentName) {
            console.log('Toggling component visibility for:', componentName);
            const currentVisibility = this.componentVisibility[componentName] || false;
            const newVisibility = !currentVisibility;
            
            console.log('Current visibility:', currentVisibility, 'New visibility:', newVisibility);
            
            try {
                const requestData = {
                    component_name: componentName,
                    visible: newVisibility
                };
                
                console.log('Sending visibility request:', requestData);
                
                const response = await fetch('{{ route("grade-records.toggle-visibility", $moduleInstance) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                console.log('Visibility response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Visibility response data:', data);
                
                if (data.success) {
                    this.componentVisibility[componentName] = newVisibility;
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification('Error updating visibility: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error toggling component visibility:', error);
                this.showNotification('Network error: ' + error.message, 'error');
            }
        },

        async toggleOverallVisibilityToStudents() {
            console.log('Toggling overall grade visibility to students');
            const newVisibility = !this.overallVisibleToStudents;
            
            try {
                const requestData = {
                    visible: newVisibility
                };
                
                console.log('Sending overall visibility request:', requestData);
                
                const response = await fetch('{{ route("grade-records.toggle-overall-visibility", $moduleInstance) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                console.log('Overall visibility response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Overall visibility response data:', data);
                
                if (data.success) {
                    this.overallVisibleToStudents = newVisibility;
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification('Error updating overall visibility: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error toggling overall visibility:', error);
                this.showNotification('Network error: ' + error.message, 'error');
            }
        },

        // NEW GRANULAR VISIBILITY CONTROLS

        async toggleAllStudentVisibility() {
            console.log('Toggling visibility for all students');
            const newVisibility = !this.allStudentsVisible;
            
            if (!confirm(`${newVisibility ? 'Show' : 'Hide'} all grades for all students?`)) {
                return;
            }
            
            try {
                const requestData = {
                    visible: newVisibility
                };
                
                const response = await fetch('{{ route("grade-records.toggle-visibility", $moduleInstance) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    // Update all student row visibility
                    @foreach($enrolledStudents as $student)
                        this.studentRowVisibility[{{ $student->id }}] = newVisibility;
                        this.individualOverallVisibility[{{ $student->id }}] = newVisibility;
                        
                        // Update all individual component visibility for this student
                        @foreach($assessmentComponents as $component)
                            this.individualComponentVisibility[{{ $student->id }}]['{{ $component['component_name'] }}'] = newVisibility;
                        @endforeach
                    @endforeach
                    
                    this.allStudentsVisible = newVisibility;
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification('Error updating visibility: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error toggling all student visibility:', error);
                this.showNotification('Network error: ' + error.message, 'error');
            }
        },

        async toggleStudentRowVisibility(studentId) {
            console.log('Toggling row visibility for student:', studentId);
            const currentVisibility = this.studentRowVisibility[studentId];
            const newVisibility = !currentVisibility;
            
            try {
                const requestData = {
                    student_id: studentId,
                    visible: newVisibility
                };
                
                const response = await fetch('{{ route("grade-records.toggle-student-visibility", $moduleInstance) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    this.studentRowVisibility[studentId] = newVisibility;
                    this.individualOverallVisibility[studentId] = newVisibility;
                    
                    // Update all individual component visibility for this student
                    @foreach($assessmentComponents as $component)
                        this.individualComponentVisibility[studentId]['{{ $component['component_name'] }}'] = newVisibility;
                    @endforeach
                    
                    // Update master toggle
                    this.allStudentsVisible = Object.values(this.studentRowVisibility).every(visible => visible);
                    
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification('Error updating student visibility: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error toggling student row visibility:', error);
                this.showNotification('Network error: ' + error.message, 'error');
            }
        },

        async toggleIndividualComponentVisibility(studentId, componentName) {
            console.log('Toggling individual component visibility:', studentId, componentName);
            const currentVisibility = this.individualComponentVisibility[studentId][componentName];
            const newVisibility = !currentVisibility;
            
            try {
                const requestData = {
                    student_id: studentId,
                    component_name: componentName,
                    visible: newVisibility
                };
                
                const response = await fetch('{{ route("grade-records.toggle-individual-component-visibility", $moduleInstance) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    this.individualComponentVisibility[studentId][componentName] = newVisibility;
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification('Error updating component visibility: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error toggling individual component visibility:', error);
                this.showNotification('Network error: ' + error.message, 'error');
            }
        },

        async toggleIndividualOverallVisibility(studentId) {
            console.log('Toggling individual overall visibility for student:', studentId);
            const currentVisibility = this.individualOverallVisibility[studentId];
            const newVisibility = !currentVisibility;
            
            try {
                const requestData = {
                    student_id: studentId,
                    visible: newVisibility
                };
                
                const response = await fetch('{{ route("grade-records.toggle-individual-overall-visibility", $moduleInstance) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    this.individualOverallVisibility[studentId] = newVisibility;
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification('Error updating individual overall visibility: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error toggling individual overall visibility:', error);
                this.showNotification('Network error: ' + error.message, 'error');
            }
        },

        async showAllGrades() {
            this.bulkMenuOpen = false;
            if (confirm('Make all graded assessments visible to students?')) {
                await this.bulkVisibilityUpdate(true, 'all grades');
            }
        },

        async hideAllGrades() {
            this.bulkMenuOpen = false;
            if (confirm('Hide all grades from students?')) {
                await this.bulkVisibilityUpdate(false, 'all grades');
            }
        },

        async showAllOverallGrades() {
            this.bulkMenuOpen = false;
            if (confirm('Make all overall grades visible to students?')) {
                await this.toggleOverallVisibilityToStudents();
            }
        },

        async hideAllOverallGrades() {
            this.bulkMenuOpen = false;
            if (confirm('Hide all overall grades from students?')) {
                if (this.overallVisibleToStudents) {
                    await this.toggleOverallVisibilityToStudents();
                }
            }
        },

        async bulkVisibilityUpdate(visible, type) {
            console.log('Bulk visibility update:', visible, type);
            
            try {
                const requestData = {
                    visible: visible
                };
                
                console.log('Sending bulk visibility request:', requestData);
                
                const response = await fetch('{{ route("grade-records.toggle-visibility", $moduleInstance) }}', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                console.log('Bulk visibility response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Bulk visibility response data:', data);
                
                if (data.success) {
                    // Update all component visibility states
                    @foreach($assessmentComponents as $component)
                        this.componentVisibility['{{ $component['component_name'] }}'] = visible;
                    @endforeach
                    
                    this.overallVisibleToStudents = visible;
                    this.showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    this.showNotification('Error updating visibility: ' + (data.message || 'Unknown error'), 'error');
                }
            } catch (error) {
                console.error('Error in bulk visibility update:', error);
                this.showNotification('Network error: ' + error.message, 'error');
            }
        },

        exportGrades() {
            this.bulkMenuOpen = false;
            window.open('{{ route("grade-records.export", $moduleInstance) }}', '_blank');
        },

        getGradeTooltip(percentage) {
            if (percentage === null) return 'Not graded';
            if (percentage >= 80) return `${percentage}% (Distinction)`;
            if (percentage >= 65) return `${percentage}% (Merit)`;
            if (percentage >= 50) return `${percentage}% (Pass)`;
            return `${percentage}% (Unsuccessful)`;
        },

        showNotification(message, type) {
            this.messageText = message;
            this.messageType = type;
            this.showMessage = true;
            
            setTimeout(() => {
                this.showMessage = false;
            }, 3000);
        }
    }
}
</script>

</x-app-layout>

@php
function getGradeClass($percentage) {
    if ($percentage === null) return 'grade-ungraded';
    if ($percentage >= 80) return 'grade-distinction';
    if ($percentage >= 65) return 'grade-merit';
    if ($percentage >= 50) return 'grade-pass';
    return 'grade-unsuccessful';
}

function calculateOverallGrade($studentGrades, $assessmentComponents) {
    $totalWeightedScore = 0;
    $totalWeighting = 0;
    
    foreach ($assessmentComponents as $component) {
        $gradeRecord = $studentGrades->where('assessment_component_name', $component['component_name'])->first();
        
        if ($gradeRecord && $gradeRecord->percentage !== null) {
            $totalWeightedScore += $gradeRecord->percentage * ($component['weighting'] / 100);
            $totalWeighting += $component['weighting'];
        }
    }
    
    if ($totalWeighting == 0) return null;
    
    $percentage = round($totalWeightedScore, 1);
    
    if ($percentage >= 80) {
        return ['percentage' => $percentage, 'band' => 'Distinction', 'class' => 'text-green-500'];
    } elseif ($percentage >= 65) {
        return ['percentage' => $percentage, 'band' => 'Merit', 'class' => 'text-blue-500'];
    } elseif ($percentage >= 50) {
        return ['percentage' => $percentage, 'band' => 'Pass', 'class' => 'text-amber-500'];
    } else {
        return ['percentage' => $percentage, 'band' => 'Unsuccessful', 'class' => 'text-red-500'];
    }
}
@endphp