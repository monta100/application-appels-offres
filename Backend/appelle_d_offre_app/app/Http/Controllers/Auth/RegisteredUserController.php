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

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telephone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => ['required', 'in:admin,representant,participant'], // selon tes rôles


        ]);

   $user = User::create([
     'nom' => $request->nom,
    'prenom' => $request->prenom,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'telephone' => $request->telephone,
    'role' => $request->role,
]);


        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
