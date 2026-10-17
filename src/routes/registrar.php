<?php

use App\Http\Controllers\Registrar\ClientIdentifierController;
use App\Http\Controllers\Registrar\ClientProfileController;
use App\Http\Controllers\Registrar\ClientVerificationController;
use App\Http\Middleware\AuditDeniedAccess;
use App\Models\ClientProfile;
use Illuminate\Support\Facades\Route;

// Client records (System_Plan Part 04, slice 1). Authorization lives in
// App\Policies\ClientProfilePolicy and always requires the Registrar office.
Route::middleware(['auth', 'verified', AuditDeniedAccess::class])
    ->prefix('registrar')
    ->name('registrar.')
    ->group(function () {
        Route::get('clients', [ClientProfileController::class, 'index'])->name('clients.index')->can('viewAny', ClientProfile::class);
        Route::get('clients/create', [ClientProfileController::class, 'create'])->name('clients.create')->can('create', ClientProfile::class);
        Route::post('clients', [ClientProfileController::class, 'store'])->name('clients.store')->can('create', ClientProfile::class);
        Route::get('clients/{clientProfile}', [ClientProfileController::class, 'show'])->name('clients.show')->can('view', 'clientProfile');
        Route::patch('clients/{clientProfile}', [ClientProfileController::class, 'update'])->name('clients.update')->can('manage', 'clientProfile');

        Route::post('clients/{clientProfile}/verify', [ClientVerificationController::class, 'verify'])->name('clients.verify')->can('manage', 'clientProfile');
        Route::post('clients/{clientProfile}/flag', [ClientVerificationController::class, 'flag'])->name('clients.flag')->can('manage', 'clientProfile');

        Route::post('clients/{clientProfile}/identifiers', [ClientIdentifierController::class, 'store'])
            ->name('clients.identifiers.store')
            ->can('manage', 'clientProfile');
        Route::delete('clients/{clientProfile}/identifiers/{clientIdentifier}', [ClientIdentifierController::class, 'destroy'])
            ->name('clients.identifiers.destroy')
            ->can('manage', 'clientProfile');
    });
