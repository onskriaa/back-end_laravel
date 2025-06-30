<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Créer l'utilisateur dans la table `users`
        $user = User::create([
            'name' => $request->nom . ' ' . $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'patient',
        ]);

        // Ajouter les informations spécifiques dans la table `patients`
        $patient = Patient::create([
            'user_id' => $user->id,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'date_naissance' => $request->date_naissance,
        ]);

        return response()->json(['message' => 'Patient enregistré avec succès', 'patient' => $patient], 201);
    }
    public function getAllPatients()
    {
        // Vérifiez si l'utilisateur est authentifié et possède un rôle autorisé
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'medecin'])) {
            return response()->json(['message' => 'Accès interdit. Seuls les administrateurs et les médecins peuvent voir la liste des patients.'], 403);
        }
    
        // Récupérer tous les patients
        $patients = \App\Models\Patient::select('id', 'nom', 'prenom', 'date_naissance')->get();
    
        return response()->json(['patients' => $patients], 200);
    }
    
    ////affiiche l'ordonnance de chaque patient 
    public function deletePatient($id)
    {
        $patient = Patient::find($id);
    
        if (!$patient) {
            return response()->json(['message' => 'Patient non trouvé'], 404);
        }
    
        // Supprimer d'abord l'utilisateur lié
        $user = $patient->user;
        if ($user) {
            $user->delete();
        }
    
        // Supprimer le patient
        $patient->delete();
    
        return response()->json(['message' => 'Patient supprimé avec succès'], 200);
    }
    

}

