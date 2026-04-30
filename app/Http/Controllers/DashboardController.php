<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ecole;
use App\Models\Eleves;
use App\Models\Classes;
use App\Models\Inscriptions;
use App\Models\Paiements_mensuels;
use App\Models\Depenses;
use App\Models\Notes;
use App\Models\Bulletins;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function activitesRecentes(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        if ($user->role !== 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'Accès refusé. Seul le SUPER_ADMIN peut voir les activités globales.'
            ], 403);
        }

        $limit = $request->get('limit', 20);

        $activites = collect();

        User::with('ecole')
            ->latest()
            ->take(10)
            ->get()
            ->each(function ($item) use ($activites) {
                $activites->push([
                    'type' => 'UTILISATEUR',
                    'message' => 'Nouvel utilisateur ajouté : ' . $item->name,
                    'date' => $item->created_at,
                    'data' => $item,
                ]);
            });

        Ecole::latest()
            ->take(10)
            ->get()
            ->each(function ($item) use ($activites) {
                $activites->push([
                    'type' => 'ECOLE',
                    'message' => 'Nouvelle école créée : ' . $item->nom_ecole,
                    'date' => $item->created_at,
                    'data' => $item,
                ]);
            });

        Eleves::with('ecole')
            ->latest()
            ->take(10)
            ->get()
            ->each(function ($item) use ($activites) {
                $activites->push([
                    'type' => 'ELEVE',
                    'message' => 'Nouvel élève inscrit : ' . $item->prenom . ' ' . $item->nom,
                    'date' => $item->created_at,
                    'data' => $item,
                ]);
            });

        Inscriptions::with(['eleve', 'classe', 'ecole'])
            ->latest()
            ->take(10)
            ->get()
            ->each(function ($item) use ($activites) {
                $activites->push([
                    'type' => 'INSCRIPTION',
                    'message' => 'Nouvelle inscription enregistrée',
                    'date' => $item->created_at,
                    'data' => $item,
                ]);
            });

        Paiements_mensuels::with(['eleve', 'ecole'])
            ->latest()
            ->take(10)
            ->get()
            ->each(function ($item) use ($activites) {
                $activites->push([
                    'type' => 'PAIEMENT',
                    'message' => 'Paiement mensuel enregistré : ' . $item->montant_paye,
                    'date' => $item->created_at,
                    'data' => $item,
                ]);
            });

        Depenses::with('ecole')
            ->latest()
            ->take(10)
            ->get()
            ->each(function ($item) use ($activites) {
                $activites->push([
                    'type' => 'DEPENSE',
                    'message' => 'Nouvelle dépense enregistrée : ' . $item->montant,
                    'date' => $item->created_at,
                    'data' => $item,
                ]);
            });

        Notes::with(['eleve', 'matiere'])
            ->latest()
            ->take(10)
            ->get()
            ->each(function ($item) use ($activites) {
                $activites->push([
                    'type' => 'NOTE',
                    'message' => 'Nouvelle note ajoutée',
                    'date' => $item->created_at,
                    'data' => $item,
                ]);
            });

        Bulletins::with(['eleve', 'ecole'])
            ->latest()
            ->take(10)
            ->get()
            ->each(function ($item) use ($activites) {
                $activites->push([
                    'type' => 'BULLETIN',
                    'message' => 'Bulletin généré',
                    'date' => $item->created_at,
                    'data' => $item,
                ]);
            });

        $activites = $activites
            ->sortByDesc('date')
            ->take($limit)
            ->values();

        return response()->json([
            'message' => 'Activités récentes récupérées avec succès',
            'data' => $activites
        ], 200);
    }


}
