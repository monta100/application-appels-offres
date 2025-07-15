<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;
class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
public function store(LoginRequest $request): JsonResponse
{
    $request->authenticate(); // Auth via guard (credentials)

    /** @var \App\Models\User $user */
    $user = User::where('email', $request->email)->first();

    // Supprimer tous les tokens existants (facultatif)
    $user->tokens()->delete();

    // Générer un nouveau token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user,
    ]);
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
{
    // Supprime le token d'accès en cours (celui envoyé dans le header Authorization)
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Déconnexion réussie',
    ]);
}
}
