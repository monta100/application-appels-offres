<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SignalReceived implements ShouldBroadcast
{
    use SerializesModels;

    public $from, $type, $data;

    public function __construct($from, $type, $data)
    {
        $this->from = $from;
        $this->type = $type;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('video-signal.' . request()->input('to'));
    }

    public function broadcastAs()
    {
        return 'SignalReceived';
    }

    public function broadcastWith()
    {
        return [
            'from' => $this->from,
            'type' => $this->type,
            'data' => $this->data
        ];
    }
}
