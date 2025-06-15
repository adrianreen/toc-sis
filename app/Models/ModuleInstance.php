<?php
// app/Models/ModuleInstance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'cohort_id',
        'instance_code',
        'start_date',
        'end_date',
        'teacher_id',
        'status',
        'settings',
        'moodle_course_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'settings' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function studentEnrolments(): HasMany
    {
        return $this->hasMany(StudentModuleEnrolment::class);
    }

    public static function generateInstanceCode($moduleCode, $cohortCode)
    {
        return $moduleCode . '-' . $cohortCode;
    }

    /**
     * Get the full course name for Moodle
     */
    public function getFullCourseName(): string
    {
        return $this->module->name . ' (' . $this->cohort->name . ')';
    }
}