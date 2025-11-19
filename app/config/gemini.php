<?php

$defaults = [
    'api_key' => getenv('GEMINI_API_KEY')
        ?: ($_ENV['GEMINI_API_KEY'] ?? '')
        ?: ($_SERVER['GEMINI_API_KEY'] ?? ''),
    'model' => 'gemini-2.5-flash-lite',
    'models' => [
        'default' => 'gemini-2.5-flash-lite',
        'summary' => 'gemini-2.5-flash-lite',
        'notes' => 'gemini-2.5-flash-lite',
        'mindmap' => 'gemini-2.5-flash-lite',
        'flashcards' => 'gemini-2.5-flash-lite',
        'synthesize' => 'gemini-2.5-pro',
        'quiz' => 'gemini-2.5-pro',
        'embedding' => 'text-embedding-004',
    ],
    'base_url' => 'https://generativelanguage.googleapis.com/v1',
    'generation_config' => [
        'temperature' => 0.2,
        'topP' => 0.95,
        'topK' => 40,
        'maxOutputTokens' => 8192,
    ],
    'rate_limiting' => [
        'delay_between_calls' => 0.5, // seconds delay between API calls
        'max_retries' => 3,
        'retry_delay' => 2, // seconds for exponential backoff
    ],
];

$localFile = __DIR__ . '/gemini.local.php';
if (file_exists($localFile)) {
    $overrides = require $localFile;
    if (is_array($overrides)) {
        return array_replace($defaults, $overrides);
    }
}

return $defaults;


