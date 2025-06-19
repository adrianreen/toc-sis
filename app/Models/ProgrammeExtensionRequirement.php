<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProgrammeExtensionRequirement extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'programme_id',
        'extension_type',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'is_required',
        'required_for_enrollment',
        'required_for_progression',
        'required_for_completion',
        'display_order',
        'description',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
        'required_for_enrollment' => 'boolean',
        'required_for_progression' => 'boolean',
        'required_for_completion' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['field_name', 'field_type', 'is_required', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeForEnrollment($query)
    {
        return $query->where('required_for_enrollment', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('extension_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}