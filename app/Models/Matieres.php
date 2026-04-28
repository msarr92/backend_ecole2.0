<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Matieres extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_matiere',
        'code_matiere',
        'coefficient',
        'niveau_id',
        'ecole_id',
    ];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function notes()
    {
        return $this->hasMany(Notes::class);
    }
}
