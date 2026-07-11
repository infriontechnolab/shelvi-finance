<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\OtpVerificationController;
use Illuminate\Support\Facades\Route;

// Login / logout only. No public registration or password-reset in this admin
// panel — users are created by an admin (Users resource, RBAC-gated).
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Second login step: password already checked, session only holds a
    // pending user id until the emailed OTP is confirmed here.
    Route::get('otp/verify', [OtpVerificationController::class, 'create'])->name('otp.verify');
    Route::post('otp/verify', [OtpVerificationController::class, 'store'])->name('otp.verify.store');
    Route::post('otp/resend', [OtpVerificationController::class, 'resend'])->name('otp.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
