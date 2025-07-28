<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class CallAccepted implements ShouldBroadcast
{
    use SerializesModels;

    public $callerId;
    public $receiverId;

    public function __construct($callerId, $receiverId)
    {
        $this->callerId = $callerId;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new Channel('video-call.' . $this->callerId);
    }

    public function broadcastWith()
    {
        return [
            'receiverId' => $this->receiverId
        ];
    }
}
