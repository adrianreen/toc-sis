<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalyticsMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_type',
        'metric_key',
        'metric_data',
        'period_type',
        'period_date',
        'calculated_at',
    ];

    protected $casts = [
        'metric_data' => 'array',
        'period_date' => 'date',
        'calculated_at' => 'datetime',
    ];

    protected $dates = [
        'period_date',
        'calculated_at',
    ];

    /**
     * Scope to filter by metric type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Scope to filter by period type
     */
    public function scopeOfPeriod($query, $period)
    {
        return $query->where('period_type', $period);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_date', [$startDate, $endDate]);
    }

    /**
     * Get metrics for a specific key
     */
    public function scopeForKey($query, $key)
    {
        return $query->where('metric_key', $key);
    }
}
