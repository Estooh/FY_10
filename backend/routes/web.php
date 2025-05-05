<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserProfileController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Get the user profile of the authenticated user
Route::middleware('auth')->get('api/user/profile', [UserProfileController::class, 'getUserProfile']);

// Update the user profile of the authenticated user
Route::middleware('auth')->post('api/user/profile', [UserProfileController::class, 'updateUserProfile']);
