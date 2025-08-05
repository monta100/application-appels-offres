<?php

namespace App\Http\Controllers;

use App\Models\appelle_offres;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Soumission;
use App\Models\Contrat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
{
    $now = Carbon::now();
    $startThisWeek = $now->copy()->startOfWeek(); // lundi cette semaine
    $startLastWeek = $now->copy()->subWeek()->startOfWeek(); // lundi semaine dernière
    $endLastWeek = $now->copy()->subWeek()->endOfWeek(); // dimanche semaine dernière

    return response()->json([
        // Valeurs totales actuelles
        'appels_count' => appelle_offres::count(),
        'users_count' => User::count(),
        'soumissions_count' => Soumission::count(),
        'contrats_count' => Contrat::count(),

        // Valeurs de cette semaine
        'appels_this_week' => appelle_offres::where('created_at', '>=', $startThisWeek)->count(),
        'users_this_week' => User::where('created_at', '>=', $startThisWeek)->count(),
        'soumissions_this_week' => Soumission::where('created_at', '>=', $startThisWeek)->count(),
        'contrats_this_week' => Contrat::where('created_at', '>=', $startThisWeek)->count(),

        // Valeurs de la semaine passée
        'appels_last_week' => appelle_offres::whereBetween('created_at', [$startLastWeek, $endLastWeek])->count(),
        'users_last_week' => User::whereBetween('created_at', [$startLastWeek, $endLastWeek])->count(),
        'soumissions_last_week' => Soumission::whereBetween('created_at', [$startLastWeek, $endLastWeek])->count(),
        'contrats_last_week' => Contrat::whereBetween('created_at', [$startLastWeek, $endLastWeek])->count(),
    ]);
}

public function dashboardActivites()
{
    try {
        $activites = [];

        // 🔹 Contrats signés cette semaine
        $contrats = Contrat::where('created_at', '>=', Carbon::now()->startOfWeek())
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($contrat) {
                return [
                    'title' => "Contrat signé avec " . $contrat->soumission->user->nom,
                    'datetime' => $contrat->created_at->format('d M H:i'),
                    'type' => 'contrat',
                ];
            });

        // 🔹 Soumissions récentes
        $soumissions = Soumission::where('created_at', '>=', Carbon::now()->startOfWeek())
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($soumission) {
                return [
                    'title' => "Soumission par " . $soumission->user->nom,
                    'datetime' => $soumission->created_at->format('d M H:i'),
                    'type' => 'soumission',
                ];
            });

        // 🔹 Appels d'offres publiés
        $appels = appelle_offres::where('created_at', '>=', Carbon::now()->startOfWeek())
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($appel) {
                return [
                    'title' => "Nouvel appel d'offre publié",
                    'datetime' => $appel->created_at->format('d M H:i'),
                    'type' => 'appel',
                ];
            });

        $activites = $contrats->merge($soumissions)->merge($appels)->sortByDesc('datetime')->values();

        return response()->json($activites);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function soumissionsParSemaine()
{
    $now = Carbon::now();
    $soumissionsParSemaine = [];

    for ($i = 5; $i >= 0; $i--) {
        $startOfWeek = $now->copy()->subWeeks($i)->startOfWeek();
        $endOfWeek = $now->copy()->subWeeks($i)->endOfWeek();

        $count = Soumission::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
        $soumissionsParSemaine[] = $count;
    }

    return response()->json([
        'year' => $now->year,
        'soumissions_par_semaine' => $soumissionsParSemaine,
    ]);
}

public function getActiviteGlobale()
{
    try {
        // Récupération des données de la semaine actuelle
        $now = \Carbon\Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();

        // Nombre d’actions pendant cette semaine
        $soumissions = \App\Models\Soumission::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
        $utilisateurs = \App\Models\User::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
        $contrats = \App\Models\Contrat::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

        // Poids de chaque activité (ajuste selon tes priorités)
        $poidsSoumission = 4;
        $poidsUtilisateur = 3;
        $poidsContrat = 5;

        // Score brut
        $score = ($soumissions * $poidsSoumission) + ($utilisateurs * $poidsUtilisateur) + ($contrats * $poidsContrat);

        // Conversion en pourcentage sur 100 max
        $indice = min(round($score), 100); // ne jamais dépasser 100

        return response()->json([
            'success' => true,
            'indice' => $indice,
            'details' => [
                'soumissions' => $soumissions,
                'utilisateurs' => $utilisateurs,
                'contrats' => $contrats
            ]
        ]);
    } catch (\Throwable $e) {
        return response()->json(['error' => 'Erreur interne'], 500);
    }
}

public function appelsParSemaine()
{
    $weeks = DB::table('appelle_offres')
        ->selectRaw('WEEK(created_at) as semaine, COUNT(*) as total')
        ->where('created_at', '>=', now()->subWeeks(6))
        ->groupBy('semaine')
        ->orderBy('semaine')
        ->get();

    $data = array_fill(0, 6, 0);

    $currentWeek = Carbon::now()->weekOfYear;

    foreach ($weeks as $w) {
        $index = 5 - ($currentWeek - $w->semaine); // Pour avoir [S-5, ..., S]
        if ($index >= 0 && $index <= 5) {
            $data[$index] = $w->total;
        }
    }

    return response()->json([
        'appels_par_semaine' => $data
    ]);
}

public function appelsParDomaine()
{
    $domaines = DB::table('appelle_offres')
        ->join('domaines', 'appelle_offres.idDomaine', '=', 'domaines.idDomaine')
        ->select('domaines.nom as label', DB::raw('COUNT(*) as value'))
        ->groupBy('domaines.nom')
        ->get();

    return response()->json($domaines);
}

public function getTopUsers()
{
    // Top prestataires par soumissions
   $prestatairesActifs = User::select(
        'users.idUser', // 👈 précision de la table
        'users.nom',
        'users.prenom',
        DB::raw('COUNT(soumissions.idSoumission) as total_soumissions')
    )
    ->join('soumissions', 'users.idUser', '=', 'soumissions.idUser')
    ->where('users.role', 'participant')
    ->groupBy('users.idUser', 'users.nom', 'users.prenom')
    ->orderByDesc('total_soumissions')
    ->limit(5)
    ->get();

    // Top représentants par appels
   $representantsActifs = User::select(
        'users.idUser',
        'users.nom',
        'users.prenom',
        DB::raw('COUNT(appelle_offres.idAppel) as total_appels')
    )
    ->join('appelle_offres', 'users.idUser', '=', 'appelle_offres.idUser')
    ->where('users.role', 'representant')
    ->groupBy('users.idUser', 'users.nom', 'users.prenom')
    ->orderByDesc('total_appels')
    ->limit(5)
    ->get();

    // Prestataires les plus choisis
    $prestatairesChoisis = User::select(
        'users.idUser',
        'users.nom',
        'users.prenom',
        DB::raw('COUNT(soumissions.idSoumission) as total_selectionnees')
    )
    ->join('soumissions', 'users.idUser', '=', 'soumissions.idUser')
    ->where('users.role', 'participant')
    ->where('soumissions.choisie', true)
    ->groupBy('users.idUser', 'users.nom', 'users.prenom')
    ->orderByDesc('total_selectionnees')
    ->limit(5)
    ->get();
return response()->json([
    'prestatairesActifs' => $prestatairesActifs,
    'representantsActifs' => $representantsActifs,
    'prestatairesChoisis' => $prestatairesChoisis
]);

}


}
