<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProgrammeIntake extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'programme_id',
        'intake_name',
        'intake_date',
        'application_open_date',
        'application_close_date',
        'enrollment_deadline',
        'max_students',
        'current_enrollments',
        'confirmed_enrollments',
        'status',
        'programme_start_date',
        'programme_end_date',
        'cohort_id',
        'academic_term_id',
        'intake_metadata',
        'description',
        'requirements',
    ];

    protected $casts = [
        'intake_date' => 'date',
        'application_open_date' => 'date',
        'application_close_date' => 'date',
        'enrollment_deadline' => 'date',
        'programme_start_date' => 'date',
        'programme_end_date' => 'date',
        'intake_metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'intake_name', 'intake_date', 'status', 'max_students',
                'current_enrollments', 'programme_start_date'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(IntakeApplication::class);
    }

    // Status management
    public function isPlanning(): bool
    {
        return $this->status === 'planning';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isFull(): bool
    {
        return $this->status === 'full';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    // Application window management
    public function isApplicationPeriodOpen(): bool
    {
        if (!$this->isOpen()) {
            return false;
        }

        $now = now()->toDateString();
        
        if ($this->application_open_date && $now < $this->application_open_date->toDateString()) {
            return false;
        }
        
        if ($this->application_close_date && $now > $this->application_close_date->toDateString()) {
            return false;
        }
        
        return true;
    }

    public function canAcceptApplications(): bool
    {
        return $this->isApplicationPeriodOpen() && $this->hasCapacity();
    }

    public function hasCapacity(): bool
    {
        if (!$this->max_students) {
            return true;
        }
        
        return $this->current_enrollments < $this->max_students;
    }

    public function getRemainingCapacity(): ?int
    {
        if (!$this->max_students) {
            return null;
        }
        
        return max(0, $this->max_students - $this->current_enrollments);
    }

    public function getCapacityPercentage(): float
    {
        if (!$this->max_students) {
            return 0.0;
        }
        
        return ($this->current_enrollments / $this->max_students) * 100;
    }

    // Enrollment management
    public function incrementEnrollment(): void
    {
        $this->increment('current_enrollments');
        
        // Check if intake should be marked as full
        if ($this->max_students && $this->current_enrollments >= $this->max_students) {
            $this->update(['status' => 'full']);
        }
    }

    public function decrementEnrollment(): void
    {
        $this->decrement('current_enrollments');
        
        // Reopen intake if it was full
        if ($this->isFull() && $this->hasCapacity()) {
            $this->update(['status' => 'open']);
        }
    }

    public function confirmEnrollment(): void
    {
        $this->increment('confirmed_enrollments');
    }

    public function unconfirmEnrollment(): void
    {
        $this->decrement('confirmed_enrollments');
    }

    // Status transitions
    public function openForApplications(): bool
    {
        if (!$this->isPlanning()) {
            return false;
        }

        $this->update(['status' => 'open']);
        
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log('Programme intake opened for applications');
            
        return true;
    }

    public function closeApplications(): bool
    {
        if (!$this->isOpen()) {
            return false;
        }

        $this->update(['status' => 'closed']);
        
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log('Programme intake closed for applications');
            
        return true;
    }

    public function cancel(string $reason = null): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        $this->update([
            'status' => 'cancelled',
            'intake_metadata' => array_merge($this->intake_metadata ?? [], [
                'cancellation_reason' => $reason,
                'cancelled_at' => now()->toISOString(),
                'cancelled_by' => auth()->id(),
            ])
        ]);
        
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties(['reason' => $reason])
            ->log('Programme intake cancelled');
            
        return true;
    }

    public function markCompleted(): bool
    {
        $this->update(['status' => 'completed']);
        
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log('Programme intake marked as completed');
            
        return true;
    }

    // Application statistics
    public function getApplicationStatistics(): array
    {
        $applications = $this->applications();
        
        return [
            'total' => $applications->count(),
            'submitted' => $applications->where('application_status', 'submitted')->count(),
            'under_review' => $applications->where('application_status', 'under_review')->count(),
            'accepted' => $applications->where('application_status', 'accepted')->count(),
            'conditionally_accepted' => $applications->where('application_status', 'conditionally_accepted')->count(),
            'rejected' => $applications->where('application_status', 'rejected')->count(),
            'withdrawn' => $applications->where('application_status', 'withdrawn')->count(),
        ];
    }

    // Date calculations
    public function getDaysUntilIntake(): int
    {
        if ($this->intake_date < now()) {
            return 0;
        }
        
        return now()->diffInDays($this->intake_date);
    }

    public function getDaysUntilApplicationClose(): ?int
    {
        if (!$this->application_close_date || $this->application_close_date < now()) {
            return null;
        }
        
        return now()->diffInDays($this->application_close_date);
    }

    public function getDaysUntilEnrollmentDeadline(): ?int
    {
        if (!$this->enrollment_deadline || $this->enrollment_deadline < now()) {
            return null;
        }
        
        return now()->diffInDays($this->enrollment_deadline);
    }

    // Metadata management
    public function getMetadata(string $key = null): mixed
    {
        if ($key) {
            return data_get($this->intake_metadata, $key);
        }
        
        return $this->intake_metadata ?? [];
    }

    public function setMetadata(string $key, mixed $value): void
    {
        $metadata = $this->intake_metadata ?? [];
        data_set($metadata, $key, $value);
        $this->update(['intake_metadata' => $metadata]);
    }

    // Cohort integration
    public function createCohort(): ?Cohort
    {
        if ($this->cohort_id || !$this->programme) {
            return null;
        }

        $cohort = Cohort::create([
            'name' => $this->intake_name,
            'programme_id' => $this->programme_id,
            'start_date' => $this->programme_start_date,
            'end_date' => $this->programme_end_date,
            'max_students' => $this->max_students,
            'status' => 'planning',
        ]);

        $this->update(['cohort_id' => $cohort->id]);
        
        return $cohort;
    }

    // Display helpers
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'planning' => 'gray',
            'open' => 'green',
            'closed' => 'yellow',
            'full' => 'orange',
            'cancelled' => 'red',
            'completed' => 'blue',
            default => 'gray'
        };
    }

    public function getApplicationWindowDescription(): string
    {
        if (!$this->application_open_date && !$this->application_close_date) {
            return 'No application window defined';
        }
        
        $description = 'Applications';
        
        if ($this->application_open_date) {
            $description .= ' open ' . $this->application_open_date->format('M d, Y');
        }
        
        if ($this->application_close_date) {
            $description .= ($this->application_open_date ? ' - ' : ' close ') . $this->application_close_date->format('M d, Y');
        }
        
        return $description;
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeAcceptingApplications($query)
    {
        return $query->where('status', 'open')
                    ->where(function ($q) {
                        $q->whereNull('application_open_date')
                          ->orWhere('application_open_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('application_close_date')
                          ->orWhere('application_close_date', '>=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('max_students')
                          ->orWhereRaw('current_enrollments < max_students');
                    });
    }

    public function scopeByProgramme($query, int $programmeId)
    {
        return $query->where('programme_id', $programmeId);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->whereYear('intake_date', $year);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('intake_date', '>', now());
    }

    public function scopeInProgress($query)
    {
        return $query->where('programme_start_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('programme_end_date')
                          ->orWhere('programme_end_date', '>=', now());
                    });
    }

    // Factory methods
    public static function createForProgramme(Programme $programme, \DateTime $intakeDate, string $name = null): self
    {
        $name = $name ?? $programme->title . ' - ' . $intakeDate->format('M Y');
        
        return self::create([
            'programme_id' => $programme->id,
            'intake_name' => $name,
            'intake_date' => $intakeDate,
            'programme_start_date' => $intakeDate,
            'programme_end_date' => (clone $intakeDate)->add(new \DateInterval('P' . $programme->getTypicalDuration() . 'M')),
            'application_open_date' => (clone $intakeDate)->sub(new \DateInterval('P3M')),
            'application_close_date' => (clone $intakeDate)->sub(new \DateInterval('P2W')),
            'enrollment_deadline' => (clone $intakeDate)->sub(new \DateInterval('P1W')),
            'status' => 'planning',
        ]);
    }
}