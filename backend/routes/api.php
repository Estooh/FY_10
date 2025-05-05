<?php

use App\Http\Controllers\EnrollmentController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceAuthController;
use App\Http\Controllers\FingerprintAuthController;

Route::post('enroll-user', [EnrollmentController::class, 'store']);
Route::post('/auth/face', [FaceAuthController::class, 'authenticate']);
Route::post('/auth/fingerprint', [FingerprintAuthController::class, 'authenticate']);