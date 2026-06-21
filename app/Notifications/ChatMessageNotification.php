<?php

namespace App\Notifications;

use App\Models\Chat\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ChatMessageNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Message $message)
    {
        $this->message->loadMissing('sender:id,name,role');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'conversation_id' => $this->message->conversation_id,
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender?->name,
            'preview' => str($this->message->message ?? 'Attachment')->limit(120)->toString(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
