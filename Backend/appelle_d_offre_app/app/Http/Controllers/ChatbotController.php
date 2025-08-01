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

  public function createAppelOffreFromPrompt(Request $request)
{
    $message = $request->input('message');

    if (!$message) {
        return response()->json([
            'success' => false,
            'type' => 'erreur',
            'error' => 'Message requis.'
        ], 400);
    }

$prompt = "Voici un prompt : \"$message\".
Génère un JSON avec :
- titre (string)
- description (string)
- budget (float)
- date_debut (au format YYYY-MM-DD, à partir d'aujourd'hui)
- date_fin (YYYY-MM-DD, après date_debut)
- domaine (ex: informatique)

Assure-toi que les dates sont valides et récentes. N'utilise pas 'YEAR-MONTH-DAY', remplace-les par de vraies dates.";


    $response = $this->askAI($prompt);

    $data = json_decode($response, true);
    $champsManquants = [];

    if (!is_array($data)) {
        return response()->json([
            'success' => false,
            'type' => 'erreur',
            'error' => 'Réponse IA invalide.',
            'debug' => $response
        ], 400);
    }

    // Vérifie chaque champ
    foreach (['titre', 'description', 'budget', 'date_debut', 'date_fin', 'domaine'] as $champ) {
        if (empty($data[$champ])) {
            $champsManquants[] = $champ;
            $data[$champ] = null; // Assure qu'on peut insérer quand même
        }
    }

    // Gestion domaine
    $idDomaine = $data['domaine'] ? $this->resolveDomaineId($data['domaine']) : null;

    // Gestion user
    if (!auth()->check()) {
        return response()->json([
            'success' => false,
            'type' => 'erreur',
            'error' => 'Utilisateur non authentifié.'
        ], 401);
    }

    // Création en brouillon
    $appel = new appelle_offres();
    $appel->titre = $data['titre'] ?? 'Sans titre';
    $appel->description = $data['description'] ?? '';
    $appel->budget = isset($data['budget']) ? (float)$data['budget'] : 0;
    $appel->date_debut = $data['date_debut'] ?? now()->toDateString();
    $appel->date_fin = $data['date_fin'] ?? now()->addDays(7)->toDateString();
    $appel->idDomaine = $idDomaine;
    $appel->idUser = auth()->id();
    $appel->statut = 'brouillon'; // 👈 Brouillon au lieu de "publiee"
    $appel->date_publication = now();
    $appel->save();

    return response()->json([
        'success' => true,
        'type' => 'appel_offre_brouillon',
        'message' => 'Appel d\'offre généré en brouillon. Veuillez le compléter via l’interface.',
        'incomplet' => count($champsManquants) > 0,
        'champs_manquants' => $champsManquants,
        'lien_modification' => url('http://localhost:5173/appelles'), // ou front URL complète
        'data' => $appel
    ]);
}

public function aideRedactionSoumission(Request $request)
{
    $nomAppel = $request->input('nomAppel');

    $appel = appelle_offres::where('titre', 'LIKE', '%' . $nomAppel . '%')->first();

    if (!$appel) {
        return response()->json([
            'success' => false,
            'type' => 'erreur',
            'message' => "Aucun appel d'offre nommé \"$nomAppel\""
        ], 404);
    }

    $prompt = "Tu veux proposer une soumission pour \"$nomAppel\".
Génère une soumission au format JSON uniquement, sans explication, avec les champs suivants :
- prixPropose (en dinars tunisiens)
- description (max 3 lignes)
- temps_realisation (en jours ou semaines)

Format strict : pas de ```json ni autre balise autour du JSON.";

    $response = $this->askAI($prompt);

    // 🧹 Nettoyage éventuel des balises Markdown s’il y en a
    $cleaned = preg_replace('/```(json|text)?/i', '', $response);
    $cleaned = trim($cleaned);

    // 🧪 Essaye de parser comme JSON
    $data = json_decode($cleaned, true);

    if (is_array($data)) {
        // ✅ JSON valide
        return response()->json([
            'success' => true,
            'type' => 'aide_redaction',
'message' => "Voici la proposition générée pour l’appel d’offre sélectionné.",
            'data' => $data,
            'nomAppel' => $nomAppel
        ]);
    }

    // ❌ JSON invalide → on retourne le texte brut généré
    return response()->json([
        'success' => true,
        'type' => 'aide_redaction',
        'message' => "Soumission générée (texte brut, non JSON)",
        'message_ai' => $response  // ou $cleaned
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
        'data' => [
            'appels' => $appels
        ]
    ]);
}


    public function checkAppelDatesByTitre($titre, $typeDate = 'fin')
{
    $appel = appelle_offres::where('titre', 'LIKE', '%' . $titre . '%')->first();

    if (!$appel) {
        return response()->json([
            'success' => false,
            'type' => 'date',
            'error' => "Aucun appel d'offre trouvé pour le titre \"$titre\"."
        ], 404);
    }

    $dateDebut = \Carbon\Carbon::parse($appel->date_debut);
    $dateFin = \Carbon\Carbon::parse($appel->date_fin);

if ($typeDate === 'debut') {
    return response()->json([
        'success' => true,
        'type' => 'date_debut',
        'data' => [
            'date_debut' => $dateDebut->toDateString()
        ]
    ]);
}


    $expired = now()->greaterThan($dateFin);

    return response()->json([
        'success' => true,
        'type' => 'deadline',
        'expired' => $expired,
        'deadline' => $dateFin->toDateString()
    ]);
}





public function handleUnifiedChat(Request $request)
{
    $userInput = $request->input('message');

    // ✅ PROMPT AMÉLIORÉ
    $intentPrompt = "Voici une requête utilisateur : \"$userInput\".

Donne UNIQUEMENT l’intention de cette requête parmi la liste ci-dessous.
Réponds UNIQUEMENT par : intention=xxxxx (aucune autre phrase ni ponctuation).

Liste des intentions :
- intention=creer_appel
- intention=verifier_deadline
- intention=verifier_debut
- intention=verifier_contrat
- intention=aide_redaction
- intention=appels_recents";

    // 🔍 Requête à l'IA
    $intentRaw = $this->askAI($intentPrompt);
    logger('Intent IA détecté : ' . $intentRaw);

    $intent = strtolower(trim(str_replace('intention=', '', $intentRaw)));

    // 🔎 Extraction du titre si nécessaire
    preg_match('/(?:appel|offre|soumission)[^a-zA-Z0-9]*([^\?]+)/i', $userInput, $matches);
    $titre = isset($matches[1]) ? trim($matches[1]) : null;

    // 🧠 Routing selon l’intention détectée
    switch ($intent) {
       
       case 'creer_appel':
return $this->createAppelOffreFromPrompt(new Request(['message' => $userInput]));

        case 'verifier_deadline':
            if (!$titre) {
                return response()->json(['type' => 'erreur', 'error' => 'Titre manquant pour la deadline.'], 400);
            }
            return $this->checkAppelDatesByTitre($titre, 'fin');

       case 'verifier_debut':
    // meilleure extraction de titre
    $titre = appelle_offres::where('titre', 'LIKE', '%' . $userInput . '%')->value('titre');
    if (!$titre) return response()->json(['type' => 'erreur', 'error' => 'Titre introuvable.'], 404);
    return $this->checkAppelDatesByTitre($titre, $intent === 'verifier_debut' ? 'debut' : 'fin');


        case 'verifier_contrat':
            if (!$titre) {
                return response()->json(['type' => 'erreur', 'error' => 'Nom de soumission manquant pour le contrat.'], 400);
            }
            return $this->isContratGenere($titre);

       case 'aide_redaction':
    // Nouvelle extraction robuste
    $titre = appelle_offres::where('titre', 'LIKE', '%' . $userInput . '%')->value('titre');
return $this->aideRedactionSoumission(new Request(['nomAppel' => $titre]));


        case 'appels_recents':
            return $this->appelsRecents();

        default:
            return response()->json([
                'type' => 'inconnu',
                'intent_recu' => $intentRaw,
                'intent_nettoye' => $intent,
                'message' => "Je n'ai pas compris votre demande."
            ], 200);
    }
}









    private function resolveDomaineId($nom)
    {
        $domaine = \App\Models\Domaine::where('nom', 'LIKE', '%' . $nom . '%')->first();
        return $domaine ? $domaine->idDomaine : null;
    }
}
