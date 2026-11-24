<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StudentLoginController extends Controller
{
    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matricule' => ['required', 'digits:8'],
        ]);

        $user = User::where('matricule', $validated['matricule'])
            ->where('role', 'etudiant')
            ->first();

        if (! $user || ! $user->actif) {
            throw ValidationException::withMessages([
                'matricule' => ['Matricule inconnu ou compte désactivé.'],
            ]);
        }

        $code = (string) random_int(100000, 999999);

        OtpCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        OtpCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
        ]);

        $user->notify(new OtpNotification($code));

        return response()->json([
            'message' => 'Code envoyé par e-mail.',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matricule' => ['required', 'digits:8'],
            'code' => ['required', 'digits:6'],
        ]);

        $user = User::where('matricule', $validated['matricule'])
            ->where('role', 'etudiant')
            ->first();

        if (! $user || ! $user->actif) {
            throw ValidationException::withMessages([
                'matricule' => ['Matricule inconnu.'],
            ]);
        }

        $otp = OtpCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>=', now())
            ->latest()
            ->first();

        if (! $otp || ! Hash::check($validated['code'], $otp->code)) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expiré.'],
            ]);
        }

        $otp->update(['used_at' => now()]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'user' => $user,
        ]);
    }
}