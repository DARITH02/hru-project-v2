<?php

namespace App\Events\Chat;

use App\Models\Chat\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        $this->message->loadMissing(['reactions.user:id,name']);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.conversation.'.$this->message->conversation_id);
    }

    public function broadcastAs(): string
    {
        return 'message.reaction.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'reactions' => $this->message->reactions->toArray(),
        ];
    }
}
