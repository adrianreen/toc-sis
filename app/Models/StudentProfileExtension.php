<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StudentProfileExtension extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'student_id',
        'programme_id',
        'extension_type',
        'field_name',
        'field_label',
        'field_value',
        'field_type',
        'field_options',
        'is_required',
        'is_verified',
        'verified_date',
        'verified_by',
        'expiry_date',
        'status',
        'staff_notes',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
        'is_verified' => 'boolean',
        'verified_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['field_value', 'is_verified', 'status', 'verified_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Status methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expiry_date && $this->expiry_date < now());
    }

    public function verify(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_date' => now(),
            'verified_by' => auth()->id(),
            'status' => 'complete',
        ]);
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('extension_type', $type);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeForProgramme($query, int $programmeId)
    {
        return $query->where('programme_id', $programmeId);
    }
}