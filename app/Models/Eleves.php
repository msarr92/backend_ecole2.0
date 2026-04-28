<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Eleves extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'statut',
        'classe_id',
        'ecole_id',

        // Tuteur
        'nom_tuteur',
        'prenom_tuteur',
        'telephone_tuteur',
        'telephone2_tuteur',
        'adresse_tuteur',
        'profession_tuteur',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
        ];
    }

    public function classe()
    {
        return $this->belongsTo(Classes::class);
    }

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscriptions::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiements_mensuels::class);
    }

    public function notes()
    {
        return $this->hasMany(Notes::class);
    }

    public function bulletins()
    {
        return $this->hasMany(Bulletins::class);
    }
}
