<?php

namespace App\Http\Controllers;

use App\Models\soumission;
use Illuminate\Http\Request;

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
            'idUser' => 'required|exists:users,id',
            'idAppel' => 'required|exists:appelle_offres,idAppel',
        ]);

        if ($request->hasFile('fichier_joint')) {
            $validated['fichier_joint'] = $request->file('fichier_joint')->store('fichiers_soumissions');
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
            'idUser' => 'sometimes|required|exists:users,id',
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
}
