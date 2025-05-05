<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    // public function getUserProfile()
    // {
    //     $userId = Auth::id(); // Get the currently authenticated user's ID
    //     $profile = UserProfile::where('user_id', $userId)->first();

    //     if ($profile) {
    //         return response()->json($profile);
    //     } else {
    //         return response()->json(['message' => 'User profile not found'], 404);
    //     }
    // }

    public function updateUserProfile(Request $request)
    {
        $userId = Auth::id();
        $profile = UserProfile::where('user_id', $userId)->first();

        if ($profile) {
            $profile->update($request->only(['name', 'email', 'profile_picture']));
            return response()->json(['message' => 'Profile updated successfully']);
        } else {
            return response()->json(['message' => 'User profile not found'], 404);
        }
    }
}
