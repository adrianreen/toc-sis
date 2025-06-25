<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentGradeRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'module_instance_id',
        'assessment_component_name',
        'grade',
        'max_grade',
        'feedback',
        'submission_date',
        'graded_date',
        'graded_by_staff_id',
        'is_visible_to_student',
        'release_date',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'max_grade' => 'decimal:2',
        'submission_date' => 'date',
        'graded_date' => 'date',
        'release_date' => 'date',
        'is_visible_to_student' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function moduleInstance(): BelongsTo
    {
        return $this->belongsTo(ModuleInstance::class);
    }

    public function gradedByStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by_staff_id');
    }

    public function repeatAssessments(): HasMany
    {
        return $this->hasMany(RepeatAssessment::class);
    }

    public function getPercentageAttribute(): ?float
    {
        if ($this->grade === null || $this->max_grade === null || $this->max_grade == 0) {
            return null;
        }

        return ($this->grade / $this->max_grade) * 100;
    }

    public function isGraded(): bool
    {
        return $this->grade !== null;
    }

    public function isVisibleToStudent(): bool
    {
        return $this->is_visible_to_student || ($this->release_date && $this->release_date <= now());
    }
}
