<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrolment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'enrolment_type',
        'programme_instance_id',
        'module_instance_id',
        'enrolment_date',
        'status',
    ];

    protected $casts = [
        'enrolment_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function programmeInstance(): BelongsTo
    {
        return $this->belongsTo(ProgrammeInstance::class);
    }

    public function moduleInstance(): BelongsTo
    {
        return $this->belongsTo(ModuleInstance::class);
    }

    public function isProgrammeEnrolment(): bool
    {
        return $this->enrolment_type === 'programme';
    }

    public function isModuleEnrolment(): bool
    {
        return $this->enrolment_type === 'module';
    }
}
