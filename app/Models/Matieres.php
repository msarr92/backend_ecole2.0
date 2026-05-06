<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matieres extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_matiere',
        'code_matiere',
        'coefficient',
        'niveau_id',
        'classe_id',
        'ecole_id',
    ];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'niveau_id');
    }

    public function notes()
    {
        return $this->hasMany(Notes::class, 'matiere_id');
    }

    public function ecole()
    {
        return $this->belongsTo(Ecole::class, 'ecole_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classes::class, 'classe_id');
    }
}
