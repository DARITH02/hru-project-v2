<?php

namespace App\Events\Chat;

use App\Models\Chat\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        $this->message->loadMissing(['sender:id,name,role', 'attachments', 'reactions.user:id,name', 'replyTo.sender:id,name,role']);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.conversation.'.$this->message->conversation_id);
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message->toArray()];
    }
}
