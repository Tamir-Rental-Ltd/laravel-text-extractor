<?php

/**
 * @return array{
 *     default: string,
 *     requests_per_second: int|string,
 *     providers: array{
 *         koncile_ai: array{
 *             url: ?string,
 *             key: ?string,
 *             webhook_secret: ?string,
 *         },
 *     },
 * }
 */
return [
    'default' => env('EXTRACTION_PROVIDER', 'koncile_ai'),

    // Maximum provider requests per second across all processes; 0 disables throttling.
    'requests_per_second' => env('EXTRACTION_REQUESTS_PER_SECOND', 1),

    'providers' => [
        'koncile_ai' => [
            'url' => env('KONCILE_AI_API_URL', 'https://api.koncile.ai'),
            'key' => env('KONCILE_AI_API_KEY'),
            'webhook_secret' => env('KONCILE_AI_WEBHOOK_SECRET'),
        ],
    ],
];
