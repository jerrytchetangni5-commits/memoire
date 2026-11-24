<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentLoginController extends Controller
{
    // Étape 1 : formulaire du matricule
    public function create(): View
    {
        return view('auth.login');
    }

    // Étape 1 (envoi) : génère et envoie le code, puis passe à l'étape 2
    public function requestOtp(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'matricule' => ['required', 'digits:8'],
        ]);

        if (! $request->hasSession()) {
            throw ValidationException::withMessages([
                'matricule' => ['La connexion par code nécessite une session de navigateur.'],
            ]);
        }

        $user = $this->findStudent($validated['matricule']);
        $this->sendOtp($user);

        // On garde le matricule en session pour l'étape suivante
        $request->session()->put('otp_matricule', $user->matricule);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Un code de connexion vous a été envoyé.']);
        }

        return redirect()->route('login.otp');
    }

    // Étape 2 : formulaire du code
    public function showOtp(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('otp_matricule')) {
            return redirect()->route('login');
        }

        return view('auth.otp');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $matricule = $request->session()->get('otp_matricule');

        if (! $matricule) {
            return redirect()->route('login');
        }

        $this->sendOtp($this->findStudent($matricule));

        return back()->with('status', 'Un nouveau code vient de vous être envoyé.');
    }

    // Étape 2 (envoi) : vérifie le code et connecte l'étudiant
    public function verifyOtp(Request $request): RedirectResponse|JsonResponse
    {
        if (! $request->hasSession()) {
            throw ValidationException::withMessages([
                'code' => ['La connexion par code nécessite une session de navigateur.'],
            ]);
        }

        $matricule = $request->session()->get('otp_matricule');

        if (! $matricule) {
            if ($request->expectsJson()) {
                throw ValidationException::withMessages([
                    'code' => ['Aucune demande de connexion en cours.'],
                ]);
            }

            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->findStudent($matricule);

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

        Auth::login($user);
        $request->session()->regenerate();      // protège contre la fixation de session
        $request->session()->forget('otp_matricule');

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Connexion réussie.']);
        }

        $request->session()->put('etudiant_salutation', $user->prenom);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function findStudent(string $matricule): User
    {
        $user = User::where('matricule', $matricule)
            ->where('role', 'etudiant')
            ->first();

        if (! $user || ! $user->actif) {
            throw ValidationException::withMessages([
                'matricule' => ['Matricule inconnu ou compte désactivé.'],
            ]);
        }

        return $user;
    }

    private function sendOtp(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        // Invalide les anciens codes encore actifs
        OtpCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        OtpCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
        ]);

        $user->notify(new OtpNotification($code));
    }
}
