<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Mettre à jour les informations du profil de l'utilisateur connecté.
     */
  public function update(Request $request)
{
    $user = $request->user();

    // Validation
    $request->validate([
        'nom' => 'nullable|string|max:255',
        'prenom' => 'nullable|string|max:255',
        'telephone' => 'nullable|string|max:20',
        'email' => 'nullable|email|unique:users,email,' . $user->idUser,
        'password' => 'nullable|confirmed|min:6',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    // Mettre à jour les champs seulement s’ils sont remplis
    foreach (['nom', 'prenom', 'email', 'telephone'] as $field) {
        if ($request->filled($field)) {
            $user->$field = $request->$field;
        }
    }

    // Mot de passe
    if ($request->filled('password')) {
        $user->password = bcrypt($request->password);
    }

    // Avatar
    if ($request->hasFile('avatar')) {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
    }

    // ⚠️ Vérifie si quelque chose a vraiment changé
    if ($user->isDirty()) {
        $user->save();

        return response()->json([
            'message' => '✅ Profil mis à jour avec succès.',
            'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null
        ]);
    }

    return response()->json([
        'message' => 'ℹ️ Aucun changement détecté.',
        'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null
    ]);
}
}
