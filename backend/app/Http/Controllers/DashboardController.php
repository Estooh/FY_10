<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Get authenticated user data
    public function index(Request $request)
    {
        return response()->json([
            'full_name' => Auth::user()->full_name ?? Auth::user()->name,
            'email' => Auth::user()->email,
        ]);
    }
}
