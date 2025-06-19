{{-- resources/views/programmes/create-archetype.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Programme
            </h2>
            <div class="text-sm text-gray-600">
                <a href="{{ route('programmes.create-legacy') }}" class="hover:text-indigo-600">
                    Use Legacy Form
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Archetype Selection Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Step 1: Choose Programme Archetype</h3>
                    <p class="text-sm text-gray-600 mb-6">Programme archetypes provide pre-configured settings for different types of academic programmes.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="archetype-selection">
                        @foreach($programmeTypes as $type)
                            <div class="archetype-card border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-indigo-300 hover:shadow-lg transition-all relative"
                                 data-archetype-id="{{ $type->id }}"
                                 data-archetype-config="{{ htmlspecialchars(json_encode($type->createProgrammeDefaults()), ENT_QUOTES, 'UTF-8') }}"
                                 onclick="selectArchetype(this)"
                                
                                <!-- Selection indicator -->
                                <div class="archetype-select-text absolute top-2 right-2 opacity-0 transition-opacity">
                                    <div class="bg-indigo-500 text-white rounded-full p-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex items-center mb-3">
                                    <div class="archetype-icon w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mr-4">
                                        @if($type->code === 'QQI5')
                                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @elseif($type->code === 'QQI6')
                                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                            </svg>
                                        @elseif($type->code === 'DEGREE')
                                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $type->name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $type->awarding_body }}</p>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-4">{{ $type->description }}</p>
                                
                                <div class="text-xs text-gray-500 space-y-1">
                                    <div class="flex justify-between">
                                        <span>NFQ Level:</span>
                                        <span class="font-medium">{{ $type->nfq_level }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Duration:</span>
                                        <span class="font-medium">{{ $type->default_duration_months }} months</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Credits:</span>
                                        <span class="font-medium">{{ $type->default_credit_value }}</span>
                                    </div>
                                    @if($type->requires_placement)
                                        <div class="text-green-600">✓ Placement Required</div>
                                    @endif
                                    @if($type->requires_external_verification)
                                        <div class="text-blue-600">✓ External Verification</div>
                                    @endif
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <span class="archetype-select-text text-sm font-medium text-indigo-600 opacity-0 transition-opacity">
                                        Selected
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Next Button (appears after selection) -->
                    <div class="mt-8 text-center hidden" id="next-step-section">
                        <button type="button" onclick="proceedToForm()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
                            Continue with Selected Archetype →
                        </button>
                        <p class="mt-2 text-sm text-gray-600">Click to proceed to programme details</p>
                    </div>
                </div>
            </div>

            <!-- Programme Configuration Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="programme-form-section" style="display: none;">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Step 2: Configure Programme Details</h3>
                    
                    <form method="POST" action="{{ route('programmes.store') }}" id="programme-form">
                        @csrf
                        <input type="hidden" name="programme_type_id" id="selected_programme_type_id">

                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700">Programme Code *</label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}" required
                                    placeholder="e.g., ELC5, HSC, QQI6-SPEC"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Programme Title *</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                    placeholder="e.g., Early Learning & Care Level 5"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-8">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Configuration Preview -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-8" id="config-preview">
                            <h4 class="font-medium text-gray-900 mb-4">Archetype Configuration Preview</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Grading Scheme:</span>
                                    <span id="preview-grading" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Assessment Strategy:</span>
                                    <span id="preview-assessment" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Progression Rule:</span>
                                    <span id="preview-progression" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Enrolment Type:</span>
                                    <span id="preview-enrolment" class="text-gray-900"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Pass Grade:</span>
                                    <span id="preview-pass-grade" class="text-gray-900"></span>%
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Duration:</span>
                                    <span id="preview-duration" class="text-gray-900"></span> months
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Configuration (Collapsible) -->
                        <div class="border border-gray-200 rounded-lg">
                            <button type="button" class="w-full px-6 py-4 text-left font-medium text-gray-900 bg-gray-50 rounded-t-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    id="toggle-advanced-config">
                                <div class="flex items-center justify-between">
                                    <span>Advanced Configuration (Optional)</span>
                                    <svg class="w-5 h-5 transform transition-transform" id="advanced-config-icon">
                                        <path fill="currentColor" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                </div>
                            </button>
                            
                            <div class="px-6 py-4 border-t border-gray-200 hidden" id="advanced-config-section">
                                <!-- Hidden fields that will be populated by archetype selection -->
                                <input type="hidden" name="grading_scheme_id" id="grading_scheme_id">
                                <input type="hidden" name="assessment_strategy_id" id="assessment_strategy_id">
                                <input type="hidden" name="module_progression_rule_id" id="module_progression_rule_id">
                                <input type="hidden" name="enrolment_type" id="enrolment_type">
                                <input type="hidden" name="awarding_body" id="awarding_body">
                                <input type="hidden" name="nfq_level" id="nfq_level">
                                <input type="hidden" name="credit_value" id="credit_value">
                                <input type="hidden" name="minimum_pass_grade" id="minimum_pass_grade">
                                <input type="hidden" name="typical_duration_months" id="typical_duration_months">
                                <input type="hidden" name="delivery_mode" id="delivery_mode">
                                <input type="hidden" name="requires_placement" id="requires_placement">
                                <input type="hidden" name="requires_external_verification" id="requires_external_verification">
                                <input type="hidden" name="requires_portfolio_submission" id="requires_portfolio_submission">
                                <input type="hidden" name="external_examiner_required" id="external_examiner_required">
                                
                                <p class="text-sm text-gray-600 mb-4">Override archetype defaults for this specific programme.</p>
                                
                                <!-- Override controls would go here -->
                                <div class="text-sm text-gray-500">
                                    Advanced overrides available in full configuration mode.
                                    <a href="{{ route('programmes.create') }}" class="text-indigo-600 hover:text-indigo-800">Use full form</a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-between">
                            <button type="button" id="back-to-archetypes" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                ← Back to Archetypes
                            </button>
                            <div>
                                <a href="{{ route('programmes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Create Programme
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedConfig = null;
        
        function selectArchetype(card) {
            console.log('Archetype selected via onclick');
            
            // Remove selection from other cards
            document.querySelectorAll('.archetype-card').forEach(c => {
                c.classList.remove('border-indigo-500', 'bg-indigo-50');
                const selectTexts = c.querySelectorAll('.archetype-select-text');
                selectTexts.forEach(text => text.classList.add('opacity-0'));
            });
            
            // Select this card
            card.classList.add('border-indigo-500', 'bg-indigo-50');
            const selectTexts = card.querySelectorAll('.archetype-select-text');
            selectTexts.forEach(text => text.classList.remove('opacity-0'));
            
            // Get archetype configuration
            const archetypeId = card.dataset.archetypeId;
            try {
                selectedConfig = JSON.parse(card.dataset.archetypeConfig);
                console.log('Parsed config:', selectedConfig);
                
                // Store configuration for later use
                selectedConfig.programme_type_id = archetypeId;
                
                // Show next step button
                const nextStepSection = document.getElementById('next-step-section');
                if (nextStepSection) {
                    nextStepSection.classList.remove('hidden');
                    nextStepSection.scrollIntoView({ behavior: 'smooth' });
                    console.log('Next step section shown');
                } else {
                    console.error('Next step section not found');
                }
            } catch (e) {
                console.error('Error parsing archetype config:', e);
            }
        }
        
        function proceedToForm() {
            console.log('Proceed to form called');
            if (selectedConfig) {
                console.log('Populating form with config:', selectedConfig);
                // Populate form
                populateFormFromArchetype(selectedConfig);
                
                // Show form section
                const programmeFormSection = document.getElementById('programme-form-section');
                programmeFormSection.style.display = 'block';
                programmeFormSection.scrollIntoView({ behavior: 'smooth' });
                console.log('Form section shown');
            } else {
                console.error('No config selected');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing archetype selection...');
            
            const archetypeCards = document.querySelectorAll('.archetype-card');
            const programmeFormSection = document.getElementById('programme-form-section');
            const configPreview = document.getElementById('config-preview');
            const selectedProgrammeTypeId = document.getElementById('selected_programme_type_id');
            const backButton = document.getElementById('back-to-archetypes');
            const toggleAdvanced = document.getElementById('toggle-advanced-config');
            const advancedSection = document.getElementById('advanced-config-section');
            const advancedIcon = document.getElementById('advanced-config-icon');
            const nextStepSection = document.getElementById('next-step-section');
            const proceedButton = document.getElementById('proceed-to-form');
            
            console.log('Found elements:', {
                archetypeCards: archetypeCards.length,
                nextStepSection: !!nextStepSection,
                proceedButton: !!proceedButton
            });

            // Event listeners for form functionality

            // Back to archetypes
            if (backButton) {
                backButton.addEventListener('click', function() {
                    programmeFormSection.style.display = 'none';
                    nextStepSection.classList.add('hidden');
                    selectedConfig = null;
                    document.querySelectorAll('.archetype-card').forEach(c => {
                        c.classList.remove('border-indigo-500', 'bg-indigo-50');
                        const selectTexts = c.querySelectorAll('.archetype-select-text');
                        selectTexts.forEach(text => text.classList.add('opacity-0'));
                    });
                    document.getElementById('archetype-selection').scrollIntoView({ behavior: 'smooth' });
                });
            }

            // Toggle advanced configuration
            if (toggleAdvanced && advancedSection) {
                toggleAdvanced.addEventListener('click', function() {
                    advancedSection.classList.toggle('hidden');
                    if (advancedIcon) {
                        advancedIcon.classList.toggle('rotate-180');
                    }
                });
            }

            function populateFormFromArchetype(config) {
                // Set hidden form fields
                if (selectedProgrammeTypeId) {
                    selectedProgrammeTypeId.value = config.programme_type_id || '';
                }
                
                const fields = [
                    'grading_scheme_id', 'assessment_strategy_id', 'module_progression_rule_id',
                    'enrolment_type', 'awarding_body', 'nfq_level', 'credit_value',
                    'minimum_pass_grade', 'typical_duration_months', 'delivery_mode'
                ];
                
                fields.forEach(fieldId => {
                    const element = document.getElementById(fieldId);
                    if (element) {
                        element.value = config[fieldId] || '';
                    }
                });
                
                const booleanFields = [
                    'requires_placement', 'requires_external_verification',
                    'requires_portfolio_submission', 'external_examiner_required'
                ];
                
                booleanFields.forEach(fieldId => {
                    const element = document.getElementById(fieldId);
                    if (element) {
                        element.value = config[fieldId] ? '1' : '0';
                    }
                });

                // Update preview section
                updateConfigPreview(config);
            }

            function updateConfigPreview(config) {
                // Fetch readable names for the configuration
                Promise.all([
                    fetch(`/grading-schemes/${config.grading_scheme_id}`).then(r => r.json()),
                    fetch(`/assessment-strategies/${config.assessment_strategy_id}`).then(r => r.json()),
                    fetch(`/module-progression-rules/${config.module_progression_rule_id}`).then(r => r.json())
                ]).then(([grading, assessment, progression]) => {
                    document.getElementById('preview-grading').textContent = grading.name || 'Not set';
                    document.getElementById('preview-assessment').textContent = assessment.name || 'Not set';
                    document.getElementById('preview-progression').textContent = progression.name || 'Not set';
                }).catch(() => {
                    // Fallback to basic display
                    document.getElementById('preview-grading').textContent = 'Configured';
                    document.getElementById('preview-assessment').textContent = 'Configured';
                    document.getElementById('preview-progression').textContent = 'Configured';
                });

                document.getElementById('preview-enrolment').textContent = config.enrolment_type || 'Not set';
                document.getElementById('preview-pass-grade').textContent = config.minimum_pass_grade || '40';
                document.getElementById('preview-duration').textContent = config.typical_duration_months || '12';
            }
        });
    </script>
</x-app-layout>