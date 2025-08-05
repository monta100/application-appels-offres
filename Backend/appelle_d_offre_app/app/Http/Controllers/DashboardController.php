<?php

namespace App\Http\Controllers;

use App\Models\appelle_offres;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Soumission;
use App\Models\Contrat;
use Carbon\Carbon;

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
}
