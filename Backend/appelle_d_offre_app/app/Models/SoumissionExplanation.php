<?php
// app/Models/SoumissionExplanation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoumissionExplanation extends Model
{
    protected $table = 'soumission_explanations';
    protected $fillable = ['soumission_id','verdict','categories','public_phrase'];
    protected $casts = [
        'categories' => 'array',
    ];

    public function soumission() {
        return $this->belongsTo(Soumission::class, 'soumission_id');
    }
}
