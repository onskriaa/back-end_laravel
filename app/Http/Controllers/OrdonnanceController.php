<?php

namespace App\Http\Controllers;

use App\Models\Ordonnance;
use App\Models\Medicament;
use Illuminate\Http\Request;

class OrdonnanceController extends Controller
{
    public function store(Request $request)
    {
        // Vérifiez que l'utilisateur est authentifié et qu'il est un médecin
        if (!auth()->check() || auth()->user()->role !== 'medecin') {
            return response()->json(['message' => 'Accès interdit. Seuls les médecins peuvent créer des ordonnances.'], 403);
        }
    
        // Vérifiez si un médecin est associé à cet utilisateur
        $medecin = \App\Models\Medecin::where('user_id', auth()->id())->first();
        if (!$medecin) {
            return response()->json(['message' => 'Aucun médecin associé à cet utilisateur.'], 404);
        }
    
        // Valider les données
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'date' => 'required|date',
            'details' => 'required|string',
            'medicaments' => 'required|array|min:1',
            'medicaments.*.id' => 'required|exists:medicaments,id',
            'medicaments.*.quantite' => 'required|integer|min:1',
        ]);
    
        // Créer l'ordonnance
        $ordonnance = Ordonnance::create([
            'medecin_id' => $medecin->id,
            'patient_id' => $request->patient_id,
            'date' => $request->date,
            'details' => $request->details,
        ]);
    
        // Ajouter les médicaments
        foreach ($request->medicaments as $medicament) {
            $ordonnance->medicaments()->attach($medicament['id'], ['quantite' => $medicament['quantite']]);
        }
    
        return response()->json(['message' => 'Ordonnance créée avec succès.', 'ordonnance' => $ordonnance->load('medicaments')], 201);
    }
    public function update(Request $request, $id)
    {
        // Vérifier si l'utilisateur est un médecin
        if (!auth()->check() || auth()->user()->role !== 'medecin') {
            return response()->json(['message' => 'Accès interdit. Seuls les médecins peuvent modifier des ordonnances.'], 403);
        }
    
        // Récupérer le médecin associé à l'utilisateur
        $medecin = \App\Models\Medecin::where('user_id', auth()->id())->first();
        if (!$medecin) {
            return response()->json(['message' => 'Aucun médecin associé à cet utilisateur.'], 404);
        }
    
        // Récupérer l'ordonnance
        $ordonnance = Ordonnance::where('id', $id)
                                ->where('medecin_id', $medecin->id)
                                ->first();
    
        if (!$ordonnance) {
            return response()->json(['message' => 'Ordonnance introuvable ou non autorisée.'], 404);
        }
    
        // Valider les données
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'date' => 'required|date',
            'details' => 'required|string',
        ]);
    
        // Mettre à jour l'ordonnance
        $ordonnance->update([
            'patient_id' => $request->patient_id,
            'date' => $request->date,
            'details' => $request->details,
        ]);
    
        return response()->json(['message' => 'Ordonnance mise à jour avec succès.', 'ordonnance' => $ordonnance], 200);
    }
    ///////////ajouter par moi 
    public function destroy($id)
{
    // ✅ 1. Vérifier l'authentification et le rôle
    if (!auth()->check() || auth()->user()->role !== 'medecin') {
        return response()->json(['message' => 'Accès interdit. Seuls les médecins peuvent supprimer des ordonnances.'], 403);
    }

    // ✅ 2. Récupérer le médecin lié à l'utilisateur
    $medecin = \App\Models\Medecin::where('user_id', auth()->id())->first();
    if (!$medecin) {
        return response()->json(['message' => 'Aucun médecin associé à cet utilisateur.'], 404);
    }

    // ✅ 3. Rechercher l’ordonnance par ID
    $ordonnance = \App\Models\Ordonnance::find($id);

    if (!$ordonnance) {
        return response()->json(['message' => 'Ordonnance introuvable.'], 404);
    }

    // ✅ 4. Vérifier que cette ordonnance appartient bien au médecin connecté
    if ($ordonnance->medecin_id != $medecin->id) {
        return response()->json(['message' => 'Vous n\'êtes pas autorisé à supprimer cette ordonnance.'], 403);
    }

    // ✅ 5. Supprimer l’ordonnance
    $ordonnance->delete();

    return response()->json(['message' => 'Ordonnance supprimée avec succès.'], 200);
}

    ///////////ajouter par moi
    public function getOrdonnancesByMedecin()
    {
        // Vérifiez si l'utilisateur est authentifié et qu'il est un médecin
        if (!auth()->check() || auth()->user()->role !== 'medecin') {
            return response()->json(['message' => 'Accès interdit. Seuls les médecins peuvent voir leurs ordonnances.'], 403);
        }
    
        // Récupérer le médecin associé à l'utilisateur connecté
        $medecin = \App\Models\Medecin::where('user_id', auth()->id())->first();
        if (!$medecin) {
            return response()->json(['message' => 'Aucun médecin associé à cet utilisateur.'], 404);
        }
    
        // Récupérer toutes les ordonnances du médecin avec les informations nécessaires
        $ordonnances = \App\Models\Ordonnance::with(['medicaments'])
            ->where('medecin_id', $medecin->id)
            ->get()
            ->map(function ($ordonnance) {
                return [
                    'id' => $ordonnance->id, // ← AJOUTÉ ICI
                    'patient_id' => $ordonnance->patient_id,
                    'date' => $ordonnance->date,
                    'details' => $ordonnance->details,
                    'medicaments' => $ordonnance->medicaments->map(function ($medicament) {
                        return [
                            'id' => $medicament->id,
                            'name' => $medicament->name,
                            'quantite' => $medicament->pivot->quantite,
                        ];
                    }),
                ];
            });
    
        return response()->json(['ordonnances' => $ordonnances], 200);
    }
    public function getAllOrdonnances()
{
    // Vérifier si l'utilisateur est un administrateur
    if (!auth()->check() || auth()->user()->role !== 'admin') {
        return response()->json(['message' => 'Accès interdit. Seuls les administrateurs peuvent voir toutes les ordonnances.'], 403);
    }

    // Récupérer toutes les ordonnances avec les informations des patients et des médicaments
    $ordonnances = \App\Models\Ordonnance::with([
        'patient:id,nom,prenom',
        'medicaments:id,name,description'
    ])
    ->select('id', 'patient_id', 'date', 'details')
    ->get();

    // Supprimer les données du pivot
    $ordonnances->each(function ($ordonnance) {
        $ordonnance->medicaments->each(function ($medicament) {
            unset($medicament->pivot);
        });
    });

    return response()->json(['ordonnances' => $ordonnances], 200);
}
public function getOrdonnancesForPatient()
{
    if (!auth()->check() || auth()->user()->role !== 'patient') {
        return response()->json(['message' => 'Accès interdit.'], 403);
    }

    $patient = \App\Models\Patient::where('user_id', auth()->id())->first();

    if (!$patient) {
        return response()->json(['message' => 'Aucun patient associé à cet utilisateur.'], 404);
    }

    // Charger les ordonnances avec les médicaments ET leur quantité depuis le pivot
    $ordonnances = \App\Models\Ordonnance::with('medicaments')->where('patient_id', $patient->id)->get();

    $ordonnances = $ordonnances->map(function ($ordonnance) {
        return [
            'id' => $ordonnance->id,
            'date' => $ordonnance->date,
            'details' => $ordonnance->details,
            'medicaments' => $ordonnance->medicaments->map(function ($medicament) {
                return [
                    'name' => $medicament->name,
                    'quantite' => $medicament->pivot->quantite, // ✅ on garde le pivot
                ];
            }),
        ];
    });

    return response()->json(['ordonnances' => $ordonnances], 200);
}


}    