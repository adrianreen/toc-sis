{{--
Enhanced Grade Cell Component
Eliminates icon overlap, provides clear clickability, and establishes visual hierarchy
--}}

@props([
    'student',
    'component', 
    'gradeRecord' => null,
    'moduleInstance'
])

@php
    $isGraded = $gradeRecord && $gradeRecord->grade !== null;
    $isVisible = $gradeRecord?->is_visible_to_student ?? false;
    $percentage = $gradeRecord?->percentage;
    $isPassing = $percentage && $percentage >= ($component['component_pass_mark'] ?? 40);
@endphp

<td class="grade-cell-container" 
    data-student-id="{{ $student->id }}" 
    data-component="{{ $component['component_name'] }}"
    data-grade-record-id="{{ $gradeRecord?->id }}">
    
    <!-- Status Strip (Top of cell - eliminates overlap) -->
    <div class="status-strip">
        @if($isGraded && !$isVisible)
            <div class="status-indicator status-hidden" title="Hidden from student">
                <svg class="status-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                </svg>
            </div>
        @endif
        
        @if($gradeRecord?->feedback)
            <div class="status-indicator status-feedback" title="Has feedback">
                <svg class="status-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
                </svg>
            </div>
        @endif
        
        @if($component['is_must_pass'])
            <div class="status-indicator status-must-pass {{ $isPassing ? 'passing' : 'failing' }}" 
                 title="{{ $isPassing ? 'Passing must-pass component' : 'Failing must-pass component' }}">
                <svg class="status-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
        @endif
    </div>
    
    <!-- Main Grade Display (Primary Click Area) -->
    <div class="grade-display-main {{ $isGraded ? 'has-grade' : 'no-grade' }} 
                {{ $isGraded && !$isPassing ? 'failing-grade' : '' }}
                {{ $component['is_must_pass'] && !$isPassing ? 'must-pass-failing' : '' }}"
         onclick="enableGradeEdit(this)"
         tabindex="0"
         role="button"
         aria-label="Click to edit grade for {{ $student->full_name }} - {{ $component['component_name'] }}">
        
        @if($isGraded)
            <div class="grade-content">
                <div class="grade-percentage">{{ round($percentage, 1) }}%</div>
                <div class="grade-fraction">{{ $gradeRecord->grade }}/{{ $gradeRecord->max_grade }}</div>
            </div>
            <div class="grade-status-badge {{ $isPassing ? 'pass' : 'fail' }}">
                {{ $isPassing ? 'PASS' : 'FAIL' }}
            </div>
        @else
            <div class="no-grade-content">
                <div class="no-grade-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div class="no-grade-text">Add Grade</div>
            </div>
        @endif
        
        <!-- Edit Indicator (Bottom Left) -->
        <div class="edit-indicator">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.828-2.828z"/>
            </svg>
        </div>
    </div>
    
    <!-- Quick Actions Panel (Hidden by default - eliminates overlap) -->
    <div class="quick-actions-panel">
        @if($isGraded)
            <button class="quick-action-btn visibility-btn" 
                    onclick="event.stopPropagation(); toggleVisibility({{ $gradeRecord->id }})"
                    title="{{ $isVisible ? 'Hide from student' : 'Show to student' }}">
                @if($isVisible)
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                @else
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                    </svg>
                @endif
            </button>
        @endif
        
        <button class="quick-action-btn feedback-btn" 
                onclick="event.stopPropagation(); showFeedbackModal({{ $gradeRecord?->id ?? 'null' }}, '{{ $student->full_name }}', '{{ $component['component_name'] }}')"
                title="Edit Feedback">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
            </svg>
        </button>
        
        @if($isGraded)
            <button class="quick-action-btn delete-btn" 
                    onclick="event.stopPropagation(); deleteGrade({{ $gradeRecord->id }})"
                    title="Clear Grade">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </button>
        @endif
    </div>
    
    <!-- Edit Mode Input -->
    <input type="number" 
           class="grade-input-field hidden"
           value="{{ $gradeRecord?->grade ?? '' }}"
           min="0" 
           max="{{ $gradeRecord?->max_grade ?? 100 }}"
           step="0.1"
           placeholder="Enter grade (0-{{ $gradeRecord?->max_grade ?? 100 }})"
           onblur="saveGrade(this)"
           onkeydown="handleGradeKeydown(event, this)">
