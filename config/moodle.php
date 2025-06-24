<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Moodle Web Services Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for connecting to your Moodle instance
    | via Web Services API. You'll need to enable Web Services in Moodle
    | and create a token for API access.
    |
    */

    'url' => env('MOODLE_URL', 'https://your-moodle-site.com'),

    'token' => env('MOODLE_TOKEN'),

    'verify_ssl' => env('MOODLE_VERIFY_SSL', true),

    'default_category_id' => env('MOODLE_DEFAULT_CATEGORY_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Course Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for courses created in Moodle
    |
    */

    'course_defaults' => [
        'format' => 'topics',
        'showgrades' => 1,
        'newsitems' => 5,
        'visible' => 1,
        'enablecompletion' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | User Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for users created in Moodle
    |
    */

    'user_defaults' => [
        'auth' => 'manual',
        'confirmed' => 1,
        'lang' => 'en',
        'timezone' => 'Europe/Dublin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Mappings
    |--------------------------------------------------------------------------
    |
    | Map TOC-SIS roles to Moodle role IDs
    |
    */

    'role_mappings' => [
        'student' => 5,
        'teacher' => 3,
        'editingteacher' => 3,
        'manager' => 1,
    ],
];
