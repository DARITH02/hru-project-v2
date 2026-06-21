<?php

namespace App\Policies;

use App\Models\Chat\Message;
use App\Models\User;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return $user->canUseChat()
            && $message->conversation->participants()->whereKey($user->id)->exists();
    }

    public function update(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id
            && $user->canUseChat()
            && !$message->trashed();
    }

    public function deleteForEveryone(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id && !$message->trashed();
    }

    public function react(User $user, Message $message): bool
    {
        return $this->view($user, $message) && !$message->trashed();
    }
}
