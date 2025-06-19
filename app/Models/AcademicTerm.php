<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AcademicTerm extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'term_type',
        'term_number',
        'academic_year',
        'start_date',
        'end_date',
        'enrollment_open_date',
        'enrollment_close_date',
        'late_enrollment_close_date',
        'is_active',
        'is_current',
        'term_metadata',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'enrollment_open_date' => 'date',
        'enrollment_close_date' => 'date',
        'late_enrollment_close_date' => 'date',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
        'term_metadata' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'term_type', 'academic_year', 'start_date', 'end_date',
                'is_active', 'is_current'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function moduleInstances(): HasMany
    {
        return $this->hasMany(ModuleInstance::class);
    }

    public function programmeIntakes(): HasMany
    {
        return $this->hasMany(ProgrammeIntake::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(AcademicCalendarEvent::class);
    }

    // Term management
    public function isCurrent(): bool
    {
        return $this->is_current;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isEnrollmentOpen(): bool
    {
        $now = now()->toDateString();
        
        if ($this->enrollment_open_date && $now < $this->enrollment_open_date->toDateString()) {
            return false;
        }
        
        if ($this->enrollment_close_date && $now > $this->enrollment_close_date->toDateString()) {
            return false;
        }
        
        return $this->isActive();
    }

    public function isLateEnrollmentOpen(): bool
    {
        if (!$this->late_enrollment_close_date) {
            return false;
        }
        
        $now = now()->toDateString();
        
        return $now <= $this->late_enrollment_close_date->toDateString() && 
               $now > $this->enrollment_close_date->toDateString();
    }

    public function hasStarted(): bool
    {
        return now() >= $this->start_date;
    }

    public function hasEnded(): bool
    {
        return now() > $this->end_date;
    }

    public function isInProgress(): bool
    {
        return $this->hasStarted() && !$this->hasEnded();
    }

    // Academic calendar methods
    public function getDurationWeeks(): int
    {
        return $this->start_date->diffInWeeks($this->end_date);
    }

    public function getDurationDays(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getProgressPercentage(): float
    {
        if (!$this->hasStarted()) {
            return 0.0;
        }
        
        if ($this->hasEnded()) {
            return 100.0;
        }
        
        $totalDays = $this->getDurationDays();
        $elapsedDays = $this->start_date->diffInDays(now());
        
        return min(100.0, ($elapsedDays / $totalDays) * 100);
    }

    public function getRemainingDays(): int
    {
        if ($this->hasEnded()) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->end_date));
    }

    // Term metadata
    public function getMetadata(string $key = null): mixed
    {
        if ($key) {
            return data_get($this->term_metadata, $key);
        }
        
        return $this->term_metadata ?? [];
    }

    public function setMetadata(string $key, mixed $value): void
    {
        $metadata = $this->term_metadata ?? [];
        data_set($metadata, $key, $value);
        $this->update(['term_metadata' => $metadata]);
    }

    // Current term management
    public function markAsCurrent(): void
    {
        // Only one term can be current at a time
        static::where('is_current', true)->update(['is_current' => false]);
        
        $this->update(['is_current' => true]);
        
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->log('Academic term marked as current');
    }

    public function getEnrollmentStatus(): array
    {
        $now = now()->toDateString();
        
        if (!$this->enrollment_open_date) {
            return [
                'status' => 'no_enrollment',
                'message' => 'No enrollment period defined'
            ];
        }
        
        if ($now < $this->enrollment_open_date->toDateString()) {
            return [
                'status' => 'not_open',
                'message' => 'Enrollment opens on ' . $this->enrollment_open_date->format('M d, Y'),
                'opens_in_days' => now()->diffInDays($this->enrollment_open_date)
            ];
        }
        
        if ($this->enrollment_close_date && $now <= $this->enrollment_close_date->toDateString()) {
            return [
                'status' => 'open',
                'message' => 'Enrollment is open',
                'closes_in_days' => now()->diffInDays($this->enrollment_close_date)
            ];
        }
        
        if ($this->isLateEnrollmentOpen()) {
            return [
                'status' => 'late_enrollment',
                'message' => 'Late enrollment available',
                'closes_in_days' => now()->diffInDays($this->late_enrollment_close_date)
            ];
        }
        
        return [
            'status' => 'closed',
            'message' => 'Enrollment period has ended'
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeByTermType($query, string $type)
    {
        return $query->where('term_type', $type);
    }

    public function scopeEnrollmentOpen($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('enrollment_open_date')
                          ->orWhere('enrollment_open_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('enrollment_close_date')
                          ->orWhere('enrollment_close_date', '>=', now());
                    });
    }

    public function scopeInProgress($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('end_date', '<', now());
    }

    // Factory methods
    public static function createSemester(int $year, int $semesterNumber, \DateTime $startDate, \DateTime $endDate): self
    {
        return self::create([
            'name' => "Semester {$semesterNumber} {$year}",
            'term_type' => 'semester',
            'term_number' => $semesterNumber,
            'academic_year' => $year,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'enrollment_open_date' => (clone $startDate)->sub(new \DateInterval('P8W')),
            'enrollment_close_date' => (clone $startDate)->sub(new \DateInterval('P1W')),
            'late_enrollment_close_date' => (clone $startDate)->add(new \DateInterval('P2W')),
            'is_active' => true,
        ]);
    }

    public static function createAcademicYear(int $startYear): self
    {
        $startDate = new \DateTime("{$startYear}-09-01");
        $endDate = new \DateTime(($startYear + 1) . "-08-31");
        
        return self::create([
            'name' => "Academic Year {$startYear}-" . ($startYear + 1),
            'term_type' => 'academic_year',
            'term_number' => 1,
            'academic_year' => $startYear,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'enrollment_open_date' => (clone $startDate)->sub(new \DateInterval('P3M')),
            'enrollment_close_date' => (clone $startDate)->sub(new \DateInterval('P1W')),
            'is_active' => true,
        ]);
    }

    public static function getCurrentTerm(): ?self
    {
        return self::current()->first();
    }

    public static function getTermForDate(\DateTime $date): ?self
    {
        return self::active()
                  ->where('start_date', '<=', $date)
                  ->where('end_date', '>=', $date)
                  ->first();
    }
}