<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class CallRequested implements ShouldBroadcast
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
        return new Channel('video-call.' . $this->receiverId);
    }

    public function broadcastWith()
    {
        return [
            'callerId' => $this->callerId,
        ];
    }
}
