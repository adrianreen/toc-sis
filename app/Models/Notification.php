<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Notification extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'data',
        'is_read',
        'email_sent',
        'read_at',
        'scheduled_for'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'email_sent' => 'boolean',
        'read_at' => 'datetime',
        'scheduled_for' => 'datetime'
    ];

    // Notification types
    public const TYPE_ASSESSMENT_DUE = 'assessment_due';
    public const TYPE_GRADE_RELEASED = 'grade_released';
    public const TYPE_APPROVAL_REQUIRED = 'approval_required';
    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_EXTENSION_APPROVED = 'extension_approved';
    public const TYPE_DEFERRAL_APPROVED = 'deferral_approved';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_for')
                    ->where('scheduled_for', '<=', now());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'title', 'is_read'])
            ->logOnlyDirty();
    }
}
