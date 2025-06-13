<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FaceAuthController;
use App\Http\Controllers\FingerprintAuthController;

Route::post('enroll-user', [EnrollmentController::class, 'store']);
Route::post('/auth/face', [FaceAuthController::class, 'authenticate']);
Route::post('/auth/fingerprint', [FingerprintAuthController::class, 'authenticate']);
Route::get('/check-user', [EnrollUserController::class, 'checkUser']);
Route::get('/user', [EnrollUserController::class, 'User']);