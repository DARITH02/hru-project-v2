<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat\Conversation;

Broadcast::channel('teacher-attendance', fn () => true);
Broadcast::channel('teacher-attendance.{date}', fn () => true);
Broadcast::channel('teacher-attendance.teacher.{teacherId}', function ($user, int $teacherId) {
    return $user->isAdmin() || (int) $user->teacher?->id === $teacherId;
});

Broadcast::channel('chat.conversation.{conversationId}', function ($user, int $conversationId) {
    return $user->canUseChat()
        && Conversation::query()
            ->whereKey($conversationId)
            ->whereHas('participants', fn ($query) => $query->whereKey($user->id))
            ->exists();
});

Broadcast::channel('chat.user.{userId}', function ($user, int $userId) {
    return $user->canUseChat() && (int) $user->id === $userId;
});

Broadcast::channel('App.Models.User.{userId}', function ($user, int $userId) {
    return (int) $user->id === $userId;
});

Broadcast::channel('chat.presence', function ($user) {
    return $user->canUseChat()
        ? ['id' => $user->id, 'name' => $user->name, 'role' => $user->role]
        : false;
});
