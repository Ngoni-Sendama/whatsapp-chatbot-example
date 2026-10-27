<?php

use App\Http\Controllers\WhatsAppBotController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/twilio/whatsapp', [WhatsAppBotController::class, 'handle'])
    ->name('webhooks.twilio.whatsapp');
