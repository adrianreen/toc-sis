<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Module extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'title', 
        'description',
        'credits',
        'hours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'title', 'credits', 'is_active'])
            ->logOnlyDirty();
    }

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

    public function moduleInstances(): HasMany
    {
        return $this->hasMany(ModuleInstance::class);
    }
}