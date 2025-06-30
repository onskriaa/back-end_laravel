<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Valider les champs de connexion
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Essayer d'authentifier l'utilisateur avec les informations fournies
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        // Retourner le token avec les informations de l'utilisateur
        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => Auth::guard('api')->user(),
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'patient',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'Patient registered successfully', 'token' => $token]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logged out successfully']);
    }
    public function profile(Request $request)
    {
        // Retourner les informations de l'utilisateur connecté
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }
}
