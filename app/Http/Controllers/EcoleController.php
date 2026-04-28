<?php

namespace App\Http\Controllers;

use App\Models\Ecole;
use Illuminate\Http\Request;

class EcoleController extends Controller
{
    private function generateCodeEcole()
    {
        do {
            // Exemple : ECL + 5 chiffres aléatoires
            $code = 'ECL'.strtoupper(substr(uniqid(), -5));

            // Vérifier unicité
        } while (Ecole::where('code_ecole', $code)->exists());

        return $code;
    }

    public function ajouterEcole(Request $request)
    {
        // Vérifier si connecté
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        // Vérifier le rôle
        if ($user->role !== 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Accès refusé. Seul le SUPER_ADMIN peut ajouter une école.',
            ], 403);
        }

        // Validation (sans code_ecole)
        $request->validate([
            'nom_ecole' => 'required|string|max:255',
            'type_ecole' => 'required|in:PRIMAIRE,COLLEGE,PRIMAIRE_COLLEGE',
            'adresse' => 'required|string',
            'telephone' => 'required|string',
            'email' => 'required|email',
            'annee_academique_courante' => 'required|string',
            'statut' => 'in:ACTIVE,SUSPENDUE',
        ]);

        // Générer le code école
        $codeEcole = $this->generateCodeEcole();

        // Création
        $ecole = Ecole::create([
            'nom_ecole' => $request->nom_ecole,
            'code_ecole' => $codeEcole,
            'type_ecole' => $request->type_ecole,
            'adresse' => $request->adresse,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'logo' => $request->logo ?? null,
            'annee_academique_courante' => $request->annee_academique_courante,
            'statut' => $request->statut ?? 'ACTIVE',
        ]);

        return response()->json([
            'message' => 'Ecole créée avec succès',
            'data' => $ecole,
        ], 201);
    }

    public function listeEcoles(Request $request)
    {
        $parPage = $request->get('par_page', 10);

        $ecoles = Ecole::with([
            'users',
            'niveaux',
            'classes',
            'eleves',
            'inscriptions',
        ])
            ->orderBy('id', 'desc')
            ->paginate($parPage);

        return response()->json([
            'message' => 'Liste des écoles récupérée avec succès',
            'data' => $ecoles,
        ], 200);
    }

    public function modifierEcole(Request $request, $id)
    {
        // Vérifier utilisateur connecté
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        // Vérifier rôle
        if ($user->role !== 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Accès refusé. Seul le SUPER_ADMIN peut modifier une école.',
            ], 403);
        }

        // Vérifier si l'école existe
        $ecole = Ecole::find($id);

        if (! $ecole) {
            return response()->json([
                'message' => 'Ecole introuvable',
            ], 404);
        }

        // Validation
        $request->validate([
            'nom_ecole' => 'sometimes|string|max:255',
            'type_ecole' => 'sometimes|in:PRIMAIRE,COLLEGE,PRIMAIRE_COLLEGE',
            'adresse' => 'sometimes|string',
            'telephone' => 'sometimes|string',
            'email' => 'sometimes|email',
            'annee_academique_courante' => 'sometimes|string',
            'statut' => 'sometimes|in:ACTIVE,SUSPENDUE',
        ]);

        // Mise à jour
        $ecole->update([
            'nom_ecole' => $request->nom_ecole ?? $ecole->nom_ecole,
            'type_ecole' => $request->type_ecole ?? $ecole->type_ecole,
            'adresse' => $request->adresse ?? $ecole->adresse,
            'telephone' => $request->telephone ?? $ecole->telephone,
            'email' => $request->email ?? $ecole->email,
            'logo' => $request->logo ?? $ecole->logo,
            'annee_academique_courante' => $request->annee_academique_courante ?? $ecole->annee_academique_courante,
            'statut' => $request->statut ?? $ecole->statut,
        ]);

        return response()->json([
            'message' => 'Ecole modifiée avec succès',
            'data' => $ecole,
        ], 200);
    }

    public function changerStatutEcole(Request $request, $id)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        if ($user->role !== 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Accès refusé. Seul le SUPER_ADMIN peut activer ou désactiver une école.',
            ], 403);
        }

        $ecole = Ecole::find($id);

        if (! $ecole) {
            return response()->json([
                'message' => 'Ecole introuvable',
            ], 404);
        }

        $request->validate([
            'statut' => 'required|in:ACTIVE,SUSPENDUE',
        ]);

        $ecole->statut = $request->statut;
        $ecole->save();

        return response()->json([
            'message' => $request->statut === 'ACTIVE'
                ? 'Ecole activée avec succès'
                : 'Ecole désactivée avec succès',
            'data' => $ecole,
        ], 200);
    }

    public function supprimerEcole(Request $request, $id)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        if ($user->role !== 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Accès refusé. Seul le SUPER_ADMIN peut supprimer une école.',
            ], 403);
        }

        $ecole = Ecole::find($id);

        if (! $ecole) {
            return response()->json([
                'message' => 'Ecole introuvable',
            ], 404);
        }

        $ecole->delete();

        return response()->json([
            'message' => 'Ecole supprimée avec succès',
        ], 200);
    }
}
