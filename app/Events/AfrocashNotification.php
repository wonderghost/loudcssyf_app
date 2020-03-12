<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Notifications;

class AfrocashNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $user;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(\App\Notifications $n,\App\User $u)
    {
        //
      $this->notification = $n;
      $this->user = $u;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('notification');
    }
}
