<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'policy_id',
        'user_id',
        'action',
        'viewed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the policy that owns this view
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    /**
     * Get the user that owns this view
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new view record
     */
    public static function logView(Policy $policy, User $user, string $action = 'viewed'): void
    {
        static::create([
            'policy_id' => $policy->id,
            'user_id' => $user->id,
            'action' => $action,
            'viewed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope for specific action types
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }
}
