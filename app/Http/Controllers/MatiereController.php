<?php

namespace App\Http\Controllers;

use App\Models\Matieres;
use App\Models\Niveau;
use Illuminate\Http\Request;
use App\Models\Classes;

class MatiereController extends Controller
{
    private function generateCodeMatiere()
    {
        do {
            $code = 'MAT-'.strtoupper(substr(md5(uniqid()), 0, 6));
        } while (Matieres::where('code_matiere', $code)->exists());

        return $code;
    }

    public function ajouterMatiere(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        if (! in_array($user->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $request->validate([
            'nom_matiere' => 'required|string|max:255',
            'coefficient' => 'required|numeric|min:0',
            'niveau_id' => 'required|exists:niveaux,id',
            'ecole_id' => 'required|exists:ecoles,id',
            'classe_id' => 'required|exists:classes,id',
        ]);

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $request->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez ajouter une matière que dans votre école.',
            ], 403);
        }

        $niveau = Niveau::find($request->niveau_id);

        if ($niveau->ecole_id != $request->ecole_id) {
            return response()->json([
                'message' => 'Ce niveau n’appartient pas à cette école.',
            ], 400);
        }

        $matiereExiste = Matieres::where('nom_matiere', $request->nom_matiere)
            ->where('niveau_id', $request->niveau_id)
            ->where('ecole_id', $request->ecole_id)
            ->exists();

        if ($matiereExiste) {
            return response()->json([
                'message' => 'Cette matière existe déjà pour ce niveau dans cette école.',
            ], 400);
        }

        $classe = Classes::find($request->classe_id);

        if ($classe->ecole_id != $request->ecole_id) {
            return response()->json([
                'message' => 'Cette classe n’appartient pas à cette école.',
            ], 400);
        }

        if ($classe->niveau_id != $request->niveau_id) {
            return response()->json([
                'message' => 'Cette classe n’appartient pas à ce niveau.',
            ], 400);
        }

        $matiere = Matieres::create([
            'nom_matiere' => $request->nom_matiere,
            'code_matiere' => $this->generateCodeMatiere(),
            'coefficient' => $request->coefficient,
            'niveau_id' => $request->niveau_id,
            'ecole_id' => $request->ecole_id,
            'classe_id' => $request->classe_id,
        ]);

        return response()->json([
            'message' => 'Matière ajoutée avec succès',
            'data' => $matiere->load(['niveau', 'ecole']),
        ], 201);
    }

    public function listeMatieres(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        if (! in_array($user->role, ['SUPER_ADMIN', 'ADMIN_ECOLE', 'PROFESSEUR'])) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $parPage = $request->get('par_page', 10);

        $query = Matieres::with([
            'ecole:id,nom_ecole,code_ecole',
            'niveau:id,nom_niveau,ecole_id',
        ]);

        if ($user->role !== 'SUPER_ADMIN') {
            $query->where('ecole_id', $user->ecole_id);
        }

        if ($user->role === 'SUPER_ADMIN' && $request->filled('ecole_id')) {
            $query->where('ecole_id', $request->ecole_id);
        }

        if ($request->filled('niveau_id')) {
            $query->where('niveau_id', $request->niveau_id);
        }

        $matieres = $query->orderBy('id', 'desc')->paginate($parPage);

        return response()->json([
            'message' => 'Liste des matières récupérée avec succès',
            'data' => $matieres,
        ], 200);
    }

    public function modifierMatiere(Request $request, int $id)
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

        $matiere = Matieres::find($id);

        if (! $matiere) {
            return response()->json([
                'message' => 'Matière introuvable',
            ], 404);
        }

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $matiere->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez modifier que les matières de votre école.',
            ], 403);
        }

        $request->validate([
            'nom_matiere' => 'sometimes|string|max:255',
            'coefficient' => 'sometimes|numeric|min:0',
            'niveau_id' => 'sometimes|exists:niveaux,id',
        ]);

        $niveauId = $request->niveau_id ?? $matiere->niveau_id;

        if ($request->filled('niveau_id')) {
            $niveau = Niveau::find($request->niveau_id);

            if ($niveau->ecole_id != $matiere->ecole_id) {
                return response()->json([
                    'message' => 'Ce niveau n’appartient pas à cette école.',
                ], 400);
            }
        }

        $nomMatiere = $request->nom_matiere ?? $matiere->nom_matiere;

        $matiereExiste = Matieres::where('nom_matiere', $nomMatiere)
            ->where('niveau_id', $niveauId)
            ->where('ecole_id', $matiere->ecole_id)
            ->where('id', '!=', $matiere->id)
            ->exists();

        if ($matiereExiste) {
            return response()->json([
                'message' => 'Cette matière existe déjà pour ce niveau dans cette école.',
            ], 400);
        }

        $matiere->update([
            'nom_matiere' => $nomMatiere,
            'coefficient' => $request->coefficient ?? $matiere->coefficient,
            'niveau_id' => $niveauId,
        ]);

        return response()->json([
            'message' => 'Matière modifiée avec succès',
            'data' => $matiere->load(['niveau', 'ecole']),
        ], 200);
    }

    public function supprimerMatiere(Request $request, int $id)
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

        $matiere = Matieres::withCount('notes')->find($id);

        if (! $matiere) {
            return response()->json([
                'message' => 'Matière introuvable',
            ], 404);
        }

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $matiere->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez supprimer que les matières de votre école.',
            ], 403);
        }

        if ($matiere->notes_count > 0) {
            return response()->json([
                'message' => 'Suppression impossible. Cette matière contient déjà des notes.',
            ], 400);
        }

        $matiere->delete();

        return response()->json([
            'message' => 'Matière supprimée avec succès',
        ], 200);
    }
}
