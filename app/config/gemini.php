<?php

return [
    // Prefer environment variable; fallback to placeholder
    'api_key' => getenv('GEMINI_API_KEY') ?: '',
    // Default text-only model
    'model' => 'gemini-1.5-pro',
    // Optional: tune per task if needed
    'models' => [
        'summary' => 'gemini-1.5-pro',
        'notes' => 'gemini-1.5-pro',
        'mindmap' => 'gemini-1.5-pro',
    ],
    // Base URL for Gemini REST API (Google AI Studio)
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    // Request level defaults
    'generation_config' => [
        'temperature' => 0.2,
        'topP' => 0.95,
        'topK' => 40,
        'maxOutputTokens' => 2048,
    ],
];


