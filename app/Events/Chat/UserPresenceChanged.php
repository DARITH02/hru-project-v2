<?php

namespace App\Events\Chat;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPresenceChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public bool $online, public ?string $lastSeenAt = null)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.presence'),
            new PrivateChannel('chat.user.'.$this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.presence.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'online' => $this->online,
            'last_seen_at' => $this->lastSeenAt,
        ];
    }
}
