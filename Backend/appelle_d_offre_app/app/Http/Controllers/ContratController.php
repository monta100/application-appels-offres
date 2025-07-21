<?php

namespace App\Http\Controllers;

use App\Models\contrat;
use Illuminate\Http\Request;

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
}
