<?php

namespace App\Http\Controllers;

use App\Models\Niveau;
use Illuminate\Http\Request;

class NiveauController extends Controller
{
    public function ajouterNiveau(Request $request)
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
            'nom_niveau' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ecole_id' => 'required|exists:ecoles,id',
        ]);

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $request->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez ajouter un niveau que dans votre école.',
            ], 403);
        }

        $niveauExiste = Niveau::where('nom_niveau', $request->nom_niveau)
            ->where('ecole_id', $request->ecole_id)
            ->exists();

        if ($niveauExiste) {
            return response()->json([
                'message' => 'Ce niveau existe déjà dans cette école.',
            ], 400);
        }

        $niveau = Niveau::create([
            'nom_niveau' => $request->nom_niveau,
            'description' => $request->description,
            'ecole_id' => $request->ecole_id,
        ]);

        return response()->json([
            'message' => 'Niveau ajouté avec succès',
            'data' => $niveau,
        ], 201);
    }

    public function listeNiveaux(Request $request)
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

        // Pagination (par défaut 10)
        $parPage = $request->get('par_page', 10);

        // Query de base
        $query = Niveau::with('ecole');

        // ADMIN_ECOLE → seulement ses niveaux
        if ($user->role === 'ADMIN_ECOLE') {
            $query->where('ecole_id', $user->ecole_id);
        }

        // SUPER_ADMIN peut filtrer par école
        if ($user->role === 'SUPER_ADMIN' && $request->filled('ecole_id')) {
            $query->where('ecole_id', $request->ecole_id);
        }

        // Pagination
        $niveaux = $query->orderBy('id', 'desc')->paginate($parPage);

        return response()->json([
            'message' => 'Liste des niveaux récupérée avec succès',
            'data' => $niveaux,
        ], 200);
    }

    
    public function modifierNiveau(Request $request, int $id)
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

        $niveau = Niveau::find($id);

        if (! $niveau) {
            return response()->json([
                'message' => 'Niveau introuvable',
            ], 404);
        }

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $niveau->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez modifier que les niveaux de votre école.',
            ], 403);
        }

        $request->validate([
            'nom_niveau' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($request->filled('nom_niveau')) {
            $niveauExiste = Niveau::where('nom_niveau', $request->nom_niveau)
                ->where('ecole_id', $niveau->ecole_id)
                ->where('id', '!=', $niveau->id)
                ->exists();

            if ($niveauExiste) {
                return response()->json([
                    'message' => 'Ce niveau existe déjà dans cette école.',
                ], 400);
            }
        }

        $niveau->update([
            'nom_niveau' => $request->nom_niveau ?? $niveau->nom_niveau,
            'description' => $request->has('description') ? $request->description : $niveau->description,
        ]);

        return response()->json([
            'message' => 'Niveau modifié avec succès',
            'data' => $niveau,
        ], 200);
    }



    public function supprimerNiveau(Request $request, int $id)
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

        $niveau = Niveau::withCount(['classes', 'matieres'])->find($id);

        if (! $niveau) {
            return response()->json([
                'message' => 'Niveau introuvable',
            ], 404);
        }

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $niveau->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez supprimer que les niveaux de votre école.',
            ], 403);
        }

        if ($niveau->classes_count > 0 || $niveau->matieres_count > 0) {
            return response()->json([
                'message' => 'Suppression impossible. Ce niveau contient déjà des classes ou des matières.',
            ], 400);
        }

        $niveau->delete();

        return response()->json([
            'message' => 'Niveau supprimé avec succès',
        ], 200);
    }


}
