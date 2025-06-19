<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class IntakeApplication extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'programme_intake_id',
        'student_id',
        'application_date',
        'application_status',
        'application_data',
        'requirements_status',
        'staff_notes',
        'decision_date',
        'reviewed_by',
    ];

    protected $casts = [
        'application_date' => 'date',
        'application_data' => 'array',
        'decision_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['application_status', 'decision_date', 'reviewed_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function programmeIntake(): BelongsTo
    {
        return $this->belongsTo(ProgrammeIntake::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Status methods
    public function isSubmitted(): bool
    {
        return $this->application_status === 'submitted';
    }

    public function isUnderReview(): bool
    {
        return $this->application_status === 'under_review';
    }

    public function isAccepted(): bool
    {
        return $this->application_status === 'accepted';
    }

    public function isConditionallyAccepted(): bool
    {
        return $this->application_status === 'conditionally_accepted';
    }

    public function isRejected(): bool
    {
        return $this->application_status === 'rejected';
    }

    public function isWithdrawn(): bool
    {
        return $this->application_status === 'withdrawn';
    }

    // Status updates
    public function accept(): void
    {
        $this->update([
            'application_status' => 'accepted',
            'decision_date' => now(),
            'reviewed_by' => auth()->id(),
        ]);
    }

    public function reject(string $reason = null): void
    {
        $this->update([
            'application_status' => 'rejected',
            'decision_date' => now(),
            'reviewed_by' => auth()->id(),
            'staff_notes' => ($this->staff_notes ? $this->staff_notes . "\n\n" : '') . 
                           "Rejected on " . now()->format('Y-m-d') . 
                           ($reason ? ": {$reason}" : ''),
        ]);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('application_status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('application_status', ['submitted', 'under_review']);
    }
}