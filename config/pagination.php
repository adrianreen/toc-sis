<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Pagination Settings
    |--------------------------------------------------------------------------
    |
    | These values control the default pagination behavior across the
    | application. They can be overridden in individual controllers
    | when specific requirements differ.
    |
    */

    'default_per_page' => 20,
    'max_per_page' => 100,
    'student_search_limit' => 50,
    'analytics_limit' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Per-Model Pagination Overrides
    |--------------------------------------------------------------------------
    |
    | Specific pagination settings for different models when they require
    | different defaults than the global setting.
    |
    */

    'per_model' => [
        'students' => 25,
        'notifications' => 15,
        'email_logs' => 50,
        'grade_records' => 30,
    ],
];
