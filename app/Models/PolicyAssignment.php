<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyAssignment extends Model
{
    protected $fillable = [
        'policy_id',
        'programme_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the policy that owns this assignment
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    /**
     * Get the programme that owns this assignment
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }
}
