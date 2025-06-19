<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProgrammeType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'description',
        'awarding_body',
        'awarding_body_code',
        'nfq_level',
        'typical_duration_months',
        'credit_value',
        'minimum_pass_grade',
        'enrolment_type',
        'delivery_mode',
        'requires_placement',
        'requires_external_verification',
        'requires_portfolio_submission',
        'external_examiner_required',
        // Enhanced archetype configuration
        'archetype_config',
        'default_grading_scheme_id',
        'default_assessment_strategy_id',
        'default_module_progression_rule_id',
        'default_repeat_policy_id',
        'default_qqi_certification',
        'is_active',
    ];

    protected $casts = [
        'archetype_config' => 'array',
        'minimum_pass_grade' => 'decimal:2',
        'requires_placement' => 'boolean',
        'requires_external_verification' => 'boolean',
        'requires_portfolio_submission' => 'boolean',
        'external_examiner_required' => 'boolean',
        'default_qqi_certification' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'code', 'awarding_body', 'nfq_level', 
                'minimum_pass_grade', 'requires_placement', 
                'requires_external_verification', 'enrolment_type',
                'delivery_mode', 'archetype_config', 'is_active'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    public function gradingScheme(): BelongsTo
    {
        return $this->belongsTo(GradingScheme::class, 'default_grading_scheme_id');
    }

    public function defaultGradingScheme(): BelongsTo
    {
        return $this->belongsTo(GradingScheme::class, 'default_grading_scheme_id');
    }

    public function assessmentStrategy(): BelongsTo
    {
        return $this->belongsTo(AssessmentStrategy::class, 'default_assessment_strategy_id');
    }

    public function defaultAssessmentStrategy(): BelongsTo
    {
        return $this->belongsTo(AssessmentStrategy::class, 'default_assessment_strategy_id');
    }

    public function moduleProgressionRule(): BelongsTo
    {
        return $this->belongsTo(ModuleProgressionRule::class, 'default_module_progression_rule_id');
    }

    public function defaultModuleProgressionRule(): BelongsTo
    {
        return $this->belongsTo(ModuleProgressionRule::class, 'default_module_progression_rule_id');
    }

    public function defaultRepeatPolicy(): BelongsTo
    {
        return $this->belongsTo(RepeatAssessmentPolicy::class, 'default_repeat_policy_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByAwardingBody($query, string $body)
    {
        return $query->where('awarding_body', $body);
    }

    public function scopeByNfqLevel($query, string $level)
    {
        return $query->where('nfq_level', $level);
    }

    public function scopeByEnrolmentType($query, string $type)
    {
        return $query->where('enrolment_type', $type);
    }

    public function scopeByDeliveryMode($query, string $mode)
    {
        return $query->where('delivery_mode', $mode);
    }

    // Helper methods
    public function isQQILevel(): bool
    {
        return str_starts_with($this->awarding_body ?? '', 'QQI') || 
               in_array($this->nfq_level, ['5', '6']);
    }

    public function isDegreeLevel(): bool
    {
        return in_array($this->nfq_level, ['7', '8', '9', '10']) || 
               str_contains(strtolower($this->awarding_body ?? ''), 'university');
    }

    public function isELCType(): bool
    {
        return str_contains(strtolower($this->name), 'elc') || 
               str_contains(strtolower($this->name), 'early learning');
    }

    public function isOBUType(): bool
    {
        return str_contains(strtolower($this->awarding_body ?? ''), 'oxford brookes');
    }

    public function isSynchronousQQI(): bool
    {
        return $this->isQQILevel() && $this->enrolment_type === 'cohort';
    }

    public function isAsynchronousQQI(): bool
    {
        return $this->isQQILevel() && $this->enrolment_type === 'rolling';
    }

    // Archetype configuration methods
    public function getArchetypeConfig(string $configPath = null): mixed
    {
        $config = $this->archetype_config ?? $this->getDefaultArchetypeConfig();
        
        if ($configPath) {
            return data_get($config, $configPath);
        }
        
        return $config;
    }

    protected function getDefaultArchetypeConfig(): array
    {
        // Default configuration based on archetype detection
        if ($this->isELCType()) {
            return $this->getELCArchetypeConfig();
        } elseif ($this->isOBUType()) {
            return $this->getOBUArchetypeConfig();
        } elseif ($this->isSynchronousQQI()) {
            return $this->getSynchronousQQIConfig();
        } elseif ($this->isAsynchronousQQI()) {
            return $this->getAsynchronousQQIConfig();
        }
        
        return $this->getGenericArchetypeConfig();
    }

    protected function getELCArchetypeConfig(): array
    {
        return [
            'enrollment' => [
                'type' => 'cohort',
                'intake_pattern' => 'fixed_dates',
                'cohort_required' => true
            ],
            'delivery' => [
                'pattern' => 'concurrent',
                'supports_pillar_modules' => true,
                'academic_calendar' => 'programme_specific'
            ],
            'assessment' => [
                'generation_pattern' => 'cohort_scheduled',
                'due_date_calculation' => 'concurrent',
                'supports_direct_grading' => false
            ],
            'progression' => [
                'rule_type' => 'sequential',
                'prerequisite_enforcement' => 'strict'
            ],
            'repeat_assessment' => [
                'policy_name' => 'end_of_module',
                'payment_required' => true,
                'grade_cap_percentage' => 40.0
            ]
        ];
    }

    protected function getOBUArchetypeConfig(): array
    {
        return [
            'enrollment' => [
                'type' => 'cohort',
                'intake_pattern' => 'semester_based',
                'cohort_required' => true
            ],
            'delivery' => [
                'pattern' => 'semester_based',
                'supports_pillar_modules' => false,
                'academic_calendar' => 'academic_year'
            ],
            'assessment' => [
                'generation_pattern' => 'semester_based',
                'due_date_calculation' => 'semester_based',
                'supports_direct_grading' => false
            ],
            'progression' => [
                'rule_type' => 'credit_based',
                'prerequisite_enforcement' => 'advisory'
            ],
            'repeat_assessment' => [
                'policy_name' => 'scheduled_period',
                'payment_required' => true,
                'grade_cap_percentage' => null
            ]
        ];
    }

    protected function getSynchronousQQIConfig(): array
    {
        return [
            'enrollment' => [
                'type' => 'cohort',
                'intake_pattern' => 'fixed_dates',
                'cohort_required' => true
            ],
            'delivery' => [
                'pattern' => 'sequential',
                'supports_pillar_modules' => false,
                'academic_calendar' => 'programme_specific'
            ],
            'assessment' => [
                'generation_pattern' => 'cohort_scheduled',
                'due_date_calculation' => 'sequential',
                'supports_direct_grading' => false
            ],
            'progression' => [
                'rule_type' => 'sequential',
                'prerequisite_enforcement' => 'strict'
            ],
            'repeat_assessment' => [
                'policy_name' => 'end_of_module',
                'payment_required' => true,
                'grade_cap_percentage' => 40.0
            ]
        ];
    }

    protected function getAsynchronousQQIConfig(): array
    {
        return [
            'enrollment' => [
                'type' => 'rolling',
                'intake_pattern' => 'open_enrollment',
                'cohort_required' => false
            ],
            'delivery' => [
                'pattern' => 'flexible',
                'supports_pillar_modules' => false,
                'academic_calendar' => 'flexible'
            ],
            'assessment' => [
                'generation_pattern' => 'individual_paced',
                'due_date_calculation' => 'flexible',
                'supports_direct_grading' => true
            ],
            'progression' => [
                'rule_type' => 'flexible',
                'prerequisite_enforcement' => 'advisory'
            ],
            'repeat_assessment' => [
                'policy_name' => 'immediate',
                'payment_required' => true,
                'grade_cap_percentage' => 40.0
            ]
        ];
    }

    protected function getGenericArchetypeConfig(): array
    {
        return [
            'enrollment' => [
                'type' => $this->enrolment_type ?? 'cohort',
                'intake_pattern' => 'fixed_dates',
                'cohort_required' => true
            ],
            'delivery' => [
                'pattern' => 'sequential',
                'supports_pillar_modules' => false,
                'academic_calendar' => 'programme_specific'
            ],
            'assessment' => [
                'generation_pattern' => 'cohort_scheduled',
                'due_date_calculation' => 'sequential',
                'supports_direct_grading' => false
            ],
            'progression' => [
                'rule_type' => 'sequential',
                'prerequisite_enforcement' => 'strict'
            ],
            'repeat_assessment' => [
                'policy_name' => 'immediate',
                'payment_required' => true,
                'grade_cap_percentage' => 40.0
            ]
        ];
    }

    public function createProgrammeDefaults(): array
    {
        $config = $this->getArchetypeConfig();
        
        return [
            'programme_type_id' => $this->id,
            'awarding_body' => $this->awarding_body,
            'nfq_level' => $this->nfq_level,
            'credit_value' => $this->credit_value,
            'minimum_pass_grade' => $this->minimum_pass_grade,
            'typical_duration_months' => $this->typical_duration_months,
            'enrolment_type' => $config['enrollment']['type'] ?? 'cohort',
            'delivery_pattern' => $config['delivery']['pattern'] ?? 'sequential',
            'delivery_mode' => $this->delivery_mode,
            'requires_placement' => $this->requires_placement,
            'requires_external_verification' => $this->requires_external_verification,
            'requires_portfolio_submission' => $this->requires_portfolio_submission,
            'external_examiner_required' => $this->external_examiner_required,
            'requires_qqi_certification' => $this->default_qqi_certification,
            'uses_academic_calendar' => $config['delivery']['academic_calendar'] === 'academic_year',
            'grading_scheme_id' => $this->default_grading_scheme_id,
            'assessment_strategy_id' => $this->default_assessment_strategy_id,
            'module_progression_rule_id' => $this->default_module_progression_rule_id,
            'repeat_assessment_policy_id' => $this->default_repeat_policy_id,
        ];
    }

    // Configuration validation
    public function validateArchetypeConfig(): array
    {
        $config = $this->getArchetypeConfig();
        $errors = [];
        
        // Validate enrollment configuration
        if (!in_array($config['enrollment']['type'] ?? null, ['cohort', 'rolling', 'academic_term', 'standalone_modules'])) {
            $errors[] = 'Invalid enrollment type';
        }
        
        // Validate delivery configuration
        if (!in_array($config['delivery']['pattern'] ?? null, ['sequential', 'concurrent', 'semester_based', 'flexible'])) {
            $errors[] = 'Invalid delivery pattern';
        }
        
        // Validate assessment configuration
        if (!in_array($config['assessment']['generation_pattern'] ?? null, ['cohort_scheduled', 'individual_paced', 'semester_based', 'pillar_concurrent'])) {
            $errors[] = 'Invalid assessment generation pattern';
        }
        
        return $errors;
    }
    
    // Get configuration summary for display
    public function getConfigSummary(): array
    {
        $config = $this->getArchetypeConfig();
        
        return [
            'archetype_name' => $this->getArchetypeName(),
            'enrollment_type' => $config['enrollment']['type'] ?? 'cohort',
            'delivery_pattern' => $config['delivery']['pattern'] ?? 'sequential',
            'supports_pillar' => $config['delivery']['supports_pillar_modules'] ?? false,
            'uses_calendar' => $config['delivery']['academic_calendar'] === 'academic_year',
            'repeat_policy' => $config['repeat_assessment']['policy_name'] ?? 'immediate',
            'grade_cap' => $config['repeat_assessment']['grade_cap_percentage'],
        ];
    }
    
    public function getArchetypeName(): string
    {
        if ($this->isELCType()) {
            return 'ELC (Early Learning & Care)';
        } elseif ($this->isOBUType()) {
            return 'OBU (Oxford Brookes University)';
        } elseif ($this->isSynchronousQQI()) {
            return 'Synchronous QQI';
        } elseif ($this->isAsynchronousQQI()) {
            return 'Asynchronous QQI';
        }
        
        return 'Generic Programme Type';
    }
}