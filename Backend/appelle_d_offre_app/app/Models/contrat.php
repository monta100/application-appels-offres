<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contrat extends Model
{
    use HasFactory;
    protected $primaryKey = 'idContrat';

      protected $fillable = [
        'fichierpdf',
        'date_creation',
        'idSoumission',
    ];
        protected $casts = [
        'date_creation' => 'datetime',
    ];

    public function soumission()
{
    return $this->belongsTo(Soumission::class, 'idSoumission');
}

}
