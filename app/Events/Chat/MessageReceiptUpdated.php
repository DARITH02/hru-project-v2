<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceiptUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $userId,
        public array $messageIds,
        public string $status,
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.conversation.'.$this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.receipt.updated';
    }
}
