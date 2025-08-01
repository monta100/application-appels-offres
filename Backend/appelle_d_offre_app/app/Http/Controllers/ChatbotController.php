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




  public function isContratGenere($input)
{
    // 🔎 Cherche l’appel d’offre par titre
    $appel = appelle_offres::where('titre', 'like', '%' . $input . '%')->first();

    if (!$appel) {
        return response()->json([
            'success' => false,
            'type' => 'erreur',
            'error' => "Aucun appel d'offre trouvé avec un titre similaire à \"$input\"."
        ], 404);
    }

    // 📌 Récupère toutes les soumissions de cet appel
    $soumissions = Soumission::where('idAppel', $appel->idAppel)->get();

    if ($soumissions->isEmpty()) {
        return response()->json([
            'success' => false,
            'type' => 'erreur',
            'error' => "Aucune soumission trouvée pour l'appel d'offre \"$appel->titre\"."
        ], 404);
    }

    // ✅ Vérifie si une soumission a un contrat associé (PDF ou non)
    foreach ($soumissions as $soumission) {
        if (Contrat::where('idSoumission', $soumission->idSoumission)->exists()) {
            return response()->json([
                'success' => true,
                'type' => 'contrat',
                'contrat_generé' => true,
                'appel_offre' => $appel->titre,
                'soumission_id' => $soumission->idSoumission,
                'message' => "✅ Un contrat a bien été généré pour l’appel d’offre « {$appel->titre} ». Merci de consulter la page des contrats.",
                'lien' => 'http://localhost:5173/Soumission_chosi',

            ]);
        }
    }

    // ❌ Aucun contrat généré
    return response()->json([
        'success' => true,
        'type' => 'contrat',
        'contrat_generé' => false,
        'appel_offre' => $appel->titre,
    'message' => "❌ Aucun contrat n’a encore été généré pour l’appel d’offre « {$appel->titre} ».",
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

    if ($typeDate === 'debut') {
        return response()->json([
            'success' => true,
            'type' => 'date_debut',
            'data' => [
                'date_debut' => \Carbon\Carbon::parse($appel->date_debut)->toDateString()
            ]
        ]);
    }

    // par défaut, on retourne la date de fin
    return response()->json([
        'success' => true,
        'type' => 'date_fin',
        'data' => [
            'date_fin' => \Carbon\Carbon::parse($appel->date_fin)->toDateString()
        ]
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
- intention=verifier_contrat
- intention=aide_redaction
- intention=appels_recents;
- intention=date_appel";


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

       

case 'verifier_contrat':
    $titre = $this->extraireTitreAppel($userInput);
    if (!$titre) {
        return response()->json([
            'type' => 'erreur',
            'error' => 'Titre introuvable pour vérifier le contrat.'
        ], 404);
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






case 'date_appel':
    $titre = $this->extraireTitreAppel($userInput); // ✔️

    // Ajoute une logique de secours si besoin
    if (!$titre && strlen($userInput) < 40) {
        $titre = appelle_offres::where('titre', 'LIKE', '%' . $userInput . '%')->value('titre');
    }

    if (!$titre) {
        return response()->json([
            'type' => 'date',
            'success' => false,
            'error' => "Titre de l'appel d'offre non spécifié."
        ], 400);
    }

    $messageLower = strtolower($userInput);
    $typeDate = str_contains($messageLower, 'début') || str_contains($messageLower, 'debut') || str_contains($messageLower, 'commence') ? 'debut' : 'fin';

    return $this->checkAppelDatesByTitre($titre, $typeDate);
    }}




private function extraireTitreAppel($texte)
{
    // 1. Liste des titres connus
    $titresExistants = appelle_offres::pluck('titre')->toArray();

    // 2. Essayer d’extraire manuellement après les mots clés
    preg_match('/(appel d’offre|appel|soumission|offre)[^a-zA-Z0-9]*([^\?]+)/i', $texte, $matches);
    $extrait = isset($matches[2]) ? trim($matches[2]) : $texte;

    // 3. Fuzzy matching : trouver le plus proche
    $meilleurTitre = null;
    $meilleureSimilarite = 0;

    foreach ($titresExistants as $titre) {
        similar_text(strtolower($extrait), strtolower($titre), $similarite);
        if ($similarite > $meilleureSimilarite) {
            $meilleureSimilarite = $similarite;
            $meilleurTitre = $titre;
        }
    }

    return $meilleureSimilarite >= 60 ? $meilleurTitre : null;
}



    private function resolveDomaineId($nom)
    {
        $domaine = \App\Models\Domaine::where('nom', 'LIKE', '%' . $nom . '%')->first();
        return $domaine ? $domaine->idDomaine : null;
    }
}
