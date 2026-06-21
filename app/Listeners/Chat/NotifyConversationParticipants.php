<?php

namespace App\Listeners\Chat;

use App\Events\Chat\MessageSent;
use App\Notifications\ChatMessageNotification;

class NotifyConversationParticipants
{
    public function handle(MessageSent $event): void
    {
        $message = $event->message->loadMissing('conversation.participants');

        $message->conversation->participants
            ->where('id', '!=', $message->sender_id)
            ->each(fn ($participant) => $participant->notify(new ChatMessageNotification($message)));
    }
}
