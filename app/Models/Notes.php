<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notes extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'matiere_id',
        'valeur_note',
        'note_max',
        'type_evaluation',
        'periode',
        'annee_scolaire',
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleves::class);
    }

    public function matiere()
    {
        return $this->belongsTo(Matieres::class);
    }
}
