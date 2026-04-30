<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Niveau;

class ClasseController extends Controller
{
    public function ajouterClasse(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        if (!in_array($user->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé'
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
                'message' => 'Accès refusé. Vous ne pouvez ajouter une classe que dans votre école.'
            ], 403);
        }

        $niveau = Niveau::find($request->niveau_id);

        if ($niveau->ecole_id != $request->ecole_id) {
            return response()->json([
                'message' => 'Ce niveau n’appartient pas à cette école.'
            ], 400);
        }

        $classeExiste = Classes::where('nom_classe', $request->nom_classe)
            ->where('ecole_id', $request->ecole_id)
            ->where('niveau_id', $request->niveau_id)
            ->exists();

        if ($classeExiste) {
            return response()->json([
                'message' => 'Cette classe existe déjà pour ce niveau dans cette école.'
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
            'data' => $classe
        ], 201);
    }



    public function listeClasses(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        if (!in_array($user->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé'
            ], 403);
        }

        $parPage = $request->get('par_page', 10);

        $query = Classes::with([
            'ecole:id,nom_ecole,code_ecole',
            'niveau:id,nom_niveau,ecole_id'
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
            'data' => $classes
        ], 200);
    }

    

}

