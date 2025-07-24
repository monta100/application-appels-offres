<?php

namespace App\Http\Controllers;

use App\Models\contrat;
use Illuminate\Http\Request;
use App\Models\Soumission;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
class ContratController extends Controller
{
    public function index()
    {
        $contrats = contrat::with('soumission')->latest()->get();
        return response()->json($contrats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fichierpdf' => 'nullable|file|mimes:pdf|max:2048',
            'date_creation' => 'nullable|date',
            'idSoumission' => 'required|exists:soumissions,idSoumission',
        ]);

        if ($request->hasFile('fichierpdf')) {
            $validated['fichierpdf'] = $request->file('fichierpdf')->store('contrats');
        }

        $contrat = contrat::create($validated);
        return response()->json($contrat, 201);
    }

    public function show($id)
    {
        $contrat = contrat::with('soumission')->findOrFail($id);
        return response()->json($contrat);
    }

    public function update(Request $request, $id)
    {
        $contrat = contrat::findOrFail($id);

        $validated = $request->validate([
            'fichierpdf' => 'nullable|file|mimes:pdf|max:2048',
            'date_creation' => 'nullable|date',
            'idSoumission' => 'sometimes|required|exists:soumissions,idSoumission',
        ]);

        if ($request->hasFile('fichierpdf')) {
            $validated['fichierpdf'] = $request->file('fichierpdf')->store('contrats');
        }

        $contrat->update($validated);
        return response()->json($contrat);
    }

    public function destroy($id)
    {
        $contrat = contrat::findOrFail($id);
        $contrat->delete();

        return response()->json(['message' => 'Contrat supprimé avec succès.']);
    }



    public function genererContratPourSoumission($idSoumission)
{
    $soumission = Soumission::with('appelOffre', 'user')->findOrFail($idSoumission);

    // Vérifier si un contrat existe déjà
    if ($soumission->contrat) {
        return response()->json(['message' => 'Un contrat a déjà été généré pour cette soumission.'], 409);
    }

    // Création du contrat
    $contrat = contrat::create([
        'idSoumission' => $soumission->idSoumission,
        'date_creation' => Carbon::now(),
        'fichier_pdf' => null // À générer plus tard automatiquement
    ]);

    return response()->json([
        'message' => 'Contrat généré avec succès.',
        'contrat' => $contrat
    ]);
}


public function genererPDF($idSoumission)
{
    $soumission = soumission::with(['user', 'appelOffre'])->findOrFail($idSoumission);

    $pdf = Pdf::loadView('pdf.contrat', compact('soumission'));

    return $pdf->download("contrat_{$soumission->idSoumission}.pdf");
}
}
