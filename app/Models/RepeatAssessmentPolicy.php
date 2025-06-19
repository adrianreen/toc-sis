<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RepeatAssessmentPolicy extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'timing_type',
        'payment_required',
        'default_payment_amount',
        'payment_amount_configurable',
        'grade_cap_percentage',
        'grade_cap_configurable',
        'max_attempts',
        'max_attempts_configurable',
        'workflow_stages',
        'notification_schedule',
        'scheduling_rules',
        'policy_rules',
        'is_active',
    ];

    protected $casts = [
        'payment_required' => 'boolean',
        'default_payment_amount' => 'decimal:2',
        'payment_amount_configurable' => 'boolean',
        'grade_cap_percentage' => 'decimal:2',
        'grade_cap_configurable' => 'boolean',
        'max_attempts_configurable' => 'boolean',
        'workflow_stages' => 'array',
        'notification_schedule' => 'array',
        'scheduling_rules' => 'array',
        'policy_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'timing_type', 'payment_required', 'default_payment_amount',
                'grade_cap_percentage', 'max_attempts', 'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class, 'repeat_assessment_policy_id');
    }

    public function programmeTypes(): HasMany
    {
        return $this->hasMany(ProgrammeType::class, 'default_repeat_policy_id');
    }

    public function repeatAssessments(): HasMany
    {
        return $this->hasMany(RepeatAssessment::class, 'policy_id');
    }

    // Policy application methods
    public function getWorkflowStages(): array
    {
        return $this->workflow_stages ?? [
            'identified',
            'notified',
            'payment_pending',
            'moodle_setup',
            'active',
            'completed'
        ];
    }

    public function getNotificationSchedule(): array
    {
        return $this->notification_schedule ?? [
            'assessment_reminder' => ['days' => [7, 3, 1]],
            'payment_reminder' => ['days' => [14, 7, 3]],
            'completion_reminder' => ['days' => [7, 1]]
        ];
    }

    public function getPaymentAmount(?float $override = null): float
    {
        if ($override !== null && $this->payment_amount_configurable) {
            return $override;
        }
        
        return $this->default_payment_amount ?? 50.00;
    }

    public function getGradeCap(?float $override = null): ?float
    {
        if ($override !== null && $this->grade_cap_configurable) {
            return $override;
        }
        
        return $this->grade_cap_percentage;
    }

    public function getMaxAttempts(?int $override = null): int
    {
        if ($override !== null && $this->max_attempts_configurable) {
            return $override;
        }
        
        return $this->max_attempts ?? 3;
    }

    public function calculateRepeatDueDate(\DateTime $baseDate): \DateTime
    {
        $rules = $this->scheduling_rules ?? [];
        
        switch ($this->timing_type) {
            case 'immediate':
                return (clone $baseDate)->add(new \DateInterval('P1D'));
                
            case 'end_of_module':
                $weeks = $rules['weeks_after_module_end'] ?? 2;
                return (clone $baseDate)->add(new \DateInterval("P{$weeks}W"));
                
            case 'scheduled_period':
                return $this->getNextScheduledPeriod($baseDate);
                
            case 'next_intake':
                return $this->getNextIntakeDate($baseDate);
                
            case 'flexible':
                $days = $rules['flexible_days'] ?? 30;
                return (clone $baseDate)->add(new \DateInterval("P{$days}D"));
                
            default:
                return (clone $baseDate)->add(new \DateInterval('P2W'));
        }
    }

    protected function getNextScheduledPeriod(\DateTime $baseDate): \DateTime
    {
        $rules = $this->scheduling_rules ?? [];
        $periods = $rules['scheduled_periods'] ?? [];
        
        if (empty($periods)) {
            return (clone $baseDate)->add(new \DateInterval('P8W'));
        }
        
        $currentYear = $baseDate->format('Y');
        $nextPeriod = null;
        
        foreach ($periods as $period) {
            $periodDate = \DateTime::createFromFormat('Y-m-d', $currentYear . '-' . $period);
            if ($periodDate > $baseDate) {
                $nextPeriod = $periodDate;
                break;
            }
        }
        
        // If no period found in current year, use first period of next year
        if (!$nextPeriod) {
            $nextYear = $currentYear + 1;
            $firstPeriod = reset($periods);
            $nextPeriod = \DateTime::createFromFormat('Y-m-d', $nextYear . '-' . $firstPeriod);
        }
        
        return $nextPeriod;
    }

    protected function getNextIntakeDate(\DateTime $baseDate): \DateTime
    {
        // This would integrate with programme intake scheduling
        // For now, default to 12 weeks
        return (clone $baseDate)->add(new \DateInterval('P12W'));
    }

    // Validation methods
    public function validatePolicyConfiguration(): array
    {
        $errors = [];
        
        if (!in_array($this->timing_type, ['immediate', 'end_of_module', 'scheduled_period', 'next_intake', 'flexible'])) {
            $errors[] = 'Invalid timing type';
        }
        
        if ($this->payment_required && $this->default_payment_amount <= 0) {
            $errors[] = 'Default payment amount must be greater than 0 when payment is required';
        }
        
        if ($this->grade_cap_percentage && ($this->grade_cap_percentage < 0 || $this->grade_cap_percentage > 100)) {
            $errors[] = 'Grade cap percentage must be between 0 and 100';
        }
        
        if ($this->max_attempts <= 0) {
            $errors[] = 'Max attempts must be greater than 0';
        }
        
        return $errors;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTimingType($query, string $type)
    {
        return $query->where('timing_type', $type);
    }

    public function scopeRequiresPayment($query, bool $required = true)
    {
        return $query->where('payment_required', $required);
    }

    // Factory methods for common policies
    public static function createImmediatePolicy(): self
    {
        return self::create([
            'name' => 'Immediate Repeat',
            'description' => 'Students can repeat immediately after grade is released',
            'timing_type' => 'immediate',
            'payment_required' => true,
            'default_payment_amount' => 50.00,
            'grade_cap_percentage' => 40.0,
            'max_attempts' => 3,
            'workflow_stages' => ['identified', 'payment', 'active', 'completed'],
        ]);
    }

    public static function createEndOfModulePolicy(): self
    {
        return self::create([
            'name' => 'End of Module Repeat',
            'description' => 'Students can repeat at the end of the module period',
            'timing_type' => 'end_of_module',
            'payment_required' => true,
            'default_payment_amount' => 75.00,
            'grade_cap_percentage' => 40.0,
            'max_attempts' => 2,
            'scheduling_rules' => ['weeks_after_module_end' => 2],
        ]);
    }

    public static function createScheduledPeriodPolicy(): self
    {
        return self::create([
            'name' => 'Scheduled Resit Periods',
            'description' => 'Students can repeat during scheduled resit periods',
            'timing_type' => 'scheduled_period',
            'payment_required' => true,
            'default_payment_amount' => 100.00,
            'grade_cap_percentage' => null, // No cap for scheduled resits
            'max_attempts' => 2,
            'scheduling_rules' => [
                'scheduled_periods' => ['01-15', '05-15', '09-15'] // Jan 15, May 15, Sep 15
            ],
        ]);
    }
}