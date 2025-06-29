<?php

// app/Models/Deferral.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'enrolment_id',
        'from_programme_instance_id',
        'to_programme_instance_id',
        'deferral_date',
        'expected_return_date',
        'actual_return_date',
        'reason',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'deferral_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class);
    }

    public function fromProgrammeInstance(): BelongsTo
    {
        return $this->belongsTo(ProgrammeInstance::class, 'from_programme_instance_id');
    }

    public function toProgrammeInstance(): BelongsTo
    {
        return $this->belongsTo(ProgrammeInstance::class, 'to_programme_instance_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
