<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\DomainesController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\SoumissionController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\AppelleOffresController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest')
                ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('guest')
                ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware('auth:sanctum')
                ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware('auth:sanctum')
                ->name('verification.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('logout');


Route::middleware('auth:sanctum')->put('/profil', [ProfileController::class, 'update']);
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);


Route::middleware('auth:sanctum')->group(function () {
Route::apiResource('appels', AppelleOffresController::class);});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('soumissions', SoumissionController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('contrats', ContratController::class);
});

Route::middleware('auth:sanctum')->get('/domaines', [DomainesController::class, 'index']);


Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);
