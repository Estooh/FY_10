<?php
// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollUserController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FaceAuthController;
use App\Http\Controllers\FingerprintAuthController;

// Public Routes
Route::post('/enroll-user', [EnrollmentController::class, 'store']);
Route::post('/auth/face', [FaceAuthController::class, 'authenticate']);
Route::post('/auth/fingerprint', [FingerprintAuthController::class, 'authenticate']);

// Nonce route to protect against reply attack as well as MitM
Route::get('/auth/nonce', function () {
    $nonce = Str::random(40);
    Cache::put("face_nonce_{$nonce}", true, now()->addMinutes(2));
    return response()->json(['nonce' => $nonce]);
});


// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/check-user', [EnrollUserController::class, 'checkUser']);
    Route::get('/user', [EnrollUserController::class, 'user']);
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    });
});
