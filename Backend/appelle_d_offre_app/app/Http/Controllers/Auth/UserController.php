<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }
    public function toggleActive(User $user)
{
    $user->is_active = !$user->is_active;
    $user->save();

    return response()->json([
        'message' => $user->is_active ? 'Utilisateur activé' : 'Utilisateur désactivé',
        'is_active' => $user->is_active
    ]);
}

}
