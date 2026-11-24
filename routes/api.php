<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Auth\StudentRegisterController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/student/register', [StudentRegisterController::class, 'store']);
Route::post('/student/otp/request', [StudentLoginController::class, 'requestOtp']);
Route::post('/student/otp/verify', [StudentLoginController::class, 'verifyOtp']);
