<?php

namespace App\Policies;

use App\Models\Chat\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->canUseChat()
            && $conversation->participants()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->canUseChat();
    }

    public function send(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
