<?php
// app/Models/AssessmentComponent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'name',
        'type',
        'weight',
        'sequence',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function studentAssessments(): HasMany
    {
        return $this->hasMany(StudentAssessment::class);
    }
}