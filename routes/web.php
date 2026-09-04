<?php

use App\Http\Controllers\PayphoneCallbackController;
use Illuminate\Support\Facades\Route;

Route::get('/payments/payphone/confirm', PayphoneCallbackController::class)
    ->middleware('throttle:30,1')
    ->name('payments.payphone.confirm');

Route::view('/{any?}', 'app')->where('any', '(?!api(?:/|$)).*')->name('home');
