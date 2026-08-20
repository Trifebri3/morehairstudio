<?php

use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/health', function () {
    return response()->json(['status' => 'healthy']);
});

Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle']);
