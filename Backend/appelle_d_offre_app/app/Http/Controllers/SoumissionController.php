<?php

namespace App\Http\Controllers;

use App\Models\soumission;
use Illuminate\Http\Request;
use App\Mail\SoumissionChoisieMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class SoumissionController extends Controller
{
    public function index()
    {
        $soumissions = soumission::with(['user', 'appelOffre'])->latest()->get();
        return response()->json($soumissions);
    }

  public function store(Request $request)
{
    $validated = $request->validate([
        'prixPropose' => 'required|numeric|min:0',
        'description' => 'required|string',
        'temps_realisation' => 'required|string',
        'score_ia' => 'nullable|numeric|min:0|max:100',
        'fichier_joint' => 'nullable|file|mimes:pdf,docx,doc|max:2048',
        'idAppel' => 'required|exists:appelle_offres,idAppel',
    ]);

    $validated['idUser'] = auth()->id(); // 🔥 prend automatiquement l’utilisateur connecté


// ✅ Vérifie si l'utilisateur a déjà soumis une proposition pour cet appel
    $exists = Soumission::where('idUser', $validated['idUser'])
                        ->where('idAppel', $validated['idAppel'])
                        ->exists();

    if ($exists) {
        return response()->json([
            'message' => '❌ Vous avez déjà soumis une proposition pour cet appel d\'offre.'
        ], 409); // Code HTTP 409 = Conflict
    }






    if ($request->hasFile('fichier_joint')) {
$validated['fichier_joint'] = $request->file('fichier_joint')->store('fichiers_soumissions', 'public');
    }





    $soumission = soumission::create($validated);

    return response()->json($soumission, 201);
}


    public function show($id)
    {
        $soumission = soumission::with(['user', 'appelOffre', 'contrat'])->findOrFail($id);
        return response()->json($soumission);
    }

    public function update(Request $request, $id)
    {
        $soumission = soumission::findOrFail($id);

        $validated = $request->validate([
            'prixPropose' => 'sometimes|required|numeric|min:0',
            'description' => 'sometimes|required|string',
            'temps_realisation' => 'sometimes|required|string',
            'score_ia' => 'nullable|numeric|min:0|max:100',
            'fichier_joint' => 'nullable|file|mimes:pdf,docx,doc|max:2048',
            'idAppel' => 'sometimes|required|exists:appelle_offres,idAppel',
        ]);

        if ($request->hasFile('fichier_joint')) {
            $validated['fichier_joint'] = $request->file('fichier_joint')->store('fichiers_soumissions');
        }

        

        $soumission->update($validated);
        return response()->json($soumission);
    }

    public function destroy($id)
    {
        $soumission = soumission::findOrFail($id);
        $soumission->delete();

        return response()->json(['message' => 'Soumission supprimée avec succès.']);
    }
    public function mesSoumissions()
{
    $userId = auth()->id();
    $soumissions = Soumission::where('idUser', $userId)->get();
    return response()->json($soumissions);
}


public function aDejaSoumis($idAppel)
{
    $userId = auth()->id();
    $exists = \App\Models\soumission::where('idUser', $userId)
        ->where('idAppel', $idAppel)
        ->exists();

    return response()->json(['exists' => $exists]);
}


public function getSoumissionsByAppel($idAppel)
{
    $soumissions = Soumission::with('user')  // Assure-toi que la relation user est définie
        ->where('idAppel', $idAppel)
        ->get();

    return response()->json($soumissions);
}

public function choisir($id)
{
    $soumission = Soumission::with('user')->findOrFail($id);
    
    // Mettre à jour la soumission choisie (ex : champ `choisie`)
    $soumission->choisie = true;
    $soumission->save();

    // Envoi de mail
    Mail::to($soumission->user->email)->send(new SoumissionChoisieMail($soumission));

    return response()->json(['message' => 'Soumission choisie']);
}

public function soumissionsChoisies(Request $request)
{
    $userId = $request->user()->idUser;

    $soumissions = Soumission::with(['appelOffre.domaine', 'user', 'contrat'])
        ->whereHas('appelOffre', function ($query) use ($userId) {
            $query->where('idUser', $userId); // Seuls les appels d’offres du représentant connecté
        })
        ->where('choisie', true)
        ->latest()
        ->get();

    return response()->json($soumissions);
}




public function scoring($id)
{
    $soumission = soumission::findOrFail($id);

    // Appel vers Flask
    $response = Http::post('http://127.0.0.1:5000/predict', [
        'prixPropose' => $soumission->prixPropose,
        'temps_realisation' => $soumission->temps_realisation,
        'description' => $soumission->description,
    ]);

    if ($response->successful()) {
        $score = $response->json()['score_ia'];
        $soumission->score_ia = $score;
        $soumission->save();

        return response()->json([
            'message' => '✅ Scoring effectué avec succès.',
            'score_ia' => $score
        ]);
    } else {
        return response()->json(['message' => 'Erreur lors du scoring'], 500);
    }
}


public function getGlobalActivityIndex()
{
    $weekStart = \Carbon\Carbon::now()->startOfWeek();

    // Comptage des entités
    $soumissions = \App\Models\Soumission::where('created_at', '>=', $weekStart)->count();
    $appels = \App\Models\appelle_offres::where('created_at', '>=', $weekStart)->count();
    $contrats = \App\Models\Contrat::where('created_at', '>=', $weekStart)->count();
    $users = \App\Models\User::where('created_at', '>=', $weekStart)->count();

    // Pondération (tu peux ajuster)
    $score = min(100, ($soumissions * 3 + $appels * 2 + $contrats * 4 + $users * 1));

    return response()->json([
        'index' => $score,
        'details' => [
            'soumissions' => $soumissions,
            'appels' => $appels,
            'contrats' => $contrats,
            'users' => $users,
        ]
    ]);
}

}
