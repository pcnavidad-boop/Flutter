<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::post('/webhook/payment', [WebhookController::class, 'handle'])
    ->withoutMiddleware(['throttle:api']);
