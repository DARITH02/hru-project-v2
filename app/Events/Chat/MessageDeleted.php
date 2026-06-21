<?php

namespace App\Events\Chat;

use App\Models\Chat\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message, public string $mode, public int $userId)
    {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.conversation.'.$this->message->conversation_id);
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'mode' => $this->mode,
            'user_id' => $this->userId,
        ];
    }
}
