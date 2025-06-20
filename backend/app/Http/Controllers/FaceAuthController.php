<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\EnrollUser;

class FaceAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $inputDescriptor = $request->input('descriptor');
        $nonce = $request->input('nonce');

        if (!$inputDescriptor || !is_array($inputDescriptor)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing face descriptor.'
            ], 400);
        }

        if (!$nonce || !Cache::pull("face_nonce_{$nonce}")) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired nonce. Possible replay attempt.'
            ], 403);
        }

        // Retrieve users with face biometrics
        $users = EnrollUser::where('biometric_method', 'face')
            ->whereNotNull('face_descriptor')
            ->get();

        $bestMatch = null;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($users as $user) {
            $storedDescriptor = $user->face_descriptor;

            if (is_array($storedDescriptor)) {
                $distance = $this->euclideanDistance($inputDescriptor, $storedDescriptor);

                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestMatch = $user;
                }
            }
        }

        $threshold = 0.55;

        if ($bestMatch && $bestDistance <= $threshold) {
            return response()->json([
                'success' => true,
                'message' => 'Face authenticated successfully!',
                'user' => [
                    'id' => $bestMatch->id,
                    'full_name' => $bestMatch->full_name,
                    'email' => $bestMatch->email,
                ],
                'distance' => $bestDistance,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Face not recognized!'
        ], 401);
    }

    private function euclideanDistance(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return PHP_FLOAT_MAX;
        }

        $sum = 0.0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }

        return sqrt($sum);
    }
}
