<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStudentSearch
{
    /**
     * Apply student search filters to a query
     */
    protected function applyStudentSearch(Builder $query, string $search, string $relation = 'student'): Builder
    {
        return $query->whereHas($relation, function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('student_number', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Apply role-based filtering for teachers
     */
    protected function applyTeacherFilter(Builder $query, string $moduleInstanceRelation = 'moduleInstance'): Builder
    {
        if (auth()->user()->role === 'teacher') {
            return $query->whereHas($moduleInstanceRelation, function ($q) {
                $q->where('tutor_id', auth()->id());
            });
        }

        return $query;
    }

    /**
     * Get pagination size from config
     */
    protected function getPaginationSize(string $model = null): int
    {
        if ($model && config("pagination.per_model.{$model}")) {
            return config("pagination.per_model.{$model}");
        }

        return config('pagination.default_per_page', 20);
    }
}