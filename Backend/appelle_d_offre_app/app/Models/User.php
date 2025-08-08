<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomResetPassword;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

     protected $primaryKey = 'idUser';

    protected $fillable = [
    'nom',
    'prenom',
    'email',
    'password',
    'telephone',
    'role',
    'nomSociete',
    'avatar',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function soumissions()
{
    return $this->hasMany(Soumission::class, 'idUser');
}

public function appelsOffres()
{
    return $this->hasMany(appelle_offres::class, 'idUser');
}
public function domaines()
{
    return $this->belongsToMany(Domaine::class, 'domaine_user', 'idUser', 'idDomaine');
}


public function messagesEnvoyes()
{
    return $this->hasMany(Message::class, 'sender_id', 'idUser');
}

public function messagesRecus()
{
    return $this->hasMany(Message::class, 'receiver_id', 'idUser');
}
public function notifications()
{
    return $this->hasMany(Notification::class, 'user_id');
}

}
