<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgrammeInstance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'programme_id',
        'label',
        'intake_start_date',
        'intake_end_date',
        'default_delivery_style',
    ];

    protected $casts = [
        'intake_start_date' => 'date',
        'intake_end_date' => 'date',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function moduleInstances(): BelongsToMany
    {
        return $this->belongsToMany(ModuleInstance::class, 'programme_instance_curriculum');
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }
}
