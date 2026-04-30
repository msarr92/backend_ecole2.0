<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EcoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NiveauController;
use App\Http\Controllers\ClasseController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


Route::middleware('auth:api')->group(function () {
    Route::get('/activities/recent', [DashboardController::class, 'activitesRecentes']);
});


Route::middleware('auth:api')->group(function () {
    Route::post('/ecoles', [EcoleController::class, 'ajouterEcole']);
    Route::get('/ecoles', [EcoleController::class, 'listeEcoles']);
    Route::put('/ecoles/{id}', [EcoleController::class, 'modifierEcole']);
    Route::patch('/ecoles/{id}/statut', [EcoleController::class, 'changerStatutEcole']);
    Route::delete('/ecoles/{id}', [EcoleController::class, 'supprimerEcole']);
    Route::get('/ecoles/{id}', [EcoleController::class, 'detailEcole']);
    Route::get('/ecoles/{id}/stats', [EcoleController::class, 'statsEcole']);
});


Route::middleware('auth:api')->group(function () {
    Route::post('/users', [UserController::class, 'ajouterUtilisateur']);
    Route::get('/users', [UserController::class, 'listeUtilisateurs']);
    Route::put('/users/{id}', [UserController::class, 'modifierUtilisateur']);
    Route::delete('/users/{id}', [UserController::class, 'supprimerUtilisateur']);
    Route::patch('/users/{id}/statut', [UserController::class, 'changerStatutUtilisateur']);
    Route::get('/users/{id}', [UserController::class, 'detailUtilisateur']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/niveaux', [NiveauController::class, 'ajouterNiveau']);
    Route::get('/niveaux', [NiveauController::class, 'listeNiveaux']);
    Route::put('/niveaux/{id}', [NiveauController::class, 'modifierNiveau']);
    Route::delete('/niveaux/{id}', [NiveauController::class, 'supprimerNiveau']);
});



Route::middleware('auth:api')->group(function () {
    Route::post('/classes', [ClasseController::class, 'ajouterClasse']);
    Route::get('/classes', [ClasseController::class, 'listeClasses']);
});
