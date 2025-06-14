<?php
// app/Models/StudentModuleEnrolment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StudentModuleEnrolment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'student_id',
        'enrolment_id',
        'module_instance_id',
        'status',
        'attempt_number',
        'final_grade',
        'completion_date',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'final_grade' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'final_grade', 'completion_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class);
    }

    public function moduleInstance(): BelongsTo
    {
        return $this->belongsTo(ModuleInstance::class);
    }

    public function studentAssessments(): HasMany
    {
        return $this->hasMany(StudentAssessment::class);
    }

    // Helper methods
    public function isPassing(): bool
    {
        return $this->final_grade >= 40; // Assuming 40% is passing
    }

    public function calculateFinalGrade(): float
    {
        $assessments = $this->studentAssessments()
            ->with('assessmentComponent')
            ->whereIn('status', ['graded', 'passed', 'failed'])
            ->whereNotNull('grade')
            ->get();

        $totalGrade = 0;
        $totalWeight = 0;

        foreach ($assessments as $assessment) {
            $weight = $assessment->assessmentComponent->weight;
            $totalGrade += ($assessment->grade * $weight / 100);
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($totalGrade, 2) : 0;
    }

    public function updateStatus(): void
    {
        $allAssessments = $this->studentAssessments()->count();
        $gradedAssessments = $this->studentAssessments()->whereIn('status', ['graded', 'passed', 'failed'])->count();
        
        if ($allAssessments === 0) {
            $this->status = 'enrolled';
        } elseif ($gradedAssessments === $allAssessments) {
            $this->final_grade = $this->calculateFinalGrade();
            $this->status = $this->isPassing() ? 'completed' : 'failed';
            $this->completion_date = now();
        } else {
            $this->status = 'active';
        }
        
        $this->save();
    }
}