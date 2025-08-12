<?php

namespace App\Http\Controllers;

use App\Models\Soumission;
use App\Models\SoumissionExplanation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SoumissionExplanationController extends Controller
{
   public function generateForSoumission($soumissionId)
{
    // 1️⃣ Récupérer la soumission et l'appel d'offre lié
    $soumission = Soumission::with('appelOffre')->findOrFail($soumissionId);

    // ✅ Déterminer le verdict à partir de "choisi"
    $verdict = $soumission->choisi == 1 ? 'acceptée' : 'refusée';

    // 2️⃣ Construire le payload pour Flask
    $payload = [
        'verdict' => $verdict,
        'soumission' => [
            'prix' => $soumission->prixPropose,
            'delai' => $soumission->temps_realisation,
            'dossier_complet' => $soumission->fichier_joint ? true : false
        ],
        'appel' => [
            'budget_max' => $soumission->appelOffre->budget,
            'delai_max' => null // Ignoré mais clé présente
        ]
    ];

    // 3️⃣ Appeler l'API Flask
    $response = Http::post('http://127.0.0.1:5002/generate-explanation', $payload);

    if ($response->failed()) {
        return response()->json(['error' => 'Erreur lors de l’appel à Flask'], 500);
    }

    $data = $response->json();

    // 4️⃣ Sauvegarder dans la table soumission_explanations
    $explanation = SoumissionExplanation::updateOrCreate(
        ['soumission_id' => $soumission->idSoumission], // ✅ clé correcte
        [
            'verdict' => $data['verdict'],
            'categories' => $data['categories'],
            'public_phrase' => $data['public_phrase']
        ]
    );

    return response()->json([
        'message' => 'Explication générée avec succès',
        'explanation' => $explanation
    ]);
}


   public function explicationsParAppel($appelId)
{
    $soumissions = \App\Models\Soumission::query()
        ->with([
            'user:idUser,nom,prenom,email',
            'appelOffre:idAppel,titre,budget',
            'explanation:soumission_id,verdict,categories,public_phrase'
        ])
        ->where('idAppel', $appelId)
        ->latest()
        ->get([
            'idSoumission', 'idUser', 'idAppel', 'prixPropose', 'temps_realisation', 'choisie'
        ]);

    // Optionnel: transformer pour un frontend plus clean
    $payload = $soumissions->map(function ($s) {
        return [
            'idSoumission'   => $s->idSoumission,
            'prestataire'    => $s->user?->nom . ' ' . $s->user?->prenom,
            'appel'          => [
                'id'     => $s->appelOffre?->idAppel,
                'titre'  => $s->appelOffre?->titre,
                'budget' => $s->appelOffre?->budget,
            ],
            'prix'           => $s->prixPropose,
            'delai'          => $s->temps_realisation,
            'verdict'        => $s->explanation?->verdict ?? ($s->choisie ? 'acceptée' : 'refusée'),
            'categories'     => $s->explanation?->categories ?? [],
            'public_phrase'  => $s->explanation?->public_phrase ?? 'Explication non disponible.',
        ];
    });

    return response()->json($payload);
}

}
