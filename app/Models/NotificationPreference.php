<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'in_app_enabled',
        'advance_days',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
        'advance_days' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getDefaultPreferences(): array
    {
        return [
            Notification::TYPE_ASSESSMENT_DUE => [
                'email_enabled' => true,
                'in_app_enabled' => true,
                'advance_days' => 3,
            ],
            Notification::TYPE_GRADE_RELEASED => [
                'email_enabled' => true,
                'in_app_enabled' => true,
                'advance_days' => 0,
            ],
            Notification::TYPE_APPROVAL_REQUIRED => [
                'email_enabled' => true,
                'in_app_enabled' => true,
                'advance_days' => 0,
            ],
            Notification::TYPE_ANNOUNCEMENT => [
                'email_enabled' => true,
                'in_app_enabled' => true,
                'advance_days' => 0,
            ],
            Notification::TYPE_EXTENSION_APPROVED => [
                'email_enabled' => true,
                'in_app_enabled' => true,
                'advance_days' => 0,
            ],
            Notification::TYPE_DEFERRAL_APPROVED => [
                'email_enabled' => true,
                'in_app_enabled' => true,
                'advance_days' => 0,
            ],
        ];
    }
}
