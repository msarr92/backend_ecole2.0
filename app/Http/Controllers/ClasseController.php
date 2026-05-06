<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Niveau;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    public function ajouterClasse(Request $request)
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

        $request->validate([
            'nom_classe' => 'required|string|max:255',
            'niveau_id' => 'required|exists:niveaux,id',
            'ecole_id' => 'required|exists:ecoles,id',
            'montant_inscription' => 'required|numeric|min:0',
            'montant_mensuel' => 'required|numeric|min:0',
        ]);

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $request->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez ajouter une classe que dans votre école.',
            ], 403);
        }

        $niveau = Niveau::find($request->niveau_id);

        if ($niveau->ecole_id != $request->ecole_id) {
            return response()->json([
                'message' => 'Ce niveau n’appartient pas à cette école.',
            ], 400);
        }

        $classeExiste = Classes::where('nom_classe', $request->nom_classe)
            ->where('ecole_id', $request->ecole_id)
            ->where('niveau_id', $request->niveau_id)
            ->exists();

        if ($classeExiste) {
            return response()->json([
                'message' => 'Cette classe existe déjà pour ce niveau dans cette école.',
            ], 400);
        }

        $classe = Classes::create([
            'nom_classe' => $request->nom_classe,
            'niveau_id' => $request->niveau_id,
            'ecole_id' => $request->ecole_id,
            'effectif' => 0,
            'montant_inscription' => $request->montant_inscription,
            'montant_mensuel' => $request->montant_mensuel,
        ]);

        return response()->json([
            'message' => 'Classe ajoutée avec succès',
            'data' => $classe,
        ], 201);
    }

    public function listeClasses(Request $request)
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

        $parPage = $request->get('par_page', 10);

        $query = Classes::with([
            'ecole:id,nom_ecole,code_ecole',
            'niveau:id,nom_niveau,ecole_id',
        ]);

        if ($user->role === 'ADMIN_ECOLE') {
            $query->where('ecole_id', $user->ecole_id);
        }

        if ($user->role === 'SUPER_ADMIN' && $request->filled('ecole_id')) {
            $query->where('ecole_id', $request->ecole_id);
        }

        if ($request->filled('niveau_id')) {
            $query->where('niveau_id', $request->niveau_id);
        }

        $classes = $query->orderBy('id', 'desc')->paginate($parPage);

        return response()->json([
            'message' => 'Liste des classes récupérée avec succès',
            'data' => $classes,
        ], 200);
    }

    public function modifierClasse(Request $request, int $id)
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

        $classe = Classes::find($id);

        if (! $classe) {
            return response()->json([
                'message' => 'Classe introuvable',
            ], 404);
        }

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $classe->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez modifier que les classes de votre école.',
            ], 403);
        }

        $request->validate([
            'nom_classe' => 'sometimes|string|max:255',
            'niveau_id' => 'sometimes|exists:niveaux,id',
            'montant_inscription' => 'sometimes|numeric|min:0',
            'montant_mensuel' => 'sometimes|numeric|min:0',
        ]);

        if ($request->filled('niveau_id')) {
            $niveau = Niveau::find($request->niveau_id);

            if ($niveau->ecole_id != $classe->ecole_id) {
                return response()->json([
                    'message' => 'Ce niveau n’appartient pas à cette école.',
                ], 400);
            }
        }

        $nomClasse = $request->nom_classe ?? $classe->nom_classe;
        $niveauId = $request->niveau_id ?? $classe->niveau_id;

        $classeExiste = Classes::where('nom_classe', $nomClasse)
            ->where('niveau_id', $niveauId)
            ->where('ecole_id', $classe->ecole_id)
            ->where('id', '!=', $classe->id)
            ->exists();

        if ($classeExiste) {
            return response()->json([
                'message' => 'Cette classe existe déjà pour ce niveau dans cette école.',
            ], 400);
        }

        $classe->update([
            'nom_classe' => $nomClasse,
            'niveau_id' => $niveauId,
            'montant_inscription' => $request->montant_inscription ?? $classe->montant_inscription,
            'montant_mensuel' => $request->montant_mensuel ?? $classe->montant_mensuel,
        ]);

        return response()->json([
            'message' => 'Classe modifiée avec succès',
            'data' => $classe->load(['niveau', 'ecole']),
        ], 200);
    }

    public function supprimerClasse(Request $request, int $id)
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

        $classe = Classes::withCount(['eleves', 'inscriptions'])->find($id);

        if (! $classe) {
            return response()->json([
                'message' => 'Classe introuvable',
            ], 404);
        }

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $classe->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez supprimer que les classes de votre école.',
            ], 403);
        }

        if ($classe->eleves_count > 0 || $classe->inscriptions_count > 0) {
            return response()->json([
                'message' => 'Suppression impossible. Cette classe contient déjà des élèves ou des inscriptions.',
            ], 400);
        }

        $classe->delete();

        return response()->json([
            'message' => 'Classe supprimée avec succès',
        ], 200);
    }
    

    public function detailClasse(Request $request, int $id)
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

        // Récupérer la classe avec relations
        $classe = Classes::with([
            'niveau:id,nom_niveau',
            'ecole:id,nom_ecole,code_ecole',
            'eleves:id,nom,prenom,classe_id',
            'inscriptions:id,eleve_id,classe_id,annee_scolaire',
        ])->find($id);

        if (! $classe) {
            return response()->json([
                'message' => 'Classe introuvable',
            ], 404);
        }

        // ADMIN_ECOLE → accès limité à son école
        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $classe->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Cette classe ne vous appartient pas.',
            ], 403);
        }

        return response()->json([
            'message' => 'Détails de la classe récupérés avec succès',
            'data' => $classe,
        ], 200);
    }


}
