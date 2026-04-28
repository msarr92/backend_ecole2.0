<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bulletins extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'ecole_id',
        'periode',
        'annee_scolaire',
        'moyenne_generale',
        'moyenne_classe',
        'rang',
        'effectif_classe',
        'appreciation',
        'date_generation',
    ];

    protected function casts(): array
    {
        return [
            'date_generation' => 'date',
        ];
    }

    public function eleve()
    {
        return $this->belongsTo(Eleves::class);
    }
}
