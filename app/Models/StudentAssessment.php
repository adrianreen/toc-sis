<?php
// app/Models/StudentAssessment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Carbon\Carbon;

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
        'is_visible_to_student',
        'release_date',
        'visibility_changed_by',
        'visibility_changed_at',
        'release_notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'submission_date' => 'date',
        'graded_date' => 'datetime',
        'release_date' => 'datetime',
        'visibility_changed_at' => 'datetime',
        'grade' => 'decimal:2',
        'is_visible_to_student' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['grade', 'status', 'feedback', 'is_visible_to_student', 'release_date'])
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

    public function visibilityChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visibility_changed_by');
    }

    // ===== VISIBILITY METHODS =====

    /**
     * Check if this assessment result should be visible to the student
     */
    public function isVisibleToStudent(): bool
    {
        // Must have a grade to be potentially visible
        if ($this->grade === null) {
            return false;
        }

        // If manually set to visible
        if ($this->is_visible_to_student) {
            return true;
        }

        // Check if auto-release date has passed
        if ($this->release_date && $this->release_date->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Check if this assessment has been released (either manually or by time)
     */
    public function isReleased(): bool
    {
        return $this->isVisibleToStudent();
    }

    /**
     * Get the reason why this assessment is visible or not
     */
    public function getVisibilityStatus(): array
    {
        if (!$this->grade) {
            return [
                'visible' => false,
                'reason' => 'not_graded',
                'message' => 'Not yet graded'
            ];
        }

        if ($this->is_visible_to_student) {
            return [
                'visible' => true,
                'reason' => 'manual',
                'message' => 'Manually released',
                'released_by' => $this->visibilityChangedBy?->name,
                'released_at' => $this->visibility_changed_at
            ];
        }

        if ($this->release_date) {
            if ($this->release_date->isPast()) {
                return [
                    'visible' => true,
                    'reason' => 'scheduled',
                    'message' => 'Auto-released on schedule',
                    'released_at' => $this->release_date
                ];
            } else {
                return [
                    'visible' => false,
                    'reason' => 'scheduled_future',
                    'message' => 'Scheduled for release',
                    'release_date' => $this->release_date
                ];
            }
        }

        return [
            'visible' => false,
            'reason' => 'hidden',
            'message' => 'Hidden from student'
        ];
    }

    /**
     * Manually show this assessment to student
     */
    public function showToStudent(?string $notes = null): void
    {
        $this->update([
            'is_visible_to_student' => true,
            'visibility_changed_by' => auth()->id(),
            'visibility_changed_at' => now(),
            'release_notes' => $notes,
        ]);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'show_to_student', 'notes' => $notes])
            ->log('Assessment result made visible to student');
    }

    /**
     * Manually hide this assessment from student
     */
    public function hideFromStudent(?string $notes = null): void
    {
        $this->update([
            'is_visible_to_student' => false,
            'visibility_changed_by' => auth()->id(),
            'visibility_changed_at' => now(),
            'release_notes' => $notes,
        ]);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'hide_from_student', 'notes' => $notes])
            ->log('Assessment result hidden from student');
    }

    /**
     * Set scheduled release date
     */
    public function scheduleRelease(Carbon $releaseDate, ?string $notes = null): void
    {
        $this->update([
            'release_date' => $releaseDate,
            'visibility_changed_by' => auth()->id(),
            'visibility_changed_at' => now(),
            'release_notes' => $notes,
        ]);

        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['action' => 'schedule_release', 'release_date' => $releaseDate, 'notes' => $notes])
            ->log('Assessment result scheduled for release on ' . $releaseDate->format('d M Y H:i'));
    }

    // ===== EXISTING HELPER METHODS =====

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

    // ===== QUERY SCOPES =====

    /**
     * Scope to get assessments visible to students
     */
    public function scopeVisibleToStudents($query)
    {
        return $query->where(function ($q) {
            $q->where('is_visible_to_student', true)
              ->orWhere(function ($subQ) {
                  $subQ->whereNotNull('release_date')
                       ->where('release_date', '<=', now());
              });
        })->whereNotNull('grade');
    }

    /**
     * Scope to get assessments hidden from students
     */
    public function scopeHiddenFromStudents($query)
    {
        return $query->where(function ($q) {
            $q->where('is_visible_to_student', false)
              ->where(function ($subQ) {
                  $subQ->whereNull('release_date')
                       ->orWhere('release_date', '>', now());
              });
        })->whereNotNull('grade');
    }
}