<?php

use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Read per request so a cached route never shows a stale registration link.
Route::get('/', fn () => Inertia::render('Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
]))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

// Forced replacement of a temporary password at first sign-in.
Route::middleware(['auth'])->group(function () {
    Route::get('password/change', [PasswordChangeController::class, 'edit'])->name('password.change.edit');
    Route::put('password/change', [PasswordChangeController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.change.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/registrar.php';
