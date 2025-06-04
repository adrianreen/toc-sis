<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'azure_id',
        'role',
        'azure_groups',
        'last_login_at',
        'student_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'azure_groups' => 'array',
        'last_login_at' => 'datetime',
    ];

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isStudentServices()
    {
        return $this->role === 'student_services';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    public function getUnreadNotificationCount(): int
    {
        return $this->unreadNotifications()->count();
    }
}