<?php
// app/Models/Cohort.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cohort extends Model
{
    use HasFactory;

    protected $fillable = [
        'programme_id',
        'code',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $withCount = ['enrolments'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function programme()
    {
        return $this->belongsTo(Programme::class);
    }
    public function enrolments()
    {
        return $this->hasMany(Enrolment::class);
    }

    /**
     * Get the full cohort name with programme code prefix
     */
    public function getFullNameAttribute()
    {
        return $this->programme->code . ' - ' . $this->name;
    }

    /**
     * Get a display-friendly version of the cohort name
     */
    public function getDisplayNameAttribute()
    {
        return $this->programme->code . ' - ' . $this->name . ' (' . $this->code . ')';
    }

}