<?php

use App\Http\Controllers\Auth\DevelopmentTwoFactorCodeController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

// Development-only two-factor email (C22). Registered in every environment so
// the generated Wayfinder routes match; the controller refuses unless local.
Route::post('dev/two-factor-code/challenge', [DevelopmentTwoFactorCodeController::class, 'challenge'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('dev.two-factor-code.challenge');

Route::post('dev/two-factor-code/setup', [DevelopmentTwoFactorCodeController::class, 'setup'])
    ->middleware(['auth', RequirePassword::class, 'throttle:6,1'])
    ->name('dev.two-factor-code.setup');
