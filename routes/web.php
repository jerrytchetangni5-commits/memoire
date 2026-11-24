<?php

use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Auth\StudentRegisterController;
use Illuminate\Support\Facades\Route;

// Pages réservées aux visiteurs non connectés
Route::middleware('guest')->group(function () {
    Route::get('/inscription', [StudentRegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [StudentRegisterController::class, 'store'])
        ->middleware('throttle:10,1')->name('register.store');

    // Le nom "login" est obligatoire : Laravel y renvoie les visiteurs non connectés
    Route::get('/connexion', [StudentLoginController::class, 'create'])->name('login');
    Route::post('/connexion', [StudentLoginController::class, 'requestOtp'])
        ->middleware('throttle:5,1')->name('login.otp.request');

    Route::get('/connexion/code', [StudentLoginController::class, 'showOtp'])->name('login.otp');
    Route::post('/connexion/code', [StudentLoginController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')->name('login.otp.verify');
    Route::post('/connexion/code/renvoyer', [StudentLoginController::class, 'resendOtp'])
        ->middleware('throttle:3,1')->name('login.otp.resend');
});

// Pages réservées aux utilisateurs connectés
Route::middleware('auth')->group(function () {
    // Page provisoire : sera remplacée par le catalogue
    Route::get('/', fn () => 'Connecté.')->name('home');

    Route::post('/deconnexion', [StudentLoginController::class, 'logout'])->name('logout');
});