<?php

use App\Http\Controllers\Auth\PasswordChangeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
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
