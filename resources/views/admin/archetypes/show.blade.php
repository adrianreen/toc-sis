<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $archetype->name }} Archetype
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>{{ $archetype->name }} Archetype</h1>
                    <p class="text-muted">{{ $archetype->description }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.archetypes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Archetypes
                    </a>
                    <a href="{{ route('admin.archetypes.edit', $archetype) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Configuration
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Archetype Details -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Archetype Configuration</h5>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Code</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-primary">{{ $archetype->code }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">NFQ Level</dt>
                                <dd class="col-sm-8">{{ $archetype->nfq_level }}</dd>
                                
                                <dt class="col-sm-4">Awarding Body</dt>
                                <dd class="col-sm-8">{{ $archetype->awarding_body }}</dd>
                                
                                <dt class="col-sm-4">Default Duration</dt>
                                <dd class="col-sm-8">{{ $archetype->default_duration_months }} months</dd>
                                
                                <dt class="col-sm-4">Credit Value</dt>
                                <dd class="col-sm-8">{{ $archetype->default_credit_value }} credits</dd>
                                
                                <dt class="col-sm-4">Pass Grade</dt>
                                <dd class="col-sm-8">{{ $archetype->minimum_pass_grade }}%</dd>
                            </dl>

                            <h6 class="mt-4">Archetype Features</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" disabled {{ $archetype->requires_placement ? 'checked' : '' }}>
                                        <label class="form-check-label">Requires Placement</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" disabled {{ $archetype->requires_external_verification ? 'checked' : '' }}>
                                        <label class="form-check-label">External Verification</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" disabled {{ $archetype->supports_rolling_enrolment ? 'checked' : '' }}>
                                        <label class="form-check-label">Rolling Enrolment</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" disabled {{ $archetype->supports_cohort_enrolment ? 'checked' : '' }}>
                                        <label class="form-check-label">Cohort Enrolment</label>
                                    </div>
                                </div>
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
                            <div class="mb-3">
                                <h6>Grading Scheme</h6>
                                @if($archetype->defaultGradingScheme)
                                    <div class="alert alert-info">
                                        <strong>{{ $archetype->defaultGradingScheme->name }}</strong>
                                        <br><small>{{ $archetype->defaultGradingScheme->description }}</small>
                                        <br><small class="text-muted">Type: {{ ucfirst($archetype->defaultGradingScheme->type) }}</small>
                                    </div>
                                @else
                                    <div class="alert alert-warning">No default grading scheme configured</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <h6>Assessment Strategy</h6>
                                @if($archetype->defaultAssessmentStrategy)
                                    <div class="alert alert-info">
                                        <strong>{{ $archetype->defaultAssessmentStrategy->name }}</strong>
                                        <br><small>{{ $archetype->defaultAssessmentStrategy->description }}</small>
                                        <br><small class="text-muted">Type: {{ ucfirst($archetype->defaultAssessmentStrategy->assessment_type) }}</small>
                                    </div>
                                @else
                                    <div class="alert alert-warning">No default assessment strategy configured</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <h6>Module Progression Rule</h6>
                                @if($archetype->defaultModuleProgressionRule)
                                    <div class="alert alert-info">
                                        <strong>{{ $archetype->defaultModuleProgressionRule->name }}</strong>
                                        <br><small>{{ $archetype->defaultModuleProgressionRule->description }}</small>
                                        <br><small class="text-muted">Type: {{ ucfirst($archetype->defaultModuleProgressionRule->progression_type) }}</small>
                                    </div>
                                @else
                                    <div class="alert alert-warning">No default progression rule configured</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Analysis -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Configuration Analysis</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h3 class="text-primary">{{ $configAnalysis['default_compliance'] }}%</h3>
                                        <p class="text-muted">Default Compliance Rate</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h3 class="text-info">{{ count($configAnalysis['configuration_variants']) }}</h3>
                                        <p class="text-muted">Configuration Variants</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <h3 class="text-success">{{ $programmes->count() }}</h3>
                                        <p class="text-muted">Total Programmes</p>
                                    </div>
                                </div>
                            </div>

                            @if(count($configAnalysis['configuration_variants']) > 1)
                            <div class="mt-4">
                                <h6>Configuration Variants</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Grading Scheme</th>
                                                <th>Assessment Strategy</th>
                                                <th>Progression Rule</th>
                                                <th>Programme Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($configAnalysis['configuration_variants'] as $variant)
                                            <tr>
                                                <td>{{ $variant['grading_scheme'] ?? 'Not Set' }}</td>
                                                <td>{{ $variant['assessment_strategy'] ?? 'Not Set' }}</td>
                                                <td>{{ $variant['progression_rule'] ?? 'Not Set' }}</td>
                                                <td><span class="badge badge-secondary">{{ $variant['count'] }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Programmes Using This Archetype -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Programmes Using This Archetype ({{ $programmes->count() }})</h5>
                        </div>
                        <div class="card-body">
                            @if($programmes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Programme</th>
                                                <th>Cohorts</th>
                                                <th>Configuration</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($programmes as $programme)
                                            <tr>
                                                <td>
                                                    <strong>{{ $programme->code }}</strong>
                                                    <br><small class="text-muted">{{ $programme->title }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $programme->cohorts->count() }}</span>
                                                </td>
                                                <td>
                                                    <div class="small">
                                                        @if($programme->gradingScheme)
                                                            <div><strong>G:</strong> {{ $programme->gradingScheme->name }}</div>
                                                        @endif
                                                        @if($programme->assessmentStrategy)
                                                            <div><strong>A:</strong> {{ $programme->assessmentStrategy->name }}</div>
                                                        @endif
                                                        @if($programme->moduleProgressionRule)
                                                            <div><strong>P:</strong> {{ $programme->moduleProgressionRule->name }}</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($programme->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('programmes.show', $programme) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No programmes are currently using this archetype.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</x-app-layout>