<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class appelle_offres extends Model
{
    use HasFactory;

        protected $primaryKey = 'idAppel';

          protected $fillable = [
        'titre',
        'description',
        'budget',
        'date_limite',
        'fichier_joint',
        'statut',
        'date_publication',
        'idUser',
        'idDomaine',


    ];



       protected $casts = [
        'date_limite' => 'date',
        'date_publication' => 'datetime',
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'idUser');
}

public function soumissions()
{
    return $this->hasMany(Soumission::class, 'idAppel');
}
public function domaine()
{
    return $this->belongsTo(Domaine::class, 'idDomaine');
}


}
