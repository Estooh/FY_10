<?php

namespace App\Http\Controllers;

use App\Models\EnrollUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EnrollmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|email|unique:enrollusers,email',
            'biometric_method' => 'required|in:face,fingerprint',
            'face_descriptor' => 'nullable|array',
            'face_image' => 'nullable|string',
            'fingerprint_template' => 'nullable|string',
            'fingerprint_credential' => 'nullable|string',
        ]);

        $data = [
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'biometric_method' => $request->input('biometric_method'),
        ];

        if ($request->biometric_method === 'face' && $request->face_image) {
            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->face_image));
            $filename = 'faces/' . time() . '.jpg';
            Storage::disk('public')->put($filename, $image);
            $data['face_image'] = $filename;
            $data['face_descriptor'] = $request->face_descriptor;
        }

        if ($request->biometric_method === 'fingerprint' && $request->fingerprint_template) {
            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->fingerprint_template));
            $filename = 'fingerprints/' . time() . '.jpg';
            Storage::disk('public')->put($filename, $image);
            $data['fingerprint_template'] = $filename;
            $data['fingerprint_credential'] = $request->fingerprint_credential;
        }

        $enrolled = EnrollUser::create($data);

        return response()->json([
            'message' => 'Biometric data enrolled successfully.',
            'data' => $enrolled,
        ]);
    }
}
