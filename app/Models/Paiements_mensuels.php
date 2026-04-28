<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paiements_mensuels extends Model
{
    use HasFactory;

    protected $table = 'paiements_mensuels';

    protected $fillable = [
        'eleve_id',
        'inscription_id',
        'ecole_id',
        'mois',
        'annee',
        'numero_mois',
        'montant_du',
        'montant_paye',
        'montant_restant',
        'statut',
        'date_echeance',
        'date_paiement',
        'mode_paiement',
        'numero_recu',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'date_echeance' => 'date',
            'date_paiement' => 'date',
        ];
    }

    public function eleve()
    {
        return $this->belongsTo(Eleves::class);
    }

    public function inscription()
    {
        return $this->belongsTo(Inscriptions::class);
    }
}
