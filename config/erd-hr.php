<?php

return [
    'directories' => [
        base_path('app' . DIRECTORY_SEPARATOR . 'Domains' . DIRECTORY_SEPARATOR . 'HR'),
    ],
    'ignore' => [],
    'whitelist' => [],
    'recursive' => true,
    'use_db_schema' => true,
    'use_column_types' => true,
    'ignore_columns' => [
        'migrations.*',
        'password_reset_tokens.*',
        'personal_access_tokens.*',
        'failed_jobs.*',
    ],
];