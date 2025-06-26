<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTablePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'table_name',
        'visible_columns',
        'column_order',
        'column_widths',
        'sort_preferences',
    ];

    protected $casts = [
        'visible_columns' => 'array',
        'column_order' => 'array',
        'column_widths' => 'array',
        'sort_preferences' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get user's table preferences for a specific table
     */
    public static function getForUserAndTable($userId, $tableName)
    {
        return static::where('user_id', $userId)
            ->where('table_name', $tableName)
            ->first();
    }

    /**
     * Save or update user's table preferences
     */
    public static function savePreferences($userId, $tableName, $preferences)
    {
        return static::updateOrCreate(
            [
                'user_id' => $userId,
                'table_name' => $tableName,
            ],
            $preferences
        );
    }

    /**
     * Get default column configuration for students table
     */
    public static function getDefaultStudentColumns()
    {
        return [
            'checkbox' => [
                'key' => 'checkbox',
                'label' => '',
                'type' => 'checkbox',
                'sortable' => false,
                'width' => 50,
                'required' => true,
            ],
            'student' => [
                'key' => 'student',
                'label' => 'Student',
                'type' => 'student_info',
                'sortable' => true,
                'width' => 250,
                'required' => true,
            ],
            'student_number' => [
                'key' => 'student_number',
                'label' => 'Student Number',
                'type' => 'text',
                'sortable' => true,
                'width' => 150,
                'required' => false,
            ],
            'email' => [
                'key' => 'email',
                'label' => 'Email',
                'type' => 'text',
                'sortable' => true,
                'width' => 200,
                'required' => false,
            ],
            'phone' => [
                'key' => 'phone',
                'label' => 'Phone',
                'type' => 'text',
                'sortable' => false,
                'width' => 150,
                'required' => false,
            ],
            'status' => [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'status_badge',
                'sortable' => true,
                'width' => 120,
                'required' => false,
            ],
            'programme' => [
                'key' => 'programme',
                'label' => 'Programme',
                'type' => 'programme_info',
                'sortable' => false,
                'width' => 250,
                'required' => false,
            ],
            'location' => [
                'key' => 'location',
                'label' => 'Location',
                'type' => 'location',
                'sortable' => true,
                'width' => 150,
                'required' => false,
            ],
            'age' => [
                'key' => 'age',
                'label' => 'Age',
                'type' => 'calculated_age',
                'sortable' => true,
                'width' => 80,
                'required' => false,
            ],
            'created_at' => [
                'key' => 'created_at',
                'label' => 'Joined',
                'type' => 'date',
                'sortable' => true,
                'width' => 120,
                'required' => false,
            ],
            'last_activity' => [
                'key' => 'last_activity',
                'label' => 'Last Activity',
                'type' => 'date',
                'sortable' => true,
                'width' => 130,
                'required' => false,
            ],
            'actions' => [
                'key' => 'actions',
                'label' => '',
                'type' => 'actions',
                'sortable' => false,
                'width' => 120,
                'required' => true,
            ],
        ];
    }

    /**
     * Get default visible columns for students table
     */
    public static function getDefaultVisibleColumns()
    {
        return ['checkbox', 'student', 'status', 'programme', 'created_at', 'actions'];
    }
}
