<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Academic Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains academic-related configuration values for the
    | TOC Student Information System.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Pass Mark
    |--------------------------------------------------------------------------
    |
    | The default pass mark percentage for assessments. This can be overridden
    | at the module or assessment component level.
    |
    */

    'default_pass_mark' => env('ACADEMIC_DEFAULT_PASS_MARK', 40),

    /*
    |--------------------------------------------------------------------------
    | Assessment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for assessment components and weighting.
    |
    */

    'assessment' => [
        'total_weighting' => env('ACADEMIC_TOTAL_WEIGHTING', 100),
        'max_grade' => env('ACADEMIC_MAX_GRADE', 100),
        'min_grade' => env('ACADEMIC_MIN_GRADE', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Limits
    |--------------------------------------------------------------------------
    |
    | Maximum character limits for various text fields in academic forms.
    |
    */

    'validation_limits' => [
        'description_max_length' => env('ACADEMIC_DESCRIPTION_MAX', 1000),
        'reason_max_length' => env('ACADEMIC_REASON_MAX', 1000),
        'notes_max_length' => env('ACADEMIC_NOTES_MAX', 2000),
        'title_max_length' => env('ACADEMIC_TITLE_MAX', 255),
    ],

    /*
    |--------------------------------------------------------------------------
    | Grade Ranges
    |--------------------------------------------------------------------------
    |
    | Define grade ranges for different classification levels.
    |
    */

    'grade_ranges' => [
        'distinction' => [
            'min' => env('ACADEMIC_DISTINCTION_MIN', 70),
            'label' => 'Distinction',
        ],
        'merit' => [
            'min' => env('ACADEMIC_MERIT_MIN', 60),
            'label' => 'Merit',
        ],
        'pass' => [
            'min' => env('ACADEMIC_PASS_MIN', 40),
            'label' => 'Pass',
        ],
        'fail' => [
            'min' => env('ACADEMIC_FAIL_MIN', 0),
            'label' => 'Fail',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Limits
    |--------------------------------------------------------------------------
    |
    | Maximum file sizes and allowed types for academic document uploads.
    |
    */

    'file_uploads' => [
        'max_size_mb' => env('ACADEMIC_MAX_FILE_SIZE', 10),
        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'gif'],
        'allowed_mime_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
    ],

];
