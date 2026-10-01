<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    |
    | Every attachment is stored on the private "local" disk and served through
    | an authorized download route. Never move these to a public disk without
    | re-examining the download policies.
    |
    */

    'uploads' => [
        'disk' => env('DARUSO_UPLOAD_DISK', 'local'),

        'max_size_kb' => (int) env('DARUSO_UPLOAD_MAX_KB', 10240),

        'mimes' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'csv', 'rtf',
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'zip',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audience resolution
    |--------------------------------------------------------------------------
    |
    | Notifications are materialised per recipient only for audiences at or
    | below this size. Wider audiences stay as rules and are resolved on read,
    | which keeps a university-wide broadcast from creating tens of thousands
    | of rows.
    |
    */

    'audience' => [
        'notification_materialisation_limit' => (int) env('DARUSO_AUDIENCE_MATERIALISE_LIMIT', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings cache key
    |--------------------------------------------------------------------------
    */

    'settings_cache_key' => 'daruso.settings.v1',

];
