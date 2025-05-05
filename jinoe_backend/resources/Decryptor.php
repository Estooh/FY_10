<?php

use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Biometric;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class EnrollmentController extends Controller
{
public function store(Request $request)
{
$key = 'your-strong-shared-key'; // must match frontend key
try {
$decryptedDescriptor = openssl_decrypt($request->face_descriptor, 'AES-128-ECB',
$key);
$decryptedImage = openssl_decrypt($request->face_image, 'AES-128-ECB', $key);
$decryptedFingerprint = openssl_decrypt($request->fingerprint_credential,
'AES-128-ECB', $key);
Biometric::create([
'full_name' => $request->full_name,
'email' => $request->email,
'face_descriptor' => json_decode($decryptedDescriptor),
'face_image' => $decryptedImage,
'fingerprint_credential' => $decryptedFingerprint,
]);return response()->json(['message' => 'User enrolled successfully'], 200);
} catch (\Exception $e) {
Log::error('Enrollment failed: ' . $e->getMessage());
return response()->json(['message' => 'Enrollment failed'], 500);
}
}
}
