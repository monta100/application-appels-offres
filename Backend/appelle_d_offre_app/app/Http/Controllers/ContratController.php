<?php

namespace App\Http\Controllers;

use App\Models\contrat;
use App\Models\Soumission;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

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



  public function genererContratComplet($idSoumission)
{
    $soumission = Soumission::with(['appelOffre', 'user'])->findOrFail($idSoumission);

    // Vérifier si un contrat existe déjà
    if ($soumission->contrat) {
        return response()->json(['message' => 'Un contrat a déjà été généré pour cette soumission.'], 409);
    }

    // Générer le PDF
    $pdf = Pdf::loadView('pdf.contrat', compact('soumission'));

    $fileName = "contrat_{$soumission->idSoumission}_" . time() . ".pdf";
    $filePath = "contrats/{$fileName}";

    // Sauvegarder le fichier dans storage/app/public/contrats
    Storage::disk('public')->put($filePath, $pdf->output());

    // Créer le contrat avec le lien du fichier PDF
    $contrat = Contrat::create([
        'idSoumission' => $soumission->idSoumission,
        'date_creation' => now(),
        'fichier_pdf' => $filePath,
    ]);

    return response()->json([
        'message' => 'Contrat généré avec succès.',
        'contrat' => $contrat,
        'fichier_pdf' => asset("storage/{$filePath}")
    ]);
}

}
