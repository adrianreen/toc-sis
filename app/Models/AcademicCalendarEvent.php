<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AcademicCalendarEvent extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'academic_term_id',
        'event_name',
        'event_type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'affects_all_programmes',
        'affected_programme_ids',
        'description',
        'is_holiday',
        'blocks_assessments',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'affects_all_programmes' => 'boolean',
        'affected_programme_ids' => 'array',
        'is_holiday' => 'boolean',
        'blocks_assessments' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'event_name', 'event_type', 'start_date', 'end_date',
                'affects_all_programmes', 'is_holiday', 'blocks_assessments'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function academicTerm(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    // Event type methods
    public function isHoliday(): bool
    {
        return $this->is_holiday;
    }

    public function blocksAssessments(): bool
    {
        return $this->blocks_assessments;
    }

    public function affectsAllProgrammes(): bool
    {
        return $this->affects_all_programmes;
    }

    public function isDeadline(): bool
    {
        return $this->event_type === 'deadline';
    }

    public function isExamPeriod(): bool
    {
        return $this->event_type === 'exam_period';
    }

    public function isBreak(): bool
    {
        return $this->event_type === 'break';
    }

    // Date and time methods
    public function isMultiDay(): bool
    {
        return $this->end_date && $this->start_date->ne($this->end_date);
    }

    public function isAllDay(): bool
    {
        return !$this->start_time && !$this->end_time;
    }

    public function getDurationDays(): int
    {
        if (!$this->end_date) {
            return 1;
        }
        
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function isCurrentlyActive(): bool
    {
        $now = now();
        $today = $now->toDateString();
        
        // Check date range
        if ($today < $this->start_date->toDateString()) {
            return false;
        }
        
        if ($this->end_date && $today > $this->end_date->toDateString()) {
            return false;
        }
        
        // For single-day events with time, check time range
        if (!$this->isMultiDay() && ($this->start_time || $this->end_time)) {
            if ($this->start_time && $now->format('H:i') < $this->start_time->format('H:i')) {
                return false;
            }
            
            if ($this->end_time && $now->format('H:i') > $this->end_time->format('H:i')) {
                return false;
            }
        }
        
        return true;
    }

    public function isUpcoming(): bool
    {
        return $this->start_date > now();
    }

    public function isPast(): bool
    {
        $endDate = $this->end_date ?? $this->start_date;
        return $endDate < now();
    }

    public function getDaysUntilStart(): int
    {
        if ($this->isCurrentlyActive() || $this->isPast()) {
            return 0;
        }
        
        return now()->diffInDays($this->start_date);
    }

    // Programme filtering
    public function affectsProgramme(int $programmeId): bool
    {
        if ($this->affectsAllProgrammes()) {
            return true;
        }
        
        return in_array($programmeId, $this->affected_programme_ids ?? []);
    }

    public function getAffectedProgrammes(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->affectsAllProgrammes()) {
            return Programme::active()->get();
        }
        
        if (empty($this->affected_programme_ids)) {
            return collect();
        }
        
        return Programme::whereIn('id', $this->affected_programme_ids)->get();
    }

    // Assessment conflict checking
    public function conflictsWithAssessments(): bool
    {
        if (!$this->blocksAssessments()) {
            return false;
        }
        
        // Check if there are any assessments scheduled during this event
        $startDate = $this->start_date;
        $endDate = $this->end_date ?? $this->start_date;
        
        $conflictingAssessments = StudentAssessment::whereBetween('due_date', [$startDate, $endDate]);
        
        if (!$this->affectsAllProgrammes()) {
            $conflictingAssessments->whereHas('studentModuleEnrolment.moduleInstance.cohort.programme', function ($query) {
                $query->whereIn('id', $this->affected_programme_ids ?? []);
            });
        }
        
        return $conflictingAssessments->exists();
    }

    // Display helpers
    public function getEventTypeLabel(): string
    {
        return match($this->event_type) {
            'holiday' => 'Holiday',
            'deadline' => 'Deadline',
            'exam_period' => 'Exam Period',
            'break' => 'Break',
            'other' => 'Event',
            default => ucfirst($this->event_type)
        };
    }

    public function getDateRangeDisplay(): string
    {
        if (!$this->isMultiDay()) {
            $display = $this->start_date->format('M d, Y');
            
            if (!$this->isAllDay()) {
                if ($this->start_time && $this->end_time) {
                    $display .= ' ' . $this->start_time->format('H:i') . '-' . $this->end_time->format('H:i');
                } elseif ($this->start_time) {
                    $display .= ' from ' . $this->start_time->format('H:i');
                }
            }
            
            return $display;
        }
        
        return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getStatusDisplay(): string
    {
        if ($this->isCurrentlyActive()) {
            return 'Active';
        } elseif ($this->isUpcoming()) {
            $days = $this->getDaysUntilStart();
            return "In {$days} day" . ($days !== 1 ? 's' : '');
        } else {
            return 'Past';
        }
    }

    public function getEventTypeColor(): string
    {
        return match($this->event_type) {
            'holiday' => 'green',
            'deadline' => 'red',
            'exam_period' => 'orange',
            'break' => 'blue',
            'other' => 'gray',
            default => 'gray'
        };
    }

    // Scopes
    public function scopeByEventType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeHolidays($query)
    {
        return $query->where('is_holiday', true);
    }

    public function scopeBlockingAssessments($query)
    {
        return $query->where('blocks_assessments', true);
    }

    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeInDateRange($query, \DateTime $startDate, \DateTime $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                       ->where('end_date', '>=', $endDate);
              });
        });
    }

    public function scopeForProgramme($query, int $programmeId)
    {
        return $query->where(function ($q) use ($programmeId) {
            $q->where('affects_all_programmes', true)
              ->orWhereRaw('JSON_CONTAINS(affected_programme_ids, ?)', [json_encode($programmeId)]);
        });
    }

    // Factory methods
    public static function createHoliday(string $name, \DateTime $startDate, \DateTime $endDate = null): self
    {
        return self::create([
            'event_name' => $name,
            'event_type' => 'holiday',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'affects_all_programmes' => true,
            'is_holiday' => true,
            'blocks_assessments' => true,
        ]);
    }

    public static function createExamPeriod(string $name, \DateTime $startDate, \DateTime $endDate, AcademicTerm $term = null): self
    {
        return self::create([
            'academic_term_id' => $term?->id,
            'event_name' => $name,
            'event_type' => 'exam_period',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'affects_all_programmes' => true,
            'blocks_assessments' => false, // Exams are assessments
        ]);
    }

    public static function createDeadline(string $name, \DateTime $date, \DateTime $time = null, array $programmeIds = []): self
    {
        return self::create([
            'event_name' => $name,
            'event_type' => 'deadline',
            'start_date' => $date,
            'start_time' => $time,
            'affects_all_programmes' => empty($programmeIds),
            'affected_programme_ids' => $programmeIds,
            'blocks_assessments' => false,
        ]);
    }
}