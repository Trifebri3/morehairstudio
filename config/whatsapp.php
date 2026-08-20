<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default WhatsApp Provider
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following provider adapters will be
    | used as the primary channel to deliver messages to customers.
    | Supported: "meta", "fonnte"
    |
    */
    'provider' => env('WHATSAPP_PROVIDER', 'meta'),

    /*
    |--------------------------------------------------------------------------
    | Meta Cloud API Credentials
    |--------------------------------------------------------------------------
    |
    */
    'meta' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'version' => env('WHATSAPP_VERSION', 'v20.0'),
        'verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonnte Gateway Credentials
    |--------------------------------------------------------------------------
    |
    */
    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    */
    'queue' => [
        'connection' => env('WHATSAPP_QUEUE_CONNECTION', 'database'),
        'queue' => env('WHATSAPP_QUEUE', 'whatsapp'),
        'retry_after' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Toggle options to activate or disable automated lifecycle triggers.
    |
    */
    'notifications' => [
        'booking_confirmation' => true,
        'booking_reminder' => true,
        'booking_expired' => true,
        'booking_cancelled' => true,
        'booking_completed' => true,
    ],
];
