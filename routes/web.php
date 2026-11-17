<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::post('/email/verify-otp', function (Request $request) {
    $request->validate([
        'otp' => 'required|string',
    ]);

    $storedOtp = session('email_verification_otp');

    if (! $storedOtp || $request->input('otp') !== $storedOtp) {
        return back()->withErrors(['otp' => 'The provided OTP code is invalid or has expired.']);
    }

    session()->forget('email_verification_otp');

    if (! $request->user()->hasVerifiedEmail()) {
        $request->user()->markEmailAsVerified();
        event(new Verified($request->user()));
    }

    return redirect()->intended(route('dashboard', absolute: false));
})->middleware(['auth', 'throttle:10,1'])->name('verification.otp');

require __DIR__.'/settings.php';
