<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Depenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecole_id',
        'date',
        'montant',
        'type_depense',
        'categorie',
        'beneficiaire',
        'mode_paiement',
        'justificatif',
        'description',
        'numero_recu',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }
}
