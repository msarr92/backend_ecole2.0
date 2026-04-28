<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Niveau extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_niveau',
        'description',
        'ecole_id',
    ];

    public function ecole()
    {
        return $this->belongsTo(Ecole::class);
    }

    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    public function matieres()
    {
        return $this->hasMany(Matieres::class);
    }
}
