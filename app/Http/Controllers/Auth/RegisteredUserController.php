<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $nameParts = preg_split('/\s+/', trim($request->string('name')->toString()), 2);
        $prenom = $nameParts[0] ?? '';
        $nom = $nameParts[1] ?? $prenom;

        $user = User::create([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'role' => 'responsable',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
