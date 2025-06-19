<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit {{ $archetype->name }} Archetype
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit {{ $archetype->name }} Archetype</h1>
                <div>
                    <a href="{{ route('admin.archetypes.show', $archetype) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Details
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.archetypes.update', $archetype) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Basic Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $archetype->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3">{{ old('description', $archetype->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="awarding_body" class="form-label">Awarding Body <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('awarding_body') is-invalid @enderror" 
                                           id="awarding_body" 
                                           name="awarding_body" 
                                           value="{{ old('awarding_body', $archetype->awarding_body) }}" 
                                           required>
                                    @error('awarding_body')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="nfq_level" class="form-label">NFQ Level <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('nfq_level') is-invalid @enderror" 
                                           id="nfq_level" 
                                           name="nfq_level" 
                                           value="{{ old('nfq_level', $archetype->nfq_level) }}" 
                                           required>
                                    @error('nfq_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Programme Defaults -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Programme Defaults</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="default_duration_months" class="form-label">Default Duration (Months) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('default_duration_months') is-invalid @enderror" 
                                           id="default_duration_months" 
                                           name="default_duration_months" 
                                           value="{{ old('default_duration_months', $archetype->default_duration_months) }}" 
                                           min="1" 
                                           required>
                                    @error('default_duration_months')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="default_credit_value" class="form-label">Default Credit Value <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('default_credit_value') is-invalid @enderror" 
                                           id="default_credit_value" 
                                           name="default_credit_value" 
                                           value="{{ old('default_credit_value', $archetype->default_credit_value) }}" 
                                           min="1" 
                                           required>
                                    @error('default_credit_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="minimum_pass_grade" class="form-label">Minimum Pass Grade (%) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('minimum_pass_grade') is-invalid @enderror" 
                                           id="minimum_pass_grade" 
                                           name="minimum_pass_grade" 
                                           value="{{ old('minimum_pass_grade', $archetype->minimum_pass_grade) }}" 
                                           min="0" 
                                           max="100" 
                                           step="0.1" 
                                           required>
                                    @error('minimum_pass_grade')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archetype Features -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Archetype Features</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="requires_placement" 
                                           name="requires_placement" 
                                           value="1"
                                           {{ old('requires_placement', $archetype->requires_placement) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_placement">
                                        Requires Placement
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="requires_external_verification" 
                                           name="requires_external_verification" 
                                           value="1"
                                           {{ old('requires_external_verification', $archetype->requires_external_verification) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_external_verification">
                                        Requires External Verification
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="supports_rolling_enrolment" 
                                           name="supports_rolling_enrolment" 
                                           value="1"
                                           {{ old('supports_rolling_enrolment', $archetype->supports_rolling_enrolment) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="supports_rolling_enrolment">
                                        Supports Rolling Enrolment
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="supports_cohort_enrolment" 
                                           name="supports_cohort_enrolment" 
                                           value="1"
                                           {{ old('supports_cohort_enrolment', $archetype->supports_cohort_enrolment) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="supports_cohort_enrolment">
                                        Supports Cohort Enrolment
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Default Configuration -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Default Configuration</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="default_grading_scheme_id" class="form-label">Default Grading Scheme</label>
                                    <select class="form-control @error('default_grading_scheme_id') is-invalid @enderror" 
                                            id="default_grading_scheme_id" 
                                            name="default_grading_scheme_id">
                                        <option value="">Select a grading scheme...</option>
                                        @foreach($gradingSchemes as $scheme)
                                            <option value="{{ $scheme->id }}" 
                                                    {{ old('default_grading_scheme_id', $archetype->default_grading_scheme_id) == $scheme->id ? 'selected' : '' }}>
                                                {{ $scheme->name }} ({{ $scheme->type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_grading_scheme_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="default_assessment_strategy_id" class="form-label">Default Assessment Strategy</label>
                                    <select class="form-control @error('default_assessment_strategy_id') is-invalid @enderror" 
                                            id="default_assessment_strategy_id" 
                                            name="default_assessment_strategy_id">
                                        <option value="">Select an assessment strategy...</option>
                                        @foreach($assessmentStrategies as $strategy)
                                            <option value="{{ $strategy->id }}" 
                                                    {{ old('default_assessment_strategy_id', $archetype->default_assessment_strategy_id) == $strategy->id ? 'selected' : '' }}>
                                                {{ $strategy->name }} ({{ $strategy->assessment_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_assessment_strategy_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="default_module_progression_rule_id" class="form-label">Default Module Progression Rule</label>
                                    <select class="form-control @error('default_module_progression_rule_id') is-invalid @enderror" 
                                            id="default_module_progression_rule_id" 
                                            name="default_module_progression_rule_id">
                                        <option value="">Select a progression rule...</option>
                                        @foreach($moduleProgressionRules as $rule)
                                            <option value="{{ $rule->id }}" 
                                                    {{ old('default_module_progression_rule_id', $archetype->default_module_progression_rule_id) == $rule->id ? 'selected' : '' }}>
                                                {{ $rule->name }} ({{ $rule->progression_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('default_module_progression_rule_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Archetype Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
        </div>
    </div>
</x-app-layout>