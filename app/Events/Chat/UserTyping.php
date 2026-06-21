<?php

namespace App\Events\Chat;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $conversationId, public User $user, public bool $typing)
    {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.conversation.'.$this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'user.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'user' => $this->user->only(['id', 'name', 'role']),
            'typing' => $this->typing,
        ];
    }
}
