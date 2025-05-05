<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnrollUser;

class FingerprintAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentialId = $request->input('credential_id');

        if (!$credentialId) {
            return response()->json(['status' => 'error', 'message' => 'Credential ID missing'], 400);
        }

        $user = EnrollUser::where('fingerprint_credential', $credentialId)->first();

        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'Fingerprint authenticated.',
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email
                ]
            ]);
        }

        return response()->json(['status' => 'fail', 'message' => 'Fingerprint not recognized.']);
    }

    public function dashboard()
    {
        return view('dashboard');
    }
}
