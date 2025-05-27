<?php
// app/Models/StudentAssessment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StudentAssessment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'student_module_enrolment_id',
        'assessment_component_id',
        'attempt_number',
        'grade',
        'status',
        'due_date',
        'submission_date',
        'graded_date',
        'graded_by',
        'feedback',
    ];

    protected $casts = [
        'due_date' => 'date',
        'submission_date' => 'date',
        'graded_date' => 'datetime',
        'grade' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['grade', 'status', 'feedback'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function studentModuleEnrolment(): BelongsTo
    {
        return $this->belongsTo(StudentModuleEnrolment::class);
    }

    public function assessmentComponent(): BelongsTo
    {
        return $this->belongsTo(AssessmentComponent::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Helper methods
    public function isPassing(): bool
    {
        return $this->grade !== null && $this->grade >= 40;
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'passed', 'failed']);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'passed' => 'green',
            'failed' => 'red',
            'submitted' => 'orange',
            'graded' => 'blue',
            default => 'gray'
        };
    }

    public function getGradeWithStatusAttribute(): string
    {
        if ($this->grade !== null) {
            return number_format($this->grade, 1) . '% (' . ($this->isPassing() ? 'PASS' : 'FAIL') . ')';
        }
        return ucfirst($this->status);
    }
}