<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\soumission;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\appelle_offres;
use App\Events\NotificationEvent;
use App\Mail\SoumissionChoisieMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\SoumissionExplanation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log; // ✅ Import de Log

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
// 🔔 NOTIFICATION : informer le représentant de l’appel d’offre
$appel = appelle_offres::find($soumission->idAppel);
$representant = $appel->user; // relation avec User

$notif = Notification::create([
    'user_id' => $representant->idUser,
    'title' => 'Nouvelle soumission reçue',
    'message' => 'Vous avez reçu une nouvelle soumission pour votre appel d\'offre : ' . $appel->titre,
    'type' => 'soumission',
]);

broadcast(new NotificationEvent($notif)); // Optionnel si tu fais du temps réel


    
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
    // 1️⃣ Récupérer la soumission choisie avec son appel d'offre
    $soumissionChoisie = Soumission::with(['user', 'appelOffre'])->findOrFail($id);

    // 2️⃣ Marquer la soumission comme choisie
    $soumissionChoisie->choisie = true;
    $soumissionChoisie->save();

    // 3️⃣ Récupérer toutes les soumissions de cet appel
    $soumissions = Soumission::with('user', 'appelOffre')
        ->where('idAppel', $soumissionChoisie->idAppel)
        ->get();

    // 4️⃣ Générer une explication IA pour chaque soumission
    foreach ($soumissions as $soumission) {
        // ✅ Si c'est la soumission choisie → acceptée, sinon → refusée
        $verdict = ($soumission->idSoumission == $soumissionChoisie->idSoumission)
            ? 'acceptée'
            : 'refusée';

        $payload = [
            'verdict' => $verdict,
            'soumission' => [
                'prix' => $soumission->prixPropose,
                'delai' => $soumission->temps_realisation,
                'dossier_complet' => !empty($soumission->fichier_joint)
            ],
            'appel' => [
                'budget_max' => $soumission->appelOffre->budget,
                'delai_max' => null
            ]
        ];

        try {
            $response = Http::timeout(10)->post('http://127.0.0.1:5002/generate-explanation', $payload);

            if ($response->ok()) {
                $data = $response->json();

                SoumissionExplanation::updateOrCreate(
                    ['soumission_id' => $soumission->idSoumission],
                    [
                        'verdict' => $data['verdict'] ?? $verdict,
                        'categories' => $data['categories'] ?? [],
                        'public_phrase' => $data['public_phrase'] ?? ''
                    ]
                );
            } else {
            }
        } catch (\Exception $e) {
        }
    }

    // 5️⃣ Envoi du mail uniquement à la soumission choisie
    Mail::to($soumissionChoisie->user->email)
        ->send(new \App\Mail\SoumissionChoisieMail($soumissionChoisie));

    return response()->json(['message' => 'Soumission choisie et explications générées']);
}



public function soumissionsChoisies(Request $request)
{
    $userId = $request->user()->idUser;

    $soumissions = Soumission::with(['appelOffre.domaine', 'appelOffre', 'user', 'contrat'])
        ->whereHas('appelOffre', function ($query) use ($userId) {
            $query->where('idUser', $userId); // Seuls les appels d’offres du représentant connecté
        })
        ->where('choisie', true)
        ->latest()
        ->get();

    // 🔔 Notifier chaque prestataire concerné
    foreach ($soumissions as $soumission) {
        $notif = \App\Models\Notification::create([
            'user_id' => $soumission->user->idUser,
            'title' => '🎉 Soumission acceptée',
            'message' => 'Votre soumission pour l’appel d’offre "' . $soumission->appelOffre->titre . '" a été choisie.',
            'type' => 'soumission',
        ]);

        broadcast(new \App\Events\NotificationEvent($notif))->toOthers();
    }

    return response()->json($soumissions);
}




