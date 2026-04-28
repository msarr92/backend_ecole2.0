<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ecole extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_ecole',
        'code_ecole',
        'type_ecole',
        'adresse',
        'telephone',
        'email',
        'logo',
        'annee_academique_courante',
        'statut',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function niveaux()
    {
        return $this->hasMany(Niveau::class);
    }

    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    public function eleves()
    {
        return $this->hasMany(Eleves::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscriptions::class);
    }
}
