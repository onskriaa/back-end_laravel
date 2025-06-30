<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\OrdonnanceController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:api'])->prefix('medicaments')->group(function () {
    Route::get('/', [MedicamentController::class, 'index']); // Récupérer tous les médicaments
    Route::post('/', [MedicamentController::class, 'store']); // Créer un médicament (seulement admin)
    Route::get('/{id}', [MedicamentController::class, 'show']); // Récupérer un médicament
    Route::put('/{id}', [MedicamentController::class, 'update']); // Mettre à jour un médicament (seulement admin)
    Route::delete('/{id}', [MedicamentController::class, 'destroy']); // Supprimer un médicament (seulement admin)
});


// Routes pour les médecins
Route::middleware(['auth:api'])->prefix('medecins')->group(function () {
Route::post('/', [MedecinController::class, 'store']);
Route::get('/', [MedecinController::class, 'index']);
Route::get('/{id}', [MedecinController::class, 'show']);
Route::delete('{id}', [MedecinController::class, 'destroy']);
Route::put('/{id}', [MedecinController::class, 'update']);
Route::get('/patients', [PatientController::class, 'getAllPatients']);



});

// Routes pour les patients
Route::post('/register', [PatientController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/profile', [AuthController::class, 'profile']);
Route::post('/logout', [AuthController::class, 'logout']);

//
Route::middleware(['auth:api'])->group(function () {
    Route::get('/patients', [PatientController::class, 'getAllPatients']); // Accessible aux médecins et administrateurs
});



Route::middleware(['auth:api'])->group(function () {
    Route::post('/ordonnances', [OrdonnanceController::class, 'store']); // Créer une ordonnance
    Route::get('/ordonnances', [OrdonnanceController::class, 'index']); // Voir toutes les ordonnances
    Route::put('/ordonnances/{id}', [OrdonnanceController::class, 'update']);
    Route::delete('/ordonnances/{id}', [OrdonnanceController::class, 'destroy']);
    Route::get('/ordonnances', [OrdonnanceController::class, 'getOrdonnancesByMedecin']);
    Route::get('/patient/ordonnances', [OrdonnanceController::class, 'getOrdonnancesForPatient']);

  
Route::middleware('auth:api')->group(function () {
        Route::get('/admin/patients', [PatientController::class, 'getAllPatients'])->name('admin.patients');
        Route::get('/admin/medecins', [MedecinController::class, 'getAllMedecins'])->name('admin.medecins');
        Route::get('/admin/ordonnances', [OrdonnanceController::class, 'getAllOrdonnances'])->name('admin.ordonnances');
        Route::delete('/admin/patients/{id}', [PatientController::class, 'deletePatient'])->name('admin.patients.delete');

    });
    });
    







