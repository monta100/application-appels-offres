<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class soumission extends Model
{
    use HasFactory;

        protected $primaryKey = 'idSoumission';
  public $timestamps = true;
    protected $fillable = [
        'prixPropose',
        'description',
        'temps_realisation',
        'score_ia',
        'fichier_joint',
        'idUser',
        'idAppel',
         // 🧠 Champs ajoutés pour le microservice d’anomalie
    'score_ia_anomalie',
    'verdict_ia_anomalie',
    'explication_anomalie',
    ];


    public function user()
{
    return $this->belongsTo(User::class, 'idUser');
}
public function appelOffre()
{
    return $this->belongsTo(appelle_offres::class, 'idAppel');
}

public function contrat()
{
    return $this->hasOne(Contrat::class, 'idSoumission');
}

}
