<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function ajouterUtilisateur(Request $request)
    {
        // Vérifier utilisateur connecté
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        // Vérifier rôle SUPER_ADMIN
        if ($user->role !== 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Accès refusé. Seul le SUPER_ADMIN peut ajouter un utilisateur.',
            ], 403);
        }

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:SUPER_ADMIN,ADMIN_ECOLE,COMPTABLE,SURVEILLANT,PROFESSEUR',
            'ecole_id' => 'nullable|exists:ecoles,id',
            'telephone' => 'nullable|string|max:20',
        ]);

        // Création utilisateur
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'ecole_id' => $request->ecole_id,
            'telephone' => $request->telephone,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'data' => $newUser,
        ], 201);
    }

    public function listeUtilisateurs(Request $request)
    {
        $user = $request->user();

        // Vérifier authentification
        if (! $user) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        // Vérifier rôle autorisé
        if (! in_array($user->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        // Pagination (par défaut 10)
        $parPage = $request->get('par_page', 10);

        // Query de base
        $query = User::with('ecole');

        // Si ADMIN_ECOLE → seulement ses utilisateurs
        if ($user->role === 'ADMIN_ECOLE') {
            $query->where('ecole_id', $user->ecole_id);
        }

        // Filtre par rôle (optionnel)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtre par école (SUPER_ADMIN seulement)
        if ($user->role === 'SUPER_ADMIN' && $request->filled('ecole_id')) {
            $query->where('ecole_id', $request->ecole_id);
        }

        // Pagination
        $users = $query->orderBy('id', 'desc')->paginate($parPage);

        return response()->json([
            'message' => 'Liste des utilisateurs récupérée avec succès',
            'data' => $users,
        ], 200);
    }

    public function modifierUtilisateur(Request $request, int $id)
    {
        $userConnecte = $request->user();

        if (! $userConnecte) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        if (! in_array($userConnecte->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        $utilisateur = User::find($id);

        if (! $utilisateur) {
            return response()->json([
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // ADMIN_ECOLE ne peut modifier que les utilisateurs de son école
        if ($userConnecte->role === 'ADMIN_ECOLE') {
            if ($utilisateur->ecole_id !== $userConnecte->ecole_id) {
                return response()->json([
                    'message' => 'Accès refusé. Vous ne pouvez modifier que les utilisateurs de votre école.',
                ], 403);
            }

            // ADMIN_ECOLE ne peut pas modifier un SUPER_ADMIN
            if ($utilisateur->role === 'SUPER_ADMIN') {
                return response()->json([
                    'message' => 'Accès refusé. Vous ne pouvez pas modifier un SUPER_ADMIN.',
                ], 403);
            }
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$utilisateur->id,
            'password' => 'sometimes|string|min:6|confirmed',
            'role' => 'sometimes|in:SUPER_ADMIN,ADMIN_ECOLE,COMPTABLE,SURVEILLANT,PROFESSEUR',
            'ecole_id' => 'nullable|exists:ecoles,id',
            'telephone' => 'nullable|string|max:20',
        ]);

        // ADMIN_ECOLE ne peut pas donner le rôle SUPER_ADMIN
        if ($userConnecte->role === 'ADMIN_ECOLE' && $request->role === 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Accès refusé. Vous ne pouvez pas attribuer le rôle SUPER_ADMIN.',
            ], 403);
        }

        // SUPER_ADMIN peut modifier tous les champs
        $utilisateur->name = $request->name ?? $utilisateur->name;
        $utilisateur->email = $request->email ?? $utilisateur->email;
        $utilisateur->role = $request->role ?? $utilisateur->role;
        $utilisateur->ecole_id = $request->has('ecole_id') ? $request->ecole_id : $utilisateur->ecole_id;
        $utilisateur->telephone = $request->telephone ?? $utilisateur->telephone;

        if ($request->filled('password')) {
            $utilisateur->password = Hash::make($request->password);
        }

        $utilisateur->save();

        return response()->json([
            'message' => 'Utilisateur modifié avec succès',
            'data' => $utilisateur,
        ], 200);
    }

    public function supprimerUtilisateur(Request $request, int $id)
    {
        $userConnecte = $request->user();

        if (! $userConnecte) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        if (! in_array($userConnecte->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        $utilisateur = User::find($id);

        if (! $utilisateur) {
            return response()->json([
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        if ($userConnecte->id === $utilisateur->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 400);
        }

        if ($userConnecte->role === 'ADMIN_ECOLE') {
            if ($utilisateur->ecole_id !== $userConnecte->ecole_id) {
                return response()->json([
                    'message' => 'Accès refusé. Vous ne pouvez supprimer que les utilisateurs de votre école.',
                ], 403);
            }

            if ($utilisateur->role === 'SUPER_ADMIN') {
                return response()->json([
                    'message' => 'Accès refusé. Vous ne pouvez pas supprimer un SUPER_ADMIN.',
                ], 403);
            }
        }

        $utilisateur->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès',
        ], 200);
    }

    public function changerStatutUtilisateur(Request $request, int $id)
    {
        $userConnecte = $request->user();

        if (! $userConnecte) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        if (! in_array($userConnecte->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        $utilisateur = User::find($id);

        if (! $utilisateur) {
            return response()->json([
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // ADMIN_ECOLE ne peut gérer que son école
        if ($userConnecte->role === 'ADMIN_ECOLE') {
            if ($utilisateur->ecole_id !== $userConnecte->ecole_id) {
                return response()->json([
                    'message' => 'Accès refusé',
                ], 403);
            }

            if ($utilisateur->role === 'SUPER_ADMIN') {
                return response()->json([
                    'message' => 'Impossible de modifier un SUPER_ADMIN',
                ], 403);
            }
        }

        // empêcher de se désactiver soi-même
        if ($userConnecte->id === $utilisateur->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas modifier votre propre statut',
            ], 400);
        }

        // validation
        $request->validate([
            'statut' => 'required|in:ACTIF,INACTIF',
        ]);

        $utilisateur->statut = $request->statut;
        $utilisateur->save();

        return response()->json([
            'message' => $request->statut === 'ACTIF'
                ? 'Utilisateur activé avec succès'
                : 'Utilisateur désactivé avec succès',
            'data' => $utilisateur,
        ], 200);
    }

    public function detailUtilisateur(Request $request, int $id)
    {
        $userConnecte = $request->user();

        // Vérifier authentification
        if (! $userConnecte) {
            return response()->json([
                'message' => 'Non authentifié',
            ], 401);
        }

        // Vérifier rôle autorisé
        if (! in_array($userConnecte->role, ['SUPER_ADMIN', 'ADMIN_ECOLE'])) {
            return response()->json([
                'message' => 'Accès refusé',
            ], 403);
        }

        // Récupérer utilisateur avec relation ecole
        $utilisateur = User::with('ecole')->find($id);

        if (! $utilisateur) {
            return response()->json([
                'message' => 'Utilisateur introuvable',
            ], 404);
        }

        // ADMIN_ECOLE → seulement ses utilisateurs
        if ($userConnecte->role === 'ADMIN_ECOLE') {
            if ($utilisateur->ecole_id !== $userConnecte->ecole_id) {
                return response()->json([
                    'message' => 'Accès refusé. Cet utilisateur ne fait pas partie de votre école.',
                ], 403);
            }
        }

        return response()->json([
            'message' => 'Détails de l’utilisateur récupérés avec succès',
            'data' => $utilisateur,
        ], 200);
    }





}
