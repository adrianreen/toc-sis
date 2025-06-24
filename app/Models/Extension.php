<?php

// app/Models/Extension.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Extension extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_grade_record_id',
        'student_id',
        'original_due_date',
        'new_due_date',
        'reason',
        'status',
        'admin_notes',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'original_due_date' => 'date',
        'new_due_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function studentGradeRecord(): BelongsTo
    {
        return $this->belongsTo(StudentGradeRecord::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDaysExtendedAttribute(): int
    {
        return $this->original_due_date->diffInDays($this->new_due_date);
    }
}
