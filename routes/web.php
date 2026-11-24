<?php

use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Auth\StudentRegisterController;
use App\Http\Controllers\Etudiant\CatalogueController;
use App\Http\Controllers\Etudiant\DepotController;
use App\Http\Controllers\Etudiant\FavoriController;
use App\Http\Controllers\Etudiant\LectureController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/inscription', [StudentRegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [StudentRegisterController::class, 'store'])
        ->middleware('throttle:10,1')->name('register.store');

    Route::get('/connexion', [StudentLoginController::class, 'create'])->name('login');
    Route::post('/connexion', [StudentLoginController::class, 'requestOtp'])
        ->middleware('throttle:5,1')->name('login.otp.request');

    Route::get('/connexion/code', [StudentLoginController::class, 'showOtp'])->name('login.otp');
    Route::post('/connexion/code', [StudentLoginController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')->name('login.otp.verify');
    Route::post('/connexion/code/renvoyer', [StudentLoginController::class, 'resendOtp'])
        ->middleware('throttle:3,1')->name('login.otp.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [StudentLoginController::class, 'logout'])->name('logout');

    Route::get('/memoires/{memoire}/lecture', [LectureController::class, 'lire'])
        ->name('etudiant.memoires.lecture');
    Route::get('/memoires/{memoire}/telechargement', [LectureController::class, 'telecharger'])
        ->name('etudiant.memoires.telechargement');

    Route::middleware('role:etudiant')->group(function () {
        Route::get('/', fn () => redirect()->route('etudiant.catalogue.index'))->name('home');

        Route::name('etudiant.')->group(function () {
            Route::get('/catalogue', [CatalogueController::class, 'index'])->name('catalogue.index');
            Route::get('/catalogue/{memoire}', [CatalogueController::class, 'show'])->name('catalogue.show');

            Route::get('/mes-depots', [DepotController::class, 'index'])->name('depots.index');
            Route::get('/mes-depots/nouveau', [DepotController::class, 'create'])->name('depots.create');
            Route::post('/mes-depots', [DepotController::class, 'store'])
                ->middleware('throttle:10,1')->name('depots.store');
            Route::get('/mes-depots/{memoire}', [DepotController::class, 'show'])->name('depots.show');

            Route::get('/favoris', [FavoriController::class, 'index'])->name('favoris.index');
            Route::post('/favoris', [FavoriController::class, 'store'])->name('favoris.store');
            Route::delete('/favoris/{memoire}', [FavoriController::class, 'destroy'])->name('favoris.destroy');

            Route::get('/historique', [LectureController::class, 'historique'])->name('historique');
        });
    });
});
