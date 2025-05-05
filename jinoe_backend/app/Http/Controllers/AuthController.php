<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{


    public function login(LoginRequest $request)
    {
        $request->validated($request->all());

        $credentials = ['email' => $request->email, 'password' => $request->password];

        if (!Auth::guard('web')->attempt($credentials, false)) {
            return response()->json('Credentials do not match', 401);
        }

        $user = User::where('email', $request->email)->with(['userRole:role_id'])->first();

        $role = Role::where('role_id', $user->role_id)->get('role');

        $r = null;
        if ($role->count() > 0) {

            $r = $role[0]['role'];

        } else {
            $r = null;
        }

        return response()->json(
            [
            'user' => $user,
            'role' => $r,
            'token' => $user->createToken('Api Token of' . $user->first_name)->plainTextToken,
            ]
        );

    }



    public function logout(Request $request)
    {
        // $request->user()->currentAccessToken()->delete();
        // return $this->success('','You have logged out');
        return $request->user();
    }
}