</td>

<style>
/* Grade Cell Component Styles */
:root {
    --grade-cell-width: 140px;
    --grade-cell-height: 80px;
    --grade-cell-padding: 8px;
    --grade-primary: #3b82f6;
    --grade-success: #10b981;
    --grade-danger: #ef4444;
    --grade-warning: #f59e0b;
    --grade-neutral: #6b7280;
    --grade-border-radius: 12px;
    --grade-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --z-grade-base: 1;
    --z-grade-hover: 5;
    --z-grade-edit: 10;
    --z-grade-actions: 15;
}

.grade-cell-container {
    position: relative;
    width: var(--grade-cell-width);
    height: var(--grade-cell-height);
    padding: var(--grade-cell-padding);
    vertical-align: top;
}

.grade-cell-container:hover {
    z-index: var(--z-grade-hover);
}

/* Status Strip - Eliminates Icon Overlap */
.status-strip {
    position: absolute;
    top: 2px;
    left: 2px;
    right: 2px;
    height: 16px;
    display: flex;
    justify-content: flex-end;
    gap: 2px;
    z-index: var(--z-grade-base);
}

.status-indicator {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    opacity: 0.9;
    transition: var(--grade-transition);
}

.status-indicator.status-hidden {
    background: var(--grade-warning);
    color: white;
}

.status-indicator.status-feedback {
    background: var(--grade-primary);
    color: white;
}

.status-indicator.status-must-pass.passing {
    background: var(--grade-success);
    color: white;
}

.status-indicator.status-must-pass.failing {
    background: var(--grade-danger);
    color: white;
    animation: critical-pulse 2s infinite;
}

.status-icon {
    width: 8px;
    height: 8px;
}

