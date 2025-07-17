<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Mettre à jour les informations du profil de l'utilisateur connecté.
     */
  public function update(Request $request)
{
    $user = $request->user();

    // Ne mettre à jour que les champs fournis
    $validated = $request->only([
        'nom', 'prenom', 'email', 'telephone', 'password', 'password_confirmation'
    ]);

    if (isset($validated['email']) && $validated['email'] !== $user->email) {
        $request->validate([
            'email' => 'email|unique:users,email,' . $user->id,
        ]);
        $user->email = $validated['email'];
        $user->email_verified_at = null;
    }

    foreach (['nom', 'prenom', 'telephone'] as $field) {
        if (isset($validated[$field])) {
            $user->$field = $validated[$field];
        }
    }

    if (isset($validated['password'])) {
        $request->validate([
            'password' => 'required|confirmed|min:6'
        ]);
        $user->password = bcrypt($validated['password']);
    }

    $user->save();

    return response()->json(['message' => 'Profil mis à jour.']);
}

}
