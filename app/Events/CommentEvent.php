<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment,$user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user,$comment)
    {
        $this->user = $user;
        $this->comment = $comment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('User.Comment.Facility.'.$this->comment->id_facility);
    }
    public function broadcastAs():string
    {
        return "CommentEvent";
    }
    public function broadcastWith():array
    {
        return [
            "comment" => $this->comment,
            "user" => $this->user
        ];
    }
}
