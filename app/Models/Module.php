<?php
// app/Models/Module.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'credits',
        'hours',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function programmes(): BelongsToMany
    {
        return $this->belongsToMany(Programme::class, 'programme_modules')
            ->withPivot('sequence', 'is_mandatory')
            ->withTimestamps();
    }

    public function assessmentComponents(): HasMany
{
    return $this->hasMany(AssessmentComponent::class);
}
}