<?php

use Ht3aa\QiCard\Http\Controllers\QiCardPaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/qi-card')->group(function () {
    Route::post('payments/webhook', [QiCardPaymentController::class, 'webhook'])->name('qi-card.payment.webhook');
});
