<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Graph API
    |--------------------------------------------------------------------------
    |
    | Base Graph endpoint and version used for all outbound requests.
    |
    */
    'graph_base_url' => env('WHATSAPP_GRAPH_BASE_URL', 'https://graph.facebook.com'),

    'graph_api_version' => env('WHATSAPP_GRAPH_API_VERSION', 'v23.0'),

    /*
    |--------------------------------------------------------------------------
    | Primary Credentials
    |--------------------------------------------------------------------------
    |
    | The access token authenticates API calls. The phone number ID identifies
    | the default business number used for sending messages and webhook checks.
    |
    */
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),

    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | The verify token is used during Meta's webhook challenge. The app secret
    | validates webhook signatures. By default, inbound webhook entries are
    | only processed when their metadata.phone_number_id matches the configured
    | phone_number_id for this package instance.
    |
    */
    'webhook' => [
        'verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
        'restrict_inbound_messages_to_phone_number_id' => (bool) env('WHATSAPP_RESTRICT_INBOUND_MESSAGES_TO_PHONE_NUMBER_ID', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Flows
    |--------------------------------------------------------------------------
    |
    | These keys are only required when you expose a custom encrypted flow
    | endpoint. The message version controls the flow_message_version payload.
    |
    */
    'flow' => [
        'passphrase' => env('WHATSAPP_KEYS_PASSPHRASE'),
        'public_key' => env('WHATSAPP_PUBLIC_KEY'),
        'private_key' => env('WHATSAPP_PRIVATE_KEY'),
        'message_version' => env('WHATSAPP_FLOW_MESSAGE_VERSION', '3'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    |
    | Timeout and retry settings used by the underlying Laravel HTTP client.
    |
    */
    'http' => [
        'timeout' => (int) env('WHATSAPP_HTTP_TIMEOUT', 30),
        'retry_times' => (int) env('WHATSAPP_HTTP_RETRY_TIMES', 2),
        'retry_sleep_milliseconds' => (int) env('WHATSAPP_HTTP_RETRY_SLEEP_MS', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Optional fallback phone number ID for Laravel notifications when a
    | different sending number should be used from the package default.
    |
    */
    'notifications' => [
        'default_phone_number_id' => env('WHATSAPP_NOTIFICATION_PHONE_NUMBER_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, the client throws validation errors for malformed outgoing
    | payloads instead of allowing incomplete request bodies through.
    |
    */
    'strict_mode' => (bool) env('WHATSAPP_STRICT_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Conversational Components
    |--------------------------------------------------------------------------
    |
    | Configure welcome messages, slash-style commands, and prompts that can
    | be synced to the configured WhatsApp business phone number.
    |
    */
    'conversational_components' => [
        'enable_welcome_message' => (bool) env('WHATSAPP_ENABLE_WELCOME_MESSAGE', false),
        'commands' => [],
        'prompts' => [],
    ],
];
