<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Integrations\ExchangeRate\ExchangeRateAdapter;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {   
    Route::resource('user', UserController::class);
    Route::get('/exchange-rate/{base}/amount/{amount}', function ($base, $amount, CurrencyService $service) {
        return $service->convert($base, $amount);       
    });
});