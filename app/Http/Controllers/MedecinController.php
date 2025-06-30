<?php

namespace App\Http\Controllers;

use App\Models\Medecin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MedecinController extends Controller
{
    public function store(Request $request)
    {
        // Vérifiez si l'utilisateur est authentifié et qu'il a le rôle d'administrateur
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès interdit. Seul un administrateur peut ajouter des médecins.'], 403);
        }

        // Valider les données du formulaire
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'specialite' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Créer l'utilisateur dans la table `users`
        $user = User::create([
            'name' => $request->nom . ' ' . $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'medecin', // Attribuer le rôle "medecin"
        ]);

        // Ajouter les informations spécifiques dans la table `medecins`
        $medecin = Medecin::create([
            'user_id' => $user->id,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'specialite' => $request->specialite,
        ]);

        return response()->json(['message' => 'Médecin ajouté avec succès', 'medecin' => $medecin], 201);
    }
    public function index()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès interdit. Seul un administrateur peut ajouter des médecins.'], 403);
        }
        // Récupérer tous les médecins avec leur utilisateur associé
        $medecins = Medecin::with('user')->get();

        return response()->json([
            'message' => 'Liste des médecins récupérée avec succès.',
            'medecins' => $medecins,
        ], 200);
    }
    public function show($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès interdit. Seul un administrateur peut ajouter des médecins.'], 403);
        }
        // Trouver le médecin par ID
        $medecin = Medecin::with('user')->find($id);

        // Vérifiez si le médecin existe
        if (!$medecin) {
            return response()->json(['message' => 'Médecin introuvable.'], 404);
        }

        // Retourner les informations du médecin
        return response()->json([
            'message' => 'Détails du médecin récupérés avec succès.',
            'medecin' => $medecin,
        ], 200);
    }
    public function destroy($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès interdit. Seul un administrateur peut ajouter des médecins.'], 403);
        }
        // Trouver le médecin par ID
        $medecin = Medecin::find($id);

        // Vérifiez si le médecin existe
        if (!$medecin) {
            return response()->json(['message' => 'Médecin introuvable.'], 404);
        }

        // Supprimer le médecin
        $medecin->delete();

        return response()->json(['message' => 'Médecin supprimé avec succès.'], 200);
    }
    public function update(Request $request, $id)
{
    // Vérifier si l'utilisateur est admin
    if (!auth()->check() || auth()->user()->role !== 'admin') {
        return response()->json(['message' => 'Accès interdit. Seul un administrateur peut modifier les médecins.'], 403);
    }

    // Trouver le médecin par ID
    $medecin = Medecin::with('user')->find($id);

    if (!$medecin) {
        return response()->json(['message' => 'Médecin introuvable.'], 404);
    }

    // Valider les données entrantes
    $request->validate([
        'nom' => 'sometimes|string|max:255',
        'prenom' => 'sometimes|string|max:255',
        'specialite' => 'sometimes|string|max:255',
        'email' => 'sometimes|email|unique:users,email,' . $medecin->user_id, // Vérifier l'email pour l'utilisateur
        'password' => 'nullable|string|min:8', // Password facultatif
    ]);

    // Mettre à jour les informations de l'utilisateur (dans `users`)
    if ($request->has('email') || $request->has('password')) {
        $medecin->user->update([
            'email' => $request->email ?? $medecin->user->email,
            'password' => $request->password ? Hash::make($request->password) : $medecin->user->password,
        ]);
    }

    // Mettre à jour les informations spécifiques au médecin (dans `medecins`)
    $medecin->update([
        'nom' => $request->nom ?? $medecin->nom,
        'prenom' => $request->prenom ?? $medecin->prenom,
        'specialite' => $request->specialite ?? $medecin->specialite,
    ]);

    return response()->json([
        'message' => 'Médecin mis à jour avec succès.',
        'medecin' => $medecin,
    ], 200);
}
public function getAllMedecins()
{
    // Vérifier si l'utilisateur est un administrateur
    if (!auth()->check() || auth()->user()->role !== 'admin') {
        return response()->json(['message' => 'Accès interdit. Seuls les administrateurs peuvent voir la liste des médecins.'], 403);
    }

    // Récupérer tous les médecins
    $medecins = \App\Models\Medecin::select('id', 'nom', 'prenom', 'specialite')->get();

    return response()->json(['medecins' => $medecins], 200);
}




}
