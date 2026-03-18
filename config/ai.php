<?php

return [
    'qna_enabled'   => env('AI_QNA_ENABLED', false),
    'gemini_key'    => env('AI_GEMINI_KEY'),
    'openai_key'    => env('AI_OPENAI_KEY'),
    'default_model' => env('AI_DEFAULT_MODEL', 'gemini'),
    'allowed_topics' => [
        'laravel',
        'vue.js',
        'inertia.js',
        'tailwind css',
        'php',
        'javascript',
        'mysql',
        'postgresql',
        'redis',
        'docker',
    ],
];
