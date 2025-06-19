<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Archetype Validation Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Archetype Validation Dashboard</h1>
                <div>
                    <a href="{{ route('admin.archetypes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Archetypes
                    </a>
                    <button class="btn btn-primary" onclick="refreshValidation()">
                        <i class="fas fa-sync-alt"></i> Refresh Validation
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                @php
                    $totalProgrammes = count($validationResults);
                    $validProgrammes = collect($validationResults)->where('validation.valid', true)->count();
                    $invalidProgrammes = $totalProgrammes - $validProgrammes;
                    $validationRate = $totalProgrammes > 0 ? round(($validProgrammes / $totalProgrammes) * 100, 1) : 0;
                @endphp

                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Programmes</h6>
                                    <h3>{{ $totalProgrammes }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-graduation-cap fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Valid Configurations</h6>
                                    <h3>{{ $validProgrammes }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Invalid Configurations</h6>
                                    <h3>{{ $invalidProgrammes }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Validation Rate</h6>
                                    <h3>{{ $validationRate }}%</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <select class="form-control" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="valid">Valid Only</option>
                                <option value="invalid">Invalid Only</option>
                                <option value="warnings">Has Warnings</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="archetypeFilter">
                                <option value="">All Archetypes</option>
                                <option value="QQI5">QQI Level 5</option>
                                <option value="QQI6">QQI Level 6</option>
                                <option value="DEGREE">Degree</option>
                                <option value="FLEXIBLE">Flexible</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search programmes...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation Results Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Programme Configuration Validation Results</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="validationTable">
                            <thead>
                                <tr>
                                    <th>Programme</th>
                                    <th>Archetype</th>
                                    <th>Configuration</th>
                                    <th>Validation Status</th>
                                    <th>Issues</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($validationResults as $result)
                                @php
                                    $programme = $result['programme'];
                                    $validation = $result['validation'];
                                    $constraints = $result['constraints'];
                                @endphp
                                <tr data-status="{{ $validation['valid'] ? 'valid' : 'invalid' }}" 
                                    data-archetype="{{ $programme->programmeType?->code }}"
                                    data-search="{{ strtolower($programme->code . ' ' . $programme->title) }}">
                                    <td>
                                        <strong>{{ $programme->code }}</strong>
                                        <br><small class="text-muted">{{ Str::limit($programme->title, 50) }}</small>
                                    </td>
                                    <td>
                                        @if($programme->programmeType)
                                            <span class="badge badge-primary">{{ $programme->programmeType->code }}</span>
                                            <br><small>{{ $programme->programmeType->name }}</small>
                                        @else
                                            <span class="badge badge-secondary">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if($programme->gradingScheme)
                                                <div><strong>G:</strong> {{ $programme->gradingScheme->name }}</div>
                                            @else
                                                <div class="text-muted"><strong>G:</strong> Not configured</div>
                                            @endif
                                            @if($programme->assessmentStrategy)
                                                <div><strong>A:</strong> {{ $programme->assessmentStrategy->name }}</div>
                                            @else
                                                <div class="text-muted"><strong>A:</strong> Not configured</div>
                                            @endif
                                            @if($programme->moduleProgressionRule)
                                                <div><strong>P:</strong> {{ $programme->moduleProgressionRule->name }}</div>
                                            @else
                                                <div class="text-muted"><strong>P:</strong> Not configured</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($validation['valid'])
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Valid
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> Invalid
                                            </span>
                                        @endif
                                        
                                        @if(!empty($validation['warnings']))
                                            <br><span class="badge badge-warning mt-1">
                                                <i class="fas fa-exclamation-triangle"></i> {{ count($validation['warnings']) }} Warnings
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($validation['errors']))
                                            <div class="mb-2">
                                                <strong class="text-danger">Errors:</strong>
                                                <ul class="mb-0 pl-3">
                                                    @foreach($validation['errors'] as $error)
                                                        <li class="small text-danger">{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        
                                        @if(!empty($validation['warnings']))
                                            <div>
                                                <strong class="text-warning">Warnings:</strong>
                                                <ul class="mb-0 pl-3">
                                                    @foreach($validation['warnings'] as $warning)
                                                        <li class="small text-warning">{{ $warning }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        
                                        @if(empty($validation['errors']) && empty($validation['warnings']))
                                            <span class="text-muted small">No issues detected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('programmes.show', $programme) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="View Programme">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('programmes.edit', $programme) }}" 
                                               class="btn btn-sm btn-outline-secondary"
                                               title="Edit Programme">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($programme->programmeType)
                                                <a href="{{ route('admin.archetypes.show', $programme->programmeType) }}" 
                                                   class="btn btn-sm btn-outline-info"
                                                   title="View Archetype">
                                                    <i class="fas fa-layer-group"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(empty($validationResults))
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i>
                            No programmes with archetype configurations found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('statusFilter');
    const archetypeFilter = document.getElementById('archetypeFilter');
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('validationTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const statusValue = statusFilter.value;
        const archetypeValue = archetypeFilter.value;
        const searchValue = searchInput.value.toLowerCase();

        rows.forEach(row => {
            const status = row.dataset.status;
            const archetype = row.dataset.archetype || '';
            const searchText = row.dataset.search;
            const hasWarnings = row.querySelector('.badge-warning') !== null;

            let showRow = true;

            // Status filter
            if (statusValue === 'valid' && status !== 'valid') showRow = false;
            if (statusValue === 'invalid' && status !== 'invalid') showRow = false;
            if (statusValue === 'warnings' && !hasWarnings) showRow = false;

            // Archetype filter
            if (archetypeValue && archetype !== archetypeValue) showRow = false;

            // Search filter
            if (searchValue && !searchText.includes(searchValue)) showRow = false;

            row.style.display = showRow ? '' : 'none';
        });
    }

    statusFilter.addEventListener('change', filterTable);
    archetypeFilter.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);
});

function refreshValidation() {
    location.reload();
}
</script>
@endpush