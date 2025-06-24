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
    ];

    protected $casts = [
        'credit_value' => 'integer',
        'assessment_strategy' => 'array',
        'allows_standalone_enrolment' => 'boolean',
    ];

    public static function rules($id = null)
    {
        return [
            'title' => 'required|string|max:255',
            'module_code' => 'required|string|max:20'.($id ? '|unique:modules,module_code,'.$id : '|unique:modules'),
            'credit_value' => 'required|integer|min:1|max:60',
            'description' => 'nullable|string',
            'nfq_level' => 'nullable|integer|min:1|max:10',
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
}
