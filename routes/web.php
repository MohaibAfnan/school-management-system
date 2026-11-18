<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\SelfServiceRegistrationController;
use App\Http\Middleware\ResolveSchool;
use App\Http\Controllers\Developer\DeveloperAuthController;
use App\Http\Controllers\Developer\DeveloperDashboardController;
use App\Http\Controllers\Developer\DeveloperPlanController;
use App\Http\Controllers\Developer\DeveloperSchoolController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('register', [SelfServiceRegistrationController::class, 'create'])->name('register');
    Route::post('register', [SelfServiceRegistrationController::class, 'store'])->middleware('throttle:3,10')->name('register.store');
    Route::get('register/verify', [SelfServiceRegistrationController::class, 'prompt'])->name('register.verify');
    Route::post('register/verify', [SelfServiceRegistrationController::class, 'verify'])->middleware('throttle:10,1')->name('register.verify.store');
    Route::post('register/resend', [SelfServiceRegistrationController::class, 'resend'])->middleware('throttle:3,1')->name('register.resend');
});

Route::prefix('developer')->name('developer.')->group(function (): void {
    Route::middleware('guest:platform')->group(function (): void {
        Route::get('login', [DeveloperAuthController::class, 'create'])->name('login');
        Route::post('login', [DeveloperAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware('auth:platform')->group(function (): void {
        Route::post('logout', [DeveloperAuthController::class, 'destroy'])->name('logout');
        Route::get('/', DeveloperDashboardController::class)->name('dashboard');
        Route::get('schools', [DeveloperSchoolController::class, 'index'])->name('schools.index');
        Route::post('schools', [DeveloperSchoolController::class, 'store'])->name('schools.store');
        Route::patch('schools/{school}', [DeveloperSchoolController::class, 'update'])->name('schools.update');
        Route::post('schools/{school}/toggle', [DeveloperSchoolController::class, 'toggle'])->name('schools.toggle');
        Route::post('schools/{school}/archive', [DeveloperSchoolController::class, 'archive'])->name('schools.archive');
        Route::post('schools/{school}/restore', [DeveloperSchoolController::class, 'restore'])->withTrashed()->name('schools.restore');
        Route::put('schools/{school}/subscription', [DeveloperSchoolController::class, 'subscription'])->name('schools.subscription');
        Route::post('schools/{school}/reset-access', [DeveloperSchoolController::class, 'sendReset'])->middleware('throttle:3,10')->name('schools.reset-access');
        Route::post('schools/{school}/temporary-password', [DeveloperSchoolController::class, 'setTemporaryPassword'])->middleware('throttle:3,10')->name('schools.temporary-password');
        Route::get('plans', [DeveloperPlanController::class, 'index'])->name('plans.index');
        Route::post('plans', [DeveloperPlanController::class, 'store'])->name('plans.store');
        Route::patch('plans/{plan}', [DeveloperPlanController::class, 'update'])->name('plans.update');
        Route::post('plans/{plan}/toggle', [DeveloperPlanController::class, 'toggle'])->name('plans.toggle');
        Route::delete('plans/{plan}', [DeveloperPlanController::class, 'destroy'])->name('plans.destroy');
    });
});

Route::middleware(['auth', 'school.license'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';
