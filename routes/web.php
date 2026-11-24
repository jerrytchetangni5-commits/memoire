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
use App\Http\Controllers\Etudiant\CatalogueController;
use App\Http\Controllers\Etudiant\DepotController;
use App\Http\Controllers\Etudiant\FavoriController;
use App\Http\Controllers\Etudiant\LectureController;
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

require __DIR__.'/auth.php';
