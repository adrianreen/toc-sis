<?php
// app/Models/Programme.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'enrolment_type',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    public function cohorts()
    {
        return $this->hasMany(Cohort::class);
    }

    public function isCohortBased()
    {
        return $this->enrolment_type === 'cohort';
    }

    public function isRolling()
    {
        return $this->enrolment_type === 'rolling';
    }

    public function isAcademicTerm()
    {
        return $this->enrolment_type === 'academic_term';
    }
    public function modules(): BelongsToMany
{
    return $this->belongsToMany(Module::class, 'programme_modules')
        ->withPivot('sequence', 'is_mandatory')
        ->orderBy('pivot_sequence')
        ->withTimestamps();
}

public function enrolments(): HasMany
{
    return $this->hasMany(Enrolment::class);
}
}