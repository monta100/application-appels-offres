<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $primaryKey = 'idMessage'; // car tu as utilisé idMessage
    protected $fillable = ['sender_id', 'receiver_id', 'content', 'seen','file_path'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'idUser');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'idUser');
    }
}
