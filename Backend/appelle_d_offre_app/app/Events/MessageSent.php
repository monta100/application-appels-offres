<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;

class MessageSent implements ShouldBroadcast
{
    use SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender');
    }

    public function broadcastOn()
{
    return new Channel('public-chat'); // 👈 Channel public au lieu de PrivateChannel
}


    public function broadcastWith()
    {
        return [
            'idMessage' => $this->message->idMessage,
            'content' => $this->message->content,
            'file_path' => $this->message->file_path,

            'sender' => [
                'idUser' => $this->message->sender->idUser,
                'name' => $this->message->sender->NomUser
            ],
            'created_at' => $this->message->created_at->toDateTimeString()
        ];
    }
}
