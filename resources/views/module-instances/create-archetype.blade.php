{{-- resources/views/module-instances/create-archetype.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Module Instance
            </h2>
            <div class="text-sm text-gray-600">
                <a href="{{ route('module-instances.create-legacy') }}" class="hover:text-indigo-600">
                    Use Legacy Form
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Instance Type Selection -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Step 1: Select Instance Type</h3>
                    <p class="text-sm text-gray-600 mb-6">Choose how this module will be delivered based on your programme archetype.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="instance-type-selection">
                        <!-- Cohort-Based Instance -->
                        <div class="instance-type-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-300 transition-colors"
                             data-type="cohort">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Cohort-Based</h4>
                                    <p class="text-sm text-gray-500">Traditional cohort delivery</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Fixed start/end dates with a specific cohort of students. Ideal for QQI programmes with placement requirements.</p>
                            <div class="text-xs text-gray-500">
                                <div>✓ Structured timeline</div>
                                <div>✓ Group learning</div>
                                <div>✓ Teacher-led delivery</div>
                            </div>
                        </div>

                        <!-- Rolling Enrollment -->
                        <div class="instance-type-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-300 transition-colors"
                             data-type="rolling">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Rolling Enrollment</h4>
                                    <p class="text-sm text-gray-500">Continuous intake</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Students can join at any time within enrollment windows. Perfect for flexible learning and competency-based modules.</p>
                            <div class="text-xs text-gray-500">
                                <div>✓ Flexible start dates</div>
                                <div>✓ Individual progression</div>
                                <div>✓ Self-paced options</div>
                            </div>
                        </div>

                        <!-- Academic Term -->
                        <div class="instance-type-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-300 transition-colors"
                             data-type="academic_term">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Academic Term</h4>
                                    <p class="text-sm text-gray-500">University-style terms</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Aligned with academic calendar terms (Semester 1, 2, etc.). Ideal for degree programmes with credit accumulation.</p>
                            <div class="text-xs text-gray-500">
                                <div>✓ Calendar alignment</div>
                                <div>✓ Credit-based progression</div>
                                <div>✓ Academic standards</div>
                            </div>
                        </div>

                        <!-- Standalone Module -->
                        <div class="instance-type-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-300 transition-colors"
                             data-type="standalone">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Standalone</h4>
                                    <p class="text-sm text-gray-500">Independent module</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Independent module delivery not tied to a programme. Suitable for CPD, short courses, or skill-specific training.</p>
                            <div class="text-xs text-gray-500">
                                <div>✓ Programme independent</div>
                                <div>✓ Flexible scheduling</div>
                                <div>✓ Micro-credentials</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Instance Configuration Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="instance-form-section" style="display: none;">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Step 2: Configure Module Instance</h3>
                    
                    <form method="POST" action="{{ route('module-instances.store') }}" id="instance-form">
                        @csrf
                        <input type="hidden" name="instance_type" id="selected_instance_type">

                        <!-- Module Selection -->
                        <div class="mb-6">
                            <label for="module_id" class="block text-sm font-medium text-gray-700">Module *</label>
                            <select name="module_id" id="module_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select a module</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" 
                                        data-code="{{ $module->code }}"
                                        data-title="{{ $module->title }}"
                                        data-credits="{{ $module->credits ?? 15 }}"
                                        data-duration="{{ $module->duration_weeks ?? 12 }}"
                                        {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                        {{ $module->code }} - {{ $module->title }}
                                        @if($module->credits) ({{ $module->credits }} credits) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('module_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dynamic Configuration Sections -->
                        
                        <!-- Cohort-Based Configuration -->
                        <div id="cohort-config" class="config-section" style="display: none;">
                            <h4 class="font-medium text-gray-900 mb-4">Cohort Configuration</h4>
                            
                            <div class="mb-6">
                                <label for="cohort_id" class="block text-sm font-medium text-gray-700">Cohort *</label>
                                <select name="cohort_id" id="cohort_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select a cohort</option>
                                    @foreach($cohorts as $cohort)
                                        <option value="{{ $cohort->id }}" 
                                            data-programme="{{ $cohort->programme->code }}"
                                            data-archetype="{{ $cohort->programme->programmeType?->code }}"
                                            data-start="{{ $cohort->start_date->format('Y-m-d') }}"
                                            data-end="{{ $cohort->end_date?->format('Y-m-d') }}"
                                            {{ old('cohort_id') == $cohort->id ? 'selected' : '' }}>
                                            {{ $cohort->programme->code }} - {{ $cohort->code }} - {{ $cohort->name }}
                                            @if($cohort->programme->programmeType)
                                                ({{ $cohort->programme->programmeType->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Rolling Enrollment Configuration -->
                        <div id="rolling-config" class="config-section" style="display: none;">
                            <h4 class="font-medium text-gray-900 mb-4">Rolling Enrollment Configuration</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="max_enrollments" class="block text-sm font-medium text-gray-700">Max Enrollments</label>
                                    <input type="number" name="max_enrollments" id="max_enrollments" value="{{ old('max_enrollments', 25) }}" min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                                
                                <div>
                                    <label for="enrollment_open_date" class="block text-sm font-medium text-gray-700">Enrollment Opens</label>
                                    <input type="date" name="enrollment_open_date" id="enrollment_open_date" value="{{ old('enrollment_open_date') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                                
                                <div>
                                    <label for="enrollment_close_date" class="block text-sm font-medium text-gray-700">Enrollment Closes</label>
                                    <input type="date" name="enrollment_close_date" id="enrollment_close_date" value="{{ old('enrollment_close_date') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                            </div>
                            
                            <div class="flex items-center mb-6">
                                <input type="checkbox" name="self_paced" id="self_paced" value="1" {{ old('self_paced') ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="self_paced" class="ml-2 block text-sm text-gray-900">
                                    Self-paced learning (students can progress at their own speed)
                                </label>
                            </div>
                            
                            <div>
                                <label for="flexible_duration_weeks" class="block text-sm font-medium text-gray-700">Flexible Duration (weeks)</label>
                                <input type="number" name="flexible_duration_weeks" id="flexible_duration_weeks" value="{{ old('flexible_duration_weeks') }}" min="1"
                                    placeholder="Leave blank for module default duration"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <p class="mt-1 text-xs text-gray-500">Override the default module duration for this instance</p>
                            </div>
                        </div>

                        <!-- Academic Term Configuration -->
                        <div id="academic-term-config" class="config-section" style="display: none;">
                            <h4 class="font-medium text-gray-900 mb-4">Academic Term Configuration</h4>
                            
                            <div class="mb-6">
                                <label for="academic_term_id" class="block text-sm font-medium text-gray-700">Academic Term</label>
                                <select name="academic_term_id" id="academic_term_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select academic term</option>
                                    @foreach($academicTerms as $term)
                                        <option value="{{ $term->id }}" 
                                            data-start="{{ $term->start_date->format('Y-m-d') }}"
                                            data-end="{{ $term->end_date->format('Y-m-d') }}"
                                            {{ old('academic_term_id') == $term->id ? 'selected' : '' }}>
                                            {{ $term->name }} ({{ $term->start_date->format('M Y') }} - {{ $term->end_date->format('M Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Module instance will align with the selected academic term dates</p>
                            </div>
                        </div>

                        <!-- Common Configuration -->
                        <div class="border-t pt-6 mt-6">
                            <h4 class="font-medium text-gray-900 mb-4">General Configuration</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Start Date -->
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('start_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date *</label>
                                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Teacher -->
                                <div>
                                    <label for="teacher_id" class="block text-sm font-medium text-gray-700">Teacher</label>
                                    <select name="teacher_id" id="teacher_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select teacher (optional)</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                                {{ $teacher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('teacher_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                    <select name="status" id="status" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="planned" {{ old('status') === 'planned' ? 'selected' : '' }}>Planned</option>
                                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between">
                            <button type="button" id="back-to-types" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                ← Back to Instance Types
                            </button>
                            <div>
                                <a href="{{ route('module-instances.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Create Instance
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeCards = document.querySelectorAll('.instance-type-card');
            const formSection = document.getElementById('instance-form-section');
            const selectedInstanceType = document.getElementById('selected_instance_type');
            const backButton = document.getElementById('back-to-types');
            const configSections = document.querySelectorAll('.config-section');

            // Instance type selection
            typeCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selection from other cards
                    typeCards.forEach(c => c.classList.remove('border-indigo-500', 'bg-indigo-50'));
                    
                    // Select this card
                    this.classList.add('border-indigo-500', 'bg-indigo-50');
                    
                    // Get instance type
                    const instanceType = this.dataset.type;
                    selectedInstanceType.value = instanceType;
                    
                    // Show appropriate configuration
                    showConfigurationForType(instanceType);
                    
                    // Show form section
                    formSection.style.display = 'block';
                    formSection.scrollIntoView({ behavior: 'smooth' });
                });
            });

            // Back to types
            backButton.addEventListener('click', function() {
                formSection.style.display = 'none';
                typeCards.forEach(c => c.classList.remove('border-indigo-500', 'bg-indigo-50'));
            });

            function showConfigurationForType(type) {
                // Hide all config sections
                configSections.forEach(section => section.style.display = 'none');
                
                // Show relevant config section
                const configSection = document.getElementById(type + '-config');
                if (configSection) {
                    configSection.style.display = 'block';
                }
                
                // Set required fields based on type
                updateRequiredFields(type);
            }

            function updateRequiredFields(type) {
                // Remove required from all type-specific fields
                document.querySelectorAll('.config-section input, .config-section select').forEach(field => {
                    field.required = false;
                });
                
                // Add required to relevant fields
                if (type === 'cohort') {
                    document.getElementById('cohort_id').required = true;
                } else if (type === 'rolling') {
                    document.getElementById('max_enrollments').required = true;
                } else if (type === 'academic_term') {
                    document.getElementById('academic_term_id').required = true;
                }
            }

            // Auto-populate dates based on selections
            document.getElementById('cohort_id')?.addEventListener('change', function() {
                if (this.value) {
                    const option = this.selectedOptions[0];
                    document.getElementById('start_date').value = option.dataset.start;
                    document.getElementById('end_date').value = option.dataset.end;
                }
            });

            document.getElementById('academic_term_id')?.addEventListener('change', function() {
                if (this.value) {
                    const option = this.selectedOptions[0];
                    document.getElementById('start_date').value = option.dataset.start;
                    document.getElementById('end_date').value = option.dataset.end;
                }
            });

            // Auto-calculate end date for rolling instances
            document.getElementById('module_id')?.addEventListener('change', function() {
                if (this.value && selectedInstanceType.value === 'rolling') {
                    const option = this.selectedOptions[0];
                    const durationWeeks = parseInt(option.dataset.duration) || 12;
                    
                    const startDate = document.getElementById('start_date').value;
                    if (startDate) {
                        const start = new Date(startDate);
                        const end = new Date(start.getTime() + (durationWeeks * 7 * 24 * 60 * 60 * 1000));
                        document.getElementById('end_date').value = end.toISOString().split('T')[0];
                    }
                }
            });
        });
    </script>
</x-app-layout>