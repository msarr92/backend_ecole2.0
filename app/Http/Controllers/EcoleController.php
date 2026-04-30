<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Ecole;
use App\Models\Eleves;
use App\Models\Inscriptions;
use App\Models\Paiements_mensuels;
use App\Models\User;
use App\Models\Niveau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        if ($user->role !== 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Accès refusé. Seul le SUPER_ADMIN peut ajouter une école.',
            ], 403);
        }

        $request->validate([
            'nom_ecole' => 'required|string|max:255',
            'type_ecole' => 'required|in:PRIMAIRE,COLLEGE,PRIMAIRE_COLLEGE',
            'adresse' => 'required|string',
            'telephone' => 'required|string',
            'email' => 'required|email',
            'annee_academique_courante' => 'required|string',
            'statut' => 'nullable|in:ACTIVE,SUSPENDUE',

            'niveaux' => 'required|array|min:1',
            'niveaux.*.nom_niveau' => 'required|string|max:255',
            'niveaux.*.description' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $codeEcole = $this->generateCodeEcole();

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

            foreach ($request->niveaux as $niveauData) {
                Niveau::create([
                    'nom_niveau' => $niveauData['nom_niveau'],
                    'description' => $niveauData['description'] ?? null,
                    'ecole_id' => $ecole->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Ecole créée avec ses niveaux avec succès',
                'data' => $ecole->load('niveaux'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erreur lors de la création de l’école',
                'error' => $e->getMessage(),
            ], 500);
        }

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

    public function modifierEcole(Request $request, int $id)
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

    public function changerStatutEcole(Request $request, int $id)
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

    public function supprimerEcole(Request $request, int $id)
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

    public function detailEcole(Request $request, int $id)
    {
        $user = $request->user();

        // Vérifier authentification
        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        // Vérifier rôle
        if (! in_array($user->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        // Récupérer école avec relations
        $ecole = Ecole::with([
            'users',
            'niveaux',
            'classes',
            'eleves',
            'inscriptions',
        ])->find($id);

        if (! $ecole) {
            return response()->json([
                'message' => 'Ecole introuvable',
            ], 404);
        }

        // ADMIN_ECOLE → accès seulement à son école
        if ($user->role === 'ADMIN_ECOLE') {
            if ($ecole->id !== $user->ecole_id) {
                return response()->json([
                    'message' => 'Accès refusé. Cette école ne vous appartient pas.',
                ], 403);
            }
        }

        return response()->json([
            'message' => 'Détails de l’école récupérés avec succès',
            'data' => $ecole,
        ], 200);
    }

    public function statsEcole(Request $request, int $id)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        if (! in_array($user->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        $ecole = Ecole::find($id);

        if (! $ecole) {
            return response()->json([
                'message' => 'Ecole introuvable',
            ], 404);
        }

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id !== $ecole->id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez voir que les statistiques de votre école.',
            ], 403);
        }

        $totalEleves = Eleves::where('ecole_id', $ecole->id)->count();

        $totalEnseignants = User::where('ecole_id', $ecole->id)
            ->where('role', 'PROFESSEUR')
            ->count();

        $totalClasses = Classes::where('ecole_id', $ecole->id)->count();

        $revenuInscriptions = Inscriptions::where('ecole_id', $ecole->id)
            ->sum('montant_paye');

        $revenuPaiementsMensuels = Paiements_mensuels::where('ecole_id', $ecole->id)
            ->sum('montant_paye');

        $revenuTotal = $revenuInscriptions + $revenuPaiementsMensuels;

        return response()->json([
            'message' => 'Statistiques de l’école récupérées avec succès',
            'data' => [
                'ecole' => [
                    'id' => $ecole->id,
                    'nom_ecole' => $ecole->nom_ecole,
                    'code_ecole' => $ecole->code_ecole,
                    'statut' => $ecole->statut,
                ],
                'stats' => [
                    'total_eleves' => $totalEleves,
                    'total_enseignants' => $totalEnseignants,
                    'total_classes' => $totalClasses,
                    'revenu_inscriptions' => $revenuInscriptions,
                    'revenu_paiements_mensuels' => $revenuPaiementsMensuels,
                    'revenu_total' => $revenuTotal,
                ],
            ],
        ], 200);
    }
}