/* Main Grade Display - Clear Click Target */
.grade-display-main {
    position: relative;
    width: 100%;
    height: calc(100% - 20px);
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: var(--grade-border-radius);
    cursor: pointer;
    transition: var(--grade-transition);
    border: 2px solid transparent;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* Clear Hover States */
.grade-display-main:hover {
    transform: translateY(-1px) scale(1.02);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: var(--grade-primary);
    z-index: var(--z-grade-hover);
}

.grade-display-main:active {
    transform: translateY(0) scale(0.98);
    transition: all 0.1s ease;
}

.grade-display-main:focus {
    outline: none;
    border-color: var(--grade-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

/* State-Specific Styling */
.grade-display-main.has-grade {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-color: #93c5fd;
}

.grade-display-main.no-grade {
    border: 2px dashed #d1d5db;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

.grade-display-main.no-grade:hover {
    border-style: solid;
    border-color: var(--grade-primary);
    background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 100%);
}

.grade-display-main.failing-grade {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-color: var(--grade-danger);
}

.grade-display-main.must-pass-failing {
    animation: critical-pulse 2s infinite;
}

/* Grade Content Layout */
.grade-content {
    text-align: center;
    z-index: var(--z-grade-base);
}

.grade-percentage {
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
    color: var(--grade-primary);
}

.grade-display-main.failing-grade .grade-percentage {
    color: var(--grade-danger);
}

.grade-fraction {
    font-size: 11px;
    color: var(--grade-neutral);
    font-weight: 500;
    margin-top: 2px;
}

.grade-status-badge {
    position: absolute;
    bottom: 4px;
    right: 4px;
    font-size: 8px;
    font-weight: 700;
    padding: 2px 4px;
    border-radius: 4px;
    text-transform: uppercase;
}

.grade-status-badge.pass {
    background: var(--grade-success);
    color: white;
}

.grade-status-badge.fail {
    background: var(--grade-danger);
    color: white;
}

/* No Grade State */
.no-grade-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.grade-display-main:hover .no-grade-content {
    opacity: 1;
}

.no-grade-icon {
    color: var(--grade-neutral);
    transition: color 0.3s ease;
    width: 20px;
    height: 20px;
}

.grade-display-main:hover .no-grade-icon {
    color: var(--grade-primary);
}

.no-grade-text {
    font-size: 11px;
    color: var(--grade-neutral);
    font-weight: 500;
    transition: color 0.3s ease;
}

.grade-display-main:hover .no-grade-text {
    color: var(--grade-primary);
}

/* Edit Indicator */
.edit-indicator {
    position: absolute;
    bottom: 4px;
    left: 4px;
    width: 14px;
    height: 14px;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.grade-display-main:hover .edit-indicator {
    opacity: 1;
}

.edit-indicator svg {
    width: 8px;
    height: 8px;
    color: var(--grade-primary);
}

/* Quick Actions Panel - Eliminates Overlap */
.quick-actions-panel {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    gap: 4px;
    opacity: 0;
    visibility: hidden;
    transition: var(--grade-transition);
    z-index: var(--z-grade-actions);
    background: rgba(0, 0, 0, 0.8);
    padding: 4px;
    border-radius: 8px;
    backdrop-filter: blur(4px);
}

.grade-cell-container:hover .quick-actions-panel {
    opacity: 1;
    visibility: visible;
}

.quick-action-btn {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--grade-transition);
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.quick-action-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.quick-action-btn svg {
    width: 12px;
    height: 12px;
}

.visibility-btn:hover {
    background: var(--grade-warning);
}

.feedback-btn:hover {
    background: var(--grade-primary);
}

.delete-btn:hover {
    background: var(--grade-danger);
}

/* Edit Mode Input */
.grade-input-field {
    position: absolute;
    top: 20px;
    left: 0;
    width: 100%;
    height: calc(100% - 20px);
    border: 3px solid var(--grade-primary);
    border-radius: var(--grade-border-radius);
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    background: white;
    transition: all 0.2s ease;
    z-index: var(--z-grade-edit);
    padding: 8px;
}

.grade-input-field:focus {
    outline: none;
    border-color: #1d4ed8;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    background: #fffbeb;
}

/* Animations */
@keyframes critical-pulse {
    0%, 100% { 
        opacity: 1; 
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }
    50% { 
        opacity: 0.8; 
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }
}

@keyframes grade-save-success {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); background: var(--grade-success); }
    100% { transform: scale(1); }
}

.grade-display-main.save-success {
    animation: grade-save-success 0.6s ease;
}

/* Responsive Design */
@media (max-width: 1024px) {
    :root {
        --grade-cell-width: 120px;
        --grade-cell-height: 70px;
    }
    
    .grade-percentage {
        font-size: 16px;
    }
}

@media (max-width: 768px) {
    :root {
        --grade-cell-width: 100px;
        --grade-cell-height: 60px;
        --grade-cell-padding: 6px;
    }
    
    .grade-percentage {
        font-size: 14px;
    }
    
    .quick-actions-panel {
        gap: 2px;
        padding: 2px;
    }
    
    .quick-action-btn {
        width: 20px;
        height: 20px;
    }
}

/* Accessibility */
@media (prefers-contrast: high) {
    .grade-display-main {
        border-width: 3px;
    }
    
    .grade-display-main.no-grade {
        border-style: solid;
    }
    
    .status-indicator {
        border: 2px solid white;
    }
}

@media (prefers-reduced-motion: reduce) {
    .grade-display-main,
    .status-indicator,
    .quick-action-btn {
        transition: none;
    }
    
    .grade-display-main:hover {
        transform: none;
    }
    
    .critical-pulse {
        animation: none;
    }
}
</style>