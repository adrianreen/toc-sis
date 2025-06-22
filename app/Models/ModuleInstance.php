<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleInstance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'module_id',
        'tutor_id',
        'start_date',
        'target_end_date',
        'delivery_style',
        'moodle_course_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_end_date' => 'date',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function programmeInstances(): BelongsToMany
    {
        return $this->belongsToMany(ProgrammeInstance::class, 'programme_instance_curriculum');
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    public function studentGradeRecords(): HasMany
    {
        return $this->hasMany(StudentGradeRecord::class);
    }
}
