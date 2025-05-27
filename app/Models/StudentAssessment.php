<?php
// app/Models/StudentAssessment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'grade' => 'decimal:2',
        'due_date' => 'date',
        'submission_date' => 'date',
        'graded_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['grade', 'status', 'submission_date', 'graded_date'])
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

    public function extensions(): HasMany
    {
        return $this->hasMany(Extension::class);
    }

    public function repeatAssessments(): HasMany
    {
        return $this->hasMany(RepeatAssessment::class);
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'passed', 'failed']);
    }

    public function isGraded(): bool
    {
        return in_array($this->status, ['graded', 'passed', 'failed']);
    }

    public function isPassed(): bool
    {
        return $this->status === 'passed' || ($this->grade && $this->grade >= 40);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed' || ($this->grade && $this->grade < 40);
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && !$this->isSubmitted();
    }

    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function getDaysOverdueAttribute(): int
    {
        return $this->due_date->diffInDays(now());
    }

    public function submit(): void
    {
        $this->update([
            'status' => 'submitted',
            'submission_date' => now(),
        ]);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log('Assessment submitted');
    }

    public function grade(float $grade, string $feedback = null): void
    {
        $this->update([
            'grade' => $grade,
            'status' => $grade >= 40 ? 'passed' : 'failed',
            'graded_date' => now(),
            'graded_by' => auth()->id(),
            'feedback' => $feedback,
        ]);

        // Update the parent module enrolment
        $this->studentModuleEnrolment->updateStatus();

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['grade' => $grade])
            ->log('Assessment graded: ' . $grade . '%');
    }
}