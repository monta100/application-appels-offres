<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class soumission extends Model
{
    use HasFactory;

        protected $primaryKey = 'idSoumission';
    public $timestamps = false;

    protected $fillable = [
        'prixPropose',
        'description',
        'temps_realisation',
        'score_ia',
        'fichier_joint',
        'idUser',
        'idAppel',
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
