<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicament;

class MedicamentController extends Controller
{
    /**
     * Afficher tous les médicaments.
     */
    public function index()
    {
        return response()->json([
            'message' => 'Liste des médicaments récupérée avec succès.',
            'medicaments' => Medicament::all(),
        ], 200);
    }

    /**
     * Créer un nouveau médicament.
     */
    public function store(Request $request)
    {
        // Vérification du rôle
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès interdit. Vous devez être administrateur.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'prix' => 'required|numeric|min:0',
            'photo' => 'nullable|url',
        ]);

        $medicament = Medicament::create($validated);

        return response()->json([
            'message' => 'Médicament créé avec succès.',
            'medicament' => $medicament,
        ], 201);
    }

    /**
     * Afficher les détails d'un médicament.
     */
    public function show($id)
    {
        $medicament = Medicament::find($id);

        if (!$medicament) {
            return response()->json(['message' => 'Médicament introuvable.'], 404);
        }

        return response()->json([
            'message' => 'Médicament récupéré avec succès.',
            'medicament' => $medicament,
        ], 200);
    }

    /**
     * Mettre à jour un médicament.
     */
    public function update(Request $request, $id)
    {
        // Vérification du rôle
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès interdit. Vous devez être administrateur.'], 403);
        }

        $medicament = Medicament::find($id);

        if (!$medicament) {
            return response()->json(['message' => 'Médicament introuvable.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'prix' => 'sometimes|numeric|min:0',
            'photo' => 'nullable|url',
        ]);

        $medicament->update($validated);

        return response()->json([
            'message' => 'Médicament mis à jour avec succès.',
            'medicament' => $medicament,
        ], 200);
    }

    /**
     * Supprimer un médicament.
     */
    public function destroy($id)
    {
        // Vérification du rôle
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès interdit. Vous devez être administrateur.'], 403);
        }

        $medicament = Medicament::find($id);

        if (!$medicament) {
            return response()->json(['message' => 'Médicament introuvable.'], 404);
        }

        $medicament->delete();

        return response()->json(['message' => 'Médicament supprimé avec succès.'], 200);
    }
}
