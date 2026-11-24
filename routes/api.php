<?php

use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Auth\StudentRegisterController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\FavoriController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\MemoireController;
use App\Http\Controllers\Responsable\ValidationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/student/register', [StudentRegisterController::class, 'store'])
    ->middleware('throttle:10,1');
Route::post('/student/otp/request', [StudentLoginController::class, 'requestOtp'])
    ->middleware('throttle:3,1');
Route::post('/student/otp/verify', [StudentLoginController::class, 'verifyOtp'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/catalogue', [CatalogueController::class, 'index'])->name('catalogue.index');
    Route::get('/catalogue/{memoire}', [CatalogueController::class, 'show'])->name('catalogue.show');
    Route::get('/memoires/{memoire}/lecture', [LectureController::class, 'read'])->name('memoires.read');
    Route::get('/memoires/{memoire}/telechargement', [LectureController::class, 'download'])
        ->name('memoires.download');
    Route::get('/historique-lectures', [LectureController::class, 'historique'])
        ->name('lectures.history');

    Route::get('/favoris', [FavoriController::class, 'index'])->name('favoris.index');
    Route::post('/favoris', [FavoriController::class, 'store'])->name('favoris.store');
    Route::delete('/favoris/{memoire}', [FavoriController::class, 'destroy'])->name('favoris.destroy');

    Route::prefix('validation')->middleware('role:responsable,admin')->group(function () {
        Route::get('/memoires', [ValidationController::class, 'index'])->name('validation.index');
        Route::get('/memoires/{memoire}', [ValidationController::class, 'show'])->name('validation.show');
        Route::patch('/memoires/{memoire}/en-verification', [ValidationController::class, 'marquerEnVerification'])
            ->name('validation.en-verification');
        Route::patch('/memoires/{memoire}/valider', [ValidationController::class, 'valider'])
            ->name('validation.valider');
        Route::patch('/memoires/{memoire}/rejeter', [ValidationController::class, 'rejeter'])
            ->name('validation.rejeter');
    });

    Route::middleware('role:etudiant')->group(function () {
        Route::get('/memoires', [MemoireController::class, 'index'])->name('memoires.index');
        Route::post('/memoires', [MemoireController::class, 'store'])->name('memoires.store');
        Route::get('/memoires/{memoire}', [MemoireController::class, 'show'])->name('memoires.show');
    });
});