public function scoring($id)
{
    // Récupération de la soumission + appel d’offre (titre/desc/budget/dates)
    $soumission = \App\Models\soumission::with('appelOffre')->findOrFail($id);
    $appel = $soumission->appelOffre;

    // Texte de référence = titre + description de l'appel
    $refText = trim(($appel->titre ?? '') . ' ' . ($appel->description ?? ''));

    // Budget (float) et délai attendu (jours) à partir des dates de l'appel
    $budget = $appel?->budget ? (float) $appel->budget : null;

    // Si tes dates sont castées en Carbon, c'est déjà OK. Sinon, parse avec Carbon::parse(...)
    $joursAttendus = null;
    if (!empty($appel?->date_debut) && !empty($appel?->date_fin)) {
        try {
            $debut = $appel->date_debut instanceof Carbon ? $appel->date_debut : Carbon::parse($appel->date_debut);
            $fin   = $appel->date_fin   instanceof Carbon ? $appel->date_fin   : Carbon::parse($appel->date_fin);
            // +1 si tu veux inclure les deux extrémités
            $joursAttendus = max(1, $debut->diffInDays($fin));
        } catch (\Throwable $e) {
            $joursAttendus = null; // neutralisé côté Flask (50/100)
        }
    }

    // Corps envoyé au service Flask v2
    $payload = [
        'prixPropose'       => (float) $soumission->prixPropose,
        'temps_realisation' => (float) $soumission->temps_realisation,
        'description'       => (string) ($soumission->description ?? ''),
        'ref_text'          => $refText,          // <<< important
        'budget'            => $budget,           // <<< important (peut être null)
        'jours_attendus'    => $joursAttendus,    // <<< important (peut être null)
    ];

    // URL configurable (mettre SCORING_URL=http://127.0.0.1:5001/predict dans .env)
    $url = config('services.scoring.url', env('SCORING_URL', 'http://127.0.0.1:5001/predict'));

    try {
        $response = Http::timeout(4)->retry(2, 150)->post($url, $payload);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Erreur lors du scoring',
                'details' => $response->body(),
            ], $response->status());
        }

        $json = $response->json();

        // Score final
        $soumission->score_ia = isset($json['score_ia']) ? (float) $json['score_ia'] : null;

        // Optionnel : si tu as ajouté des colonnes pour le détail, on les remplit
        // (décommente si tu as ces colonnes en DB)
        // $soumission->score_ia_text   = $json['breakdown']['similarite'] ?? null;
        // $soumission->score_ia_price  = $json['breakdown']['prix'] ?? null;
        // $soumission->score_ia_delay  = $json['breakdown']['delai'] ?? null;
        // $soumission->score_ia_version = $json['model_version'] ?? null;

        $soumission->save();

        return response()->json([
            'message'   => '✅ Scoring effectué avec succès.',
            'payload'   => $payload,     // utile pour debug/traçabilité
            'result'    => $json,        // contient score_ia (+ breakdown si activé côté Flask)
        ]);
    } catch (ConnectionException $e) {
        return response()->json([
            'message' => 'Le service de scoring est injoignable.',
            'error'   => $e->getMessage(),
        ], 503);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Erreur inattendue lors du scoring.',
            'error'   => $e->getMessage(),
        ], 500);
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


public function detecterAnomalie($id)
{
    $soumission = soumission::with('appelOffre')->findOrFail($id);
    $appel = $soumission->appelOffre;

    try {
        $response = Http::post('http://127.0.0.1:5000/api/anomalie', [
            'description_appel' => $appel->description,
            'description_soumission' => $soumission->description,
            'prix_propose' => $soumission->prixPropose,
            'budget_max' => $appel->budget,
            'temps_realisation' => $soumission->temps_realisation,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $soumission->update([
                'score_ia_anomalie' => $data['score_total'] ?? null,
                'verdict_ia_anomalie' => $data['verdict'] ?? null,
                'explication_anomalie' => $data['explication'] ?? null,
            ]);

            return response()->json([
                'message' => 'Anomalie détectée avec succès.',
                'data' => $data
            ]);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de l’analyse'], 500);
    }
}

}
