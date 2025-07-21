<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

public function handleGoogleCallback()
{
    $googleUser = Socialite::driver('google')->stateless()->user();

    $user = User::firstOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'nom' => $googleUser->getName(), // tu peux séparer nom/prénom si nécessaire
            'email_verified_at' => now(),
            'password' => bcrypt('google_default_password'), // valeur temporaire
        ]
    );

    Auth::login($user);

    $token = $user->createToken('token_google')->plainTextToken;

    // 🔁 Redirection vers le front avec le token dans l’URL
return redirect()->away("http://localhost:5173/backoffice.html#/sign-in?google_token=" . $token);
}


}
