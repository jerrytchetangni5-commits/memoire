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
use App\Http\Controllers\Etudiant\DepotController;
use App\Http\Controllers\Etudiant\FavoriController;
use App\Http\Controllers\Etudiant\HistoriqueController;
use App\Http\Controllers\Publique\CatalogueController;
use App\Http\Controllers\Publique\LectureController;
use App\Http\Controllers\Publique\PaiementController;
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

// Accueil : catalogue public ; les étudiants connectés conservent l'accès à leur ancien espace.
Route::get('/', function (\Illuminate\Http\Request $request) {
    if ($request->user()?->role === 'etudiant') {
        return redirect()->route('etudiant.catalogue.index');
    }

    return redirect()->route('public.catalogue.index');
})->name('home');

Route::get('/espace-etudiant/catalogue', [CatalogueController::class, 'index'])
    ->name('etudiant.catalogue.index');
Route::get('/espace-etudiant/catalogue/{memoire}', [CatalogueController::class, 'show'])
    ->name('etudiant.catalogue.show');

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

// Pages OUVERTES À TOUS (pas de middleware "auth").
// Le catalogue et les métadonnées sont publics ; l'accès au PDF est décidé dans les contrôleurs :
//   - connecté : règle historique (public / propriétaire / staff, case « téléchargement autorisé ») ;
//   - externe : jeton délivré après un paiement Kkiapay vérifié côté serveur.
// Noms préfixés "public." : routes/api.php définit déjà catalogue.index, catalogue.show, etc.
Route::name('public.')->group(function () {
    Route::get('/catalogue', [CatalogueController::class, 'index'])->name('catalogue.index');
    Route::get('/catalogue/{memoire}', [CatalogueController::class, 'show'])->name('catalogue.show');

    Route::post('/catalogue/{memoire}/paiement', [PaiementController::class, 'store'])
        ->middleware('throttle:10,1')->name('paiement.store');
    Route::post('/catalogue/{memoire}/acces', [PaiementController::class, 'acces'])
        ->middleware('throttle:10,1')->name('acces.store');

    Route::get('/memoires/{memoire}/lecture', [LectureController::class, 'lire'])->name('memoires.lecture');
    Route::get('/memoires/{memoire}/telechargement', [LectureController::class, 'telecharger'])
        ->name('memoires.telechargement');
});

// Pages réservées aux utilisateurs connectés (guard "web" : session, établie par Auth::login() après le code OTP)
Route::middleware('auth')->group(function () {
    Route::post('/deconnexion', [StudentLoginController::class, 'logout'])->name('logout');

    // Espace étudiant (noms préfixés "etudiant.", pour ne pas heurter ceux de routes/api.php)
    Route::middleware('role:etudiant')->name('etudiant.')->group(function () {
        // Mes dépôts : liste, formulaire, enregistrement, détail
        Route::get('/mes-depots', [DepotController::class, 'index'])->name('depots.index');
        Route::get('/mes-depots/nouveau', [DepotController::class, 'create'])->name('depots.create');
        Route::post('/mes-depots', [DepotController::class, 'store'])
            ->middleware('throttle:10,1')->name('depots.store');
        Route::get('/mes-depots/{memoire}', [DepotController::class, 'show'])->name('depots.show');

        // Favoris
        Route::get('/favoris', [FavoriController::class, 'index'])->name('favoris.index');
        Route::post('/favoris', [FavoriController::class, 'store'])->name('favoris.store');
        Route::delete('/favoris/{memoire}', [FavoriController::class, 'destroy'])->name('favoris.destroy');

        // Historique de lecture
        Route::get('/historique', [HistoriqueController::class, 'index'])->name('historique');
    });
});

require __DIR__.'/auth.php';
