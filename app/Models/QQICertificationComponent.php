<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class QQICertificationComponent extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'qqi_certification_components';

    protected $fillable = [
        'qqi_certification_id',
        'module_id',
        'component_code',
        'component_grade',
        'component_result',
        'component_completion_date',
    ];

    protected $casts = [
        'component_grade' => 'decimal:2',
        'component_completion_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['component_code', 'component_grade', 'component_result', 'component_completion_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function qqiCertification(): BelongsTo
    {
        return $this->belongsTo(QQICertification::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    // Helper methods
    public function isPassing(): bool
    {
        return in_array(strtolower($this->component_result), ['pass', 'merit', 'distinction']);
    }

    public function getGradeDisplay(): string
    {
        if ($this->component_grade !== null) {
            return number_format($this->component_grade, 1) . '% (' . $this->component_result . ')';
        }
        
        return $this->component_result ?? 'Not graded';
    }

    // Scopes
    public function scopePassing($query)
    {
        return $query->whereIn('component_result', ['Pass', 'Merit', 'Distinction']);
    }

    public function scopeFailing($query)
    {
        return $query->where('component_result', 'Fail');
    }
}