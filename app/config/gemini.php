<?php

$defaults = [
    'api_key' => getenv('GEMINI_API_KEY')
        ?: ($_ENV['GEMINI_API_KEY'] ?? '')
        ?: ($_SERVER['GEMINI_API_KEY'] ?? ''),
    'model' => 'gemini-2.5-flash-lite',
    'models' => [
        'summary' => 'gemini-2.5-flash-lite',
        'notes' => 'gemini-2.5-flash-lite',
        'mindmap' => 'gemini-2.5-flash-lite',
        'flashcards' => 'gemini-2.5-flash-lite',
        'quiz' => 'gemini-2.5-flash',
    ],
    'base_url' => 'https://generativelanguage.googleapis.com/v1',
    'generation_config' => [
        'temperature' => 0.2,
        'topP' => 0.95,
        'topK' => 40,
        'maxOutputTokens' => 2048,
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


