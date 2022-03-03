<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentNotification;

Route::post('/battle/strong/notify/paystack', [PaymentNotification::class, 'paystack']);
Route::post('/battle/strong/notify/flutterwave', [PaymentNotification::class, 'flutterwave']);
Route::post('/battle/strong/notify/monnify', [PaymentNotification::class, 'monnify']);
