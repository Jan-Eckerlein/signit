<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SignatureImageProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $signId;
    public string $imagePath;

    /**
     * Create a new event instance.
     */
    public function __construct(int $signId, string $imagePath)
    {
        $this->signId = $signId;
        $this->imagePath = $imagePath;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\PrivateChannel|\Illuminate\Broadcasting\PresenceChannel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('sign-' . $this->signId)];
    }

    /**
     * @return array<string, int|string>
     */
    public function broadcastWith(): array
    {
        return [
            'sign_id' => $this->signId,
            'image_path' => $this->imagePath,
        ];
    }
}
