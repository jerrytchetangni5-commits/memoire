<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AnneeAcademiqueController;
use App\Http\Controllers\Admin\FiliereController;
use App\Http\Controllers\Admin\MemoireController as AdminMemoireController;
use App\Http\Controllers\Admin\UtilisateurController;
use App\Http\Controllers\Admin\ValidationController as AdminValidationController;
use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Auth\StudentRegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/connexion', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/connexion', [AdminLoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');

    Route::middleware('role:admin,responsable')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::resource('filieres', FiliereController::class)
            ->except('show')
            ->parameters(['filieres' => 'filiere']);
        Route::resource('annees-academiques', AnneeAcademiqueController::class)
            ->except('show')
            ->parameters(['annees-academiques' => 'anneeAcademique']);

        Route::middleware('role:admin')->group(function () {
            Route::get('/utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs.index');
            Route::get('/utilisateurs/creer', [UtilisateurController::class, 'create'])->name('utilisateurs.create');
            Route::post('/utilisateurs', [UtilisateurController::class, 'store'])->name('utilisateurs.store');
            Route::patch('/utilisateurs/{user}/statut', [UtilisateurController::class, 'toggleStatus'])
                ->name('utilisateurs.statut');
            Route::get('/memoires/deposer', [AdminMemoireController::class, 'create'])->name('memoires.create');
            Route::post('/memoires/deposer', [AdminMemoireController::class, 'store'])->name('memoires.store');
        });

        Route::get('/validations', [AdminValidationController::class, 'index'])->name('validations.index');
        Route::get('/validations/{memoire}', [AdminValidationController::class, 'show'])->name('validations.show');
        Route::get('/validations/{memoire}/fichier', [AdminValidationController::class, 'fichier'])
            ->name('validations.fichier');
        Route::patch('/validations/{memoire}/en-verification', [AdminValidationController::class, 'marquerEnVerification'])
            ->name('validations.en-verification');
        Route::patch('/validations/{memoire}/valider', [AdminValidationController::class, 'valider'])
            ->name('validations.valider');
        Route::patch('/validations/{memoire}/rejeter', [AdminValidationController::class, 'rejeter'])
            ->name('validations.rejeter');
    });

    Route::post('/deconnexion', [AdminLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
});

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

require __DIR__.'/auth.php';
