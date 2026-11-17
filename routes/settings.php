<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\SchoolSmtpSettingsController;
use App\Http\Controllers\Settings\SchoolActivationController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/mail', [SchoolSmtpSettingsController::class, 'edit'])->name('school-mail.edit');
    Route::put('settings/mail', [SchoolSmtpSettingsController::class, 'update'])->name('school-mail.update');
    Route::post('settings/mail/test', [SchoolSmtpSettingsController::class, 'test'])
        ->middleware('throttle:3,1')
        ->name('school-mail.test');
    Route::get('settings/activation', [SchoolActivationController::class, 'edit'])->name('school-activation.edit');
    Route::post('settings/activation/request', [SchoolActivationController::class, 'requestActivation'])
        ->middleware('throttle:1,5')
        ->name('school-activation.request');
    Route::post('settings/activation', [SchoolActivationController::class, 'activate'])
        ->middleware('throttle:5,1')
        ->name('school-activation.activate');

    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');
});

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('security.edit'),
        'manage' => route('security.edit'),
    ]);
})->name('well-known.passkeys');
