<?php

namespace App\Rules;

class ValidationRules
{
    /**
     * Common validation rules for student data
     */
    public static function studentRules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'student_number' => 'required|string|max:50|unique:students,student_number',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
        ];
    }

    /**
     * Common validation rules for grade data
     */
    public static function gradeRules(): array
    {
        return [
            'grade' => 'nullable|numeric|min:0|max:100',
            'max_grade' => 'required|numeric|min:1|max:100',
            'feedback' => 'nullable|string|max:2000',
            'submission_date' => 'nullable|date',
            'is_visible_to_student' => 'required|boolean',
            'release_date' => 'nullable|date|after:now',
        ];
    }

    /**
     * Common validation rules for pagination
     */
    public static function paginationRules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|in:asc,desc',
            'sort_by' => 'nullable|string|max:50',
        ];
    }

    /**
     * Common validation rules for date ranges
     */
    public static function dateRangeRules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period_type' => 'nullable|string|in:daily,weekly,monthly,yearly',
            'limit' => 'nullable|integer|min:1|max:1000',
        ];
    }

    /**
     * Common validation rules for assessment components
     */
    public static function assessmentComponentRules(): array
    {
        return [
            'component_name' => 'required|string|max:255',
            'weighting' => 'required|numeric|min:0|max:100',
            'is_must_pass' => 'required|boolean',
            'component_pass_mark' => 'nullable|numeric|min:0|max:100',
        ];
    }

    /**
     * Common validation rules for module data
     */
    public static function moduleRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:modules,code',
            'credits' => 'required|integer|min:1|max:120',
            'description' => 'nullable|string',
            'allows_standalone_enrolment' => 'required|boolean',
            'async_instance_cadence' => 'required|string|in:Monthly,Quarterly,Bi-Annually,Annually',
        ];
    }

    /**
     * Common validation rules for programme data
     */
    public static function programmeRules(): array
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

    /**
     * Common validation rules for enrolment data
     */
    public static function enrolmentRules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'enrolment_type' => 'required|string|in:Programme,Module',
            'programme_instance_id' => 'nullable|exists:programme_instances,id',
            'module_instance_id' => 'nullable|exists:module_instances,id',
            'status' => 'required|string|in:active,inactive,completed,withdrawn',
            'enrolment_date' => 'required|date',
        ];
    }
}