<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_classe',
        'niveau_id',
        'ecole_id',
        'effectif',
        'montant_inscription',
        'montant_mensuel',
    ];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }

    public function eleves()
    {
        return $this->hasMany(Eleves::class, 'classe_id');
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscriptions::class, 'classe_id');
    }

    public function matieres()
    {
        return $this->hasMany(Matieres::class, 'classe_id');
    }

    

}
