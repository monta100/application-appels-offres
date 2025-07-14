<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class  Domaine extends Model
{
    use HasFactory;
    protected $primaryKey = 'idDomaine';
      protected $fillable = [
        'nom',
    ];
    public function appelsOffres()
{
    return $this->hasMany(appelle_offres::class, 'idDomaine');
}
public function users()
{
    return $this->belongsToMany(User::class, 'domaine_user', 'idDomaine', 'idUser');
}


}
