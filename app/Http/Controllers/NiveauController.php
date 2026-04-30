<?php

namespace App\Http\Controllers;

use App\Models\Niveau;
use Illuminate\Http\Request;

class NiveauController extends Controller
{
    
    public function ajouterNiveau(Request $request)
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
            'nom_niveau' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ecole_id' => 'required|exists:ecoles,id',
        ]);

        if ($user->role === 'ADMIN_ECOLE' && $user->ecole_id != $request->ecole_id) {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez ajouter un niveau que dans votre école.'
            ], 403);
        }

        $niveauExiste = Niveau::where('nom_niveau', $request->nom_niveau)
            ->where('ecole_id', $request->ecole_id)
            ->exists();

        if ($niveauExiste) {
            return response()->json([
                'message' => 'Ce niveau existe déjà dans cette école.'
            ], 400);
        }

        $niveau = Niveau::create([
            'nom_niveau' => $request->nom_niveau,
            'description' => $request->description,
            'ecole_id' => $request->ecole_id,
        ]);

        return response()->json([
            'message' => 'Niveau ajouté avec succès',
            'data' => $niveau
        ], 201);
    }


}
