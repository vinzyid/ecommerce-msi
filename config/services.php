<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chatbot (OpenAI-compatible API)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk fitur chatbot customer. Mendukung endpoint apa pun
    | yang kompatibel dengan format OpenAI Chat Completions (OpenAI,
    | OpenRouter, DeepSeek, Groq, dsb). Cukup ubah base_url & model.
    |
    */

    'chatbot' => [
        'api_key' => env('CHATBOT_API_KEY'),
        'base_url' => env('CHATBOT_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('CHATBOT_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('CHATBOT_TIMEOUT', 30),
        'cache_ttl' => (int) env('CHATBOT_CACHE_TTL', 60), // menit
        'max_tokens' => (int) env('CHATBOT_MAX_TOKENS', 600),
        'temperature' => (float) env('CHATBOT_TEMPERATURE', 0.3),
    ],

];
