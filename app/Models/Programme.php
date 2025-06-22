<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programme extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'awarding_body',
        'nfq_level',
        'total_credits',
        'description',
        'learning_outcomes',
    ];

    protected $casts = [
        'nfq_level' => 'integer',
        'total_credits' => 'integer',
    ];

    public static function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'awarding_body' => 'required|string|max:255',
            'nfq_level' => 'required|integer|min:1|max:10',
            'total_credits' => 'required|integer|min:1|max:600',
            'description' => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
        ];
    }

    public function programmeInstances(): HasMany
    {
        return $this->hasMany(ProgrammeInstance::class);
    }
}
