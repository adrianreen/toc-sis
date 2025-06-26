<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'module_code',
        'credit_value',
        'assessment_strategy',
        'allows_standalone_enrolment',
        'async_instance_cadence',
        'default_pass_mark',
    ];

    protected $casts = [
        'credit_value' => 'integer',
        'assessment_strategy' => 'array',
        'allows_standalone_enrolment' => 'boolean',
        'default_pass_mark' => 'decimal:2',
    ];

    public static function rules($id = null)
    {
        return [
            'title' => 'required|string|max:255',
            'module_code' => 'required|string|max:20'.($id ? '|unique:modules,module_code,'.$id : '|unique:modules'),
            'credit_value' => 'required|integer|min:1|max:60',
            'description' => 'nullable|string',
            'nfq_level' => 'nullable|integer|min:1|max:10',
            'default_pass_mark' => 'nullable|numeric|min:0|max:100',
            'allows_standalone_enrolment' => 'boolean',
            'async_instance_cadence' => 'required|in:monthly,quarterly,bi_annually,annually',
            'assessment_strategy' => 'required|array|min:1',
            'assessment_strategy.*.component_name' => 'required|string|max:255',
            'assessment_strategy.*.weighting' => 'required|numeric|min:0|max:100',
            'assessment_strategy.*.is_must_pass' => 'boolean',
            'assessment_strategy.*.component_pass_mark' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function moduleInstances(): HasMany
    {
        return $this->hasMany(ModuleInstance::class);
    }

    public function getAssessmentComponentsAttribute()
    {
        $components = [];
        foreach ($this->assessment_strategy as $component) {
            $components[] = (object) $component;
        }

        return $components;
    }

    /**
     * Get the pass mark for a specific assessment component
     */
    public function getComponentPassMark(string $componentName): float
    {
        foreach ($this->assessment_strategy as $component) {
            if ($component['component_name'] === $componentName) {
                return $component['component_pass_mark'] ?? $this->getDefaultPassMark();
            }
        }

        return $this->getDefaultPassMark();
    }

    /**
     * Get the default pass mark for this module
     */
    public function getDefaultPassMark(): float
    {
        return $this->default_pass_mark ?? 40.0; // Default to 40% if not set
    }

    /**
     * Check if a specific component is marked as must-pass
     */
    public function isComponentMustPass(string $componentName): bool
    {
        foreach ($this->assessment_strategy as $component) {
            if ($component['component_name'] === $componentName) {
                return $component['is_must_pass'] ?? false;
            }
        }

        return false;
    }

    /**
     * Check if a grade record represents a failed assessment
     */
    public function isGradeRecordFailed(object $gradeRecord): bool
    {
        if (! $gradeRecord->isGraded()) {
            return false;
        }

        $componentPassMark = $this->getComponentPassMark($gradeRecord->assessment_component_name);

        return $gradeRecord->percentage < $componentPassMark;
    }
}
