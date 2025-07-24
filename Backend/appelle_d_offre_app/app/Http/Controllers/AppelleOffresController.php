<?php

namespace App\Http\Controllers;

use App\Models\appelle_offres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppelleOffresController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $appels = appelle_offres::with(['domaine', 'user'])
                ->where('statut', '!=', 'brouillon')
                ->get();

    return response()->json($appels);
}



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
{
    $validated = $request->validate([
        'titre' => 'required|string|max:255',
        'description' => 'required|string',
        'budget' => 'required|numeric|min:0',
         'date_debut' => 'required|date',        
         'date_fin' => 'required|date|after_or_equal:date_debut',
        'statut' => 'nullable|string',
        'date_publication' => 'nullable|date',
        'idDomaine' => 'required|exists:domaines,idDomaine',
        'fichier_joint' => 'nullable|file|mimes:pdf,docx,doc|max:2048',
        'nomSociete' => 'string'

    ]);

     

    if ($request->hasFile('fichier_joint')) {
        $validated['fichier_joint'] = $request->file('fichier_joint')->store('fichiers_appels');
    }

    $validated['idUser'] = auth()->id(); // On injecte le user connecté
    appelle_offres::create($validated);

    return response()->json(['message' => 'Ajout réussi']);
}


    /**
     * Display the specified resource.
     */
   public function show($id)
{
    $offre = appelle_offres::with(['user', 'domaine', 'soumissions'])->findOrFail($id);
    return response()->json($offre);
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(appelle_offres $appelle_offres)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, $id)
{
    $offre = appelle_offres::findOrFail($id);

    $validated = $request->validate([
        'titre' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'budget' => 'sometimes|numeric|min:0',
        'date_limite' => 'sometimes|date',
        'statut' => 'nullable|string',
        'date_debut' => 'date',        
         'date_fin' => 'date|after_or_equal:date_debut',
        'date_publication' => 'nullable|date',
        'idDomaine' => 'exists:domaines,idDomaine',
        'fichier_joint' => 'nullable|file|mimes:pdf,docx,doc|max:2048'
    ]);

    if ($request->hasFile('fichier_joint')) {
        $validated['fichier_joint'] = $request->file('fichier_joint')->store('fichiers_appels');
    }

    $offre->update($validated);

    return response()->json($offre);
}


    /**
     * Remove the specified resource from storage.
     */
  public function destroy($id)
{
    $offre = appelle_offres::findOrFail($id);
    $offre->delete();

    return response()->json(['message' => 'Appel d’offre supprimé avec succès.']);
}


public function userAppels()
{
    return appelle_offres::with('domaine')
        ->where('idUser', auth()->id())
        ->get();
}


}
