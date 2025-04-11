<?php

namespace App\Events;

use App\Models\Answer;
use App\Models\Observation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnswerSolved implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Answer $answer;
    public Observation $observation;

    /**
     * Create a new event instance.
     */
    public function __construct(Answer $answer,Observation $observation)
    {
        $this->answer = $answer;
        $this->observation = $observation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
