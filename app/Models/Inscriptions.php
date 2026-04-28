<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inscriptions extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'classe_id',
        'ecole_id',
        'annee_scolaire',
        'date_inscription',
        'montant_inscription',
        'montant_paye',
        'statut_paiement',
        'mode_paiement',
        'numero_recu',
    ];

    protected function casts(): array
    {
        return [
            'date_inscription' => 'date',
        ];
    }

    public function eleve()
    {
        return $this->belongsTo(Eleves::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classes::class);
    }

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiements_mensuels::class);
    }
}
