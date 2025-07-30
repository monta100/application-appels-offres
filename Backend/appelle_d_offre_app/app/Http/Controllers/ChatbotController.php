<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Contrat;
use App\Models\soumission;
use Illuminate\Http\Request;
use App\Models\appelle_offres;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ChatbotController extends Controller
{
    protected $apiKey;
    protected $model;
    protected $url;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key');
        $this->model = 'mistralai/mistral-7b-instruct';
        $this->url = 'https://openrouter.ai/api/v1/chat/completions';
    }

    private function askAI($userPrompt)
    {
        $payload = [
            'model' => $this->model,
            'messages' => [['role' => 'user', 'content' => $userPrompt]]
        ];

        $response = Http::withToken($this->apiKey)->post($this->url, $payload);

        return $response->successful()
            ? $response->json()['choices'][0]['message']['content'] ?? 'Réponse vide.'
            : response()->json(['error' => $response->json()], $response->status());
    }

    public function generateSoumission(Request $request)
    {
        $description = $request->input('description');
        $prompt = "Tu es un prestataire professionnel. Voici un projet : \"$description\".
Génère une soumission JSON structurée avec un titre, une description, un prix cohérent et un temps de réalisation.";

        return response()->json([
            'success' => true,
            'type' => 'soumission',
            'data' => $this->askAI($prompt)
        ]);
    }

    public function createAppelOffreFromPrompt(Request $request)
    {
        $prompt = "Voici un prompt : \"" . $request->input('description') . "\".
Génère un JSON avec :
- titre (string)
- description (string)
- budget (float)
- date_debut (YYYY-MM-DD)
- date_fin (YYYY-MM-DD)
- domaine (ex: informatique)";

        $response = $this->askAI($prompt);
        $data = json_decode($response, true);

        if (!isset($data['titre'], $data['description'], $data['budget'], $data['date_debut'], $data['date_fin'], $data['domaine'])) {
            return response()->json(['success' => false, 'type' => 'erreur', 'error' => 'Champs manquants.'], 400);
        }

        $idDomaine = $this->resolveDomaineId($data['domaine']);
        if (!$idDomaine) {
            return response()->json(['success' => false, 'type' => 'erreur', 'error' => "Domaine non reconnu : " . $data['domaine']], 400);
        }

        if (!auth()->check()) {
            return response()->json(['success' => false, 'type' => 'erreur', 'error' => 'Utilisateur non authentifié.'], 401);
        }

        $appel = new appelle_offres();
        $appel->titre = $data['titre'];
        $appel->description = $data['description'];
        $appel->budget = (float) $data['budget'];
        $appel->date_debut = $data['date_debut'];
        $appel->date_fin = $data['date_fin'];
        $appel->idDomaine = $idDomaine;
        $appel->idUser = auth()->id();
        $appel->statut = 'publiee';
        $appel->date_publication = now();
        $appel->save();

        return response()->json([
            'success' => true,
            'type' => 'appel_offre',
            'message' => 'Appel d\'offre généré avec succès.',
            'data' => $appel
        ]);
    }

    public function aideRedactionSoumission(Request $request)
    {
        $nomAppel = $request->input('nomAppel');
        $appel = appelle_offres::where('titre', 'LIKE', '%' . $nomAppel . '%')->first();

        if (!$appel) {
            return response()->json(['success' => false, 'type' => 'erreur', 'message' => "Aucun appel d'offre nommé \"$nomAppel\""], 404);
        }

        $prompt = "Tu veux proposer une soumission pour \"$nomAppel\".
Génère une soumission JSON avec :
- prixPropose en tnd
- description
- temps_realisation (jours ou semaines)";

        $response = $this->askAI($prompt);
        $data = json_decode($response, true);

        if (!isset($data['prixPropose'], $data['description'], $data['temps_realisation'])) {
            return response()->json(['success' => false, 'type' => 'erreur', 'error' => 'Champs IA manquants.'], 400);
        }

        return response()->json([
            'success' => true,
            'type' => 'aide_soumission',
            'message' => "Soumission générée pour \"$nomAppel\"",
            'data' => $data
        ]);
    }

    public function checkDeadline($id)
    {
        $appel = appelle_offres::findOrFail($id);
        $deadline = Carbon::parse($appel->date_fin);
        $isOver = now()->greaterThan($deadline);

        return response()->json([
            'success' => true,
            'type' => 'deadline',
            'expired' => $isOver,
            'deadline' => $deadline->toDateString()
        ]);
    }

   public function isContratGenere($input)
{
    // On cherche l'appel d'offre par titre approximatif
    $appel = appelle_offres::where('titre', 'like', '%' . $input . '%')->first();

    if (!$appel) {
        return response()->json([
            'type' => 'erreur',
            'error' => "Aucun appel d'offre trouvé avec un titre correspondant à \"$input\"."
        ], 404);
    }

    // On récupère la soumission liée à cet appel (option : la plus récente ou la première)
    $soumission = Soumission::where('idAppel', $appel->idAppel)->orderBy('created_at', 'desc')->first();

    if (!$soumission) {
        return response()->json([
            'type' => 'erreur',
            'error' => "Aucune soumission trouvée pour l'appel d'offre \"$appel->titre\"."
        ], 404);
    }

    // On cherche le contrat lié à la soumission
    $contrat = Contrat::where('idSoumission', $soumission->idSoumission)->first();

    if ($contrat) {
        $fichierExiste = $contrat->fichier_pdf && Storage::disk('public')->exists($contrat->fichier_pdf);

        return response()->json([
            'success' => true,
            'type' => 'contrat',
            'contrat_generé' => true,
            'appel_offre' => $appel->titre,
            'soumission_id' => $soumission->idSoumission,
            'contrat' => [
                'idContrat' => $contrat->idContrat,
                'date_signature' => $contrat->date_signature ?? 'Non définie',
                'pdf_existe' => $fichierExiste,
                'pdf_url' => $fichierExiste ? asset('storage/' . $contrat->fichier_pdf) : null
            ]
        ]);
    }

    return response()->json([
        'success' => true,
        'type' => 'contrat',
        'contrat_generé' => false,
        'appel_offre' => $appel->titre,
        'message' => "Aucun contrat encore généré pour l'appel d'offre \"$appel->titre\"."
    ]);
}


    public function appelsRecents()
    {
        $dateLimite = Carbon::now()->subDays(3)->startOfDay();
        $appels = appelle_offres::where('created_at', '>=', $dateLimite)->get();

        return response()->json([
            'success' => true,
            'type' => 'appels_recents',
            'message' => $appels->isEmpty()
                ? "Aucun appel d'offre récent trouvé."
                : "Appels d’offres récents :",
            'data' => $appels
        ]);
    }

   public function handleUnifiedChat(Request $request)
{
    $userInput = $request->input('message');

    $intentPrompt = "Voici une requête utilisateur : \"$userInput\".
Identifie clairement son intention parmi :
- générer une soumission
- créer un appel d'offre
- vérifier la deadline
- vérifier un contrat
- aide à la rédaction
- appels récents

Réponds uniquement par : intention=xxx";

    $intentRaw = $this->askAI($intentPrompt);
    $intent = strtolower(trim(str_replace('intention=', '', $intentRaw)));

    // ⬇️ Détection souple d’intention
    if (str_contains($intent, 'générer') && str_contains($intent, 'soumission')) {
        return $this->generateSoumission(new Request(['description' => $userInput]));
    }

    if (str_contains($intent, 'créer') && str_contains($intent, 'appel')) {
        return $this->createAppelOffreFromPrompt(new Request(['description' => $userInput]));
    }

    if (str_contains($intent, 'deadline')) {
        if (preg_match('/\b(\d+)\b/', $userInput, $matches)) {
            return $this->checkDeadline($matches[1]);
        }
        return response()->json(['type' => 'erreur', 'error' => 'ID de deadline manquant.'], 400);
    }

 if (str_contains($intent, 'contrat')) {
    // Tenter d'extraire un titre de l'appel d'offre depuis l'entrée utilisateur
    preg_match('/soumission.*?(?:de|du|pour)?\s*(.+)/i', $userInput, $matches);
    $titre = isset($matches[1]) ? trim($matches[1]) : null;

    if (!$titre) {
        return response()->json([
            'type' => 'erreur',
            'error' => 'Titre de l’appel d’offre manquant dans la requête.'
        ], 400);
    }

    return $this->isContratGenere($titre); // 👈 Utilisation par titre maintenant
}



  if (str_contains($intent, 'aide') && str_contains($intent, 'rédaction')) {
    // 🔍 Extraire uniquement le titre d'appel d'offre de la requête
    preg_match('/appel (d\'offre)?\s*(.+)/i', $userInput, $matches);
    $titre = isset($matches[2]) ? trim($matches[2]) : $userInput;

    return $this->aideRedactionSoumission(new Request(['nomAppel' => $titre]));
}


    if (str_contains($intent, 'appel') && str_contains($intent, 'récent')) {
        return $this->appelsRecents();
    }

    // 👇 Debug (optionnel, à retirer en production)
    return response()->json([
        'type' => 'inconnu',
        'intent_recu' => $intentRaw,
        'intent_nettoye' => $intent,
        'message' => "Je n'ai pas compris votre demande."
    ], 200);
}

    private function resolveDomaineId($nom)
    {
        $domaine = \App\Models\Domaine::where('nom', 'LIKE', '%' . $nom . '%')->first();
        return $domaine ? $domaine->idDomaine : null;
    }
}
