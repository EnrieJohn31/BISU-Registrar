<?php

use App\Http\Controllers\Admin\AuditEventController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserCredentialController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\UserStatusController;
use App\Http\Middleware\AuditDeniedAccess;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Account administration and the audit trail (System_Plan Part 03, slice 1).
Route::middleware(['auth', 'verified', AuditDeniedAccess::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index')->can('viewAny', User::class);
        Route::get('users/create', [UserController::class, 'create'])->name('users.create')->can('create', User::class);
        Route::post('users', [UserController::class, 'store'])->name('users.store')->can('create', User::class);
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->can('view', 'user');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update')->can('update', 'user');

        Route::post('users/{user}/roles', [UserRoleController::class, 'store'])->name('users.roles.store')->can('manageRoles', 'user');
        Route::delete('users/{user}/roles/{roleAssignment}', [UserRoleController::class, 'destroy'])
            ->name('users.roles.destroy')
            ->can('manageRoles', 'user')
            ->scopeBindings();

        Route::post('users/{user}/deactivate', [UserStatusController::class, 'deactivate'])->name('users.deactivate')->can('changeStatus', 'user');
        Route::post('users/{user}/reactivate', [UserStatusController::class, 'reactivate'])->name('users.reactivate')->can('changeStatus', 'user');

        Route::post('users/{user}/temporary-password', [UserCredentialController::class, 'temporaryPassword'])
            ->name('users.temporary-password')
            ->can('manageCredentials', 'user');
        Route::delete('users/{user}/two-factor', [UserCredentialController::class, 'resetTwoFactor'])
            ->name('users.two-factor.reset')
            ->can('manageCredentials', 'user');

        Route::get('audit', [AuditEventController::class, 'index'])->name('audit.index')->can('viewAny', AuditEvent::class);
    });
