<?php

namespace App\Repositories\Chat;

use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChatRepository
{
    public function conversationsFor(User $user, ?string $search = null): LengthAwarePaginator
    {
        return Conversation::query()
            ->whereHas('participants', fn ($query) => $query->whereKey($user->id))
            ->with([
                'participants:id,name,email,role',
                'messages' => fn ($query) => $query->latest()->limit(1)->with('attachments'),
            ])
            ->withCount([
                'messages as unread_messages_count' => fn ($query) => $query
                    ->where('sender_id', '!=', $user->id)
                    ->whereHas('receipts', fn ($receipt) => $receipt
                        ->where('user_id', $user->id)
                        ->whereNull('read_at')),
            ])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('participants', fn ($participant) => $participant
                    ->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%"));
            })
            ->latest('updated_at')
            ->paginate(20);
    }

    public function messagesFor(Conversation $conversation, User $user, int $perPage = 30): LengthAwarePaginator
    {
        return Message::query()
            ->where('conversation_id', $conversation->id)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('message_id')
                    ->from('message_deletions')
                    ->where('user_id', $user->id);
            })
            ->with(['sender:id,name,role', 'attachments', 'reactions.user:id,name', 'replyTo.sender:id,name,role', 'receipts'])
            ->latest()
            ->paginate($perPage);
    }

    public function searchableUsers(User $user, ?string $search = null): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->where('id', '!=', $user->id)
            ->where('is_approved', true)
            ->whereIn('role', ['teacher', 'admin', 'super_admin'])
            ->when($user->role === 'teacher', fn ($query) => $query->whereIn('role', ['admin', 'super_admin']))
            ->when($user->role === 'admin', fn ($query) => $query->whereIn('role', ['teacher', 'super_admin']))
            ->when($search, fn ($query) => $query
                ->where(fn ($inner) => $inner
                    ->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")))
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function searchMessages(User $user, ?string $search, int $perPage = 20): LengthAwarePaginator
    {
        return Message::query()
            ->whereHas('conversation.participants', fn ($query) => $query->whereKey($user->id))
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('message_id')
                    ->from('message_deletions')
                    ->where('user_id', $user->id);
            })
            ->when($search, fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('message', 'ilike', "%{$search}%")
                    ->orWhereHas('attachments', fn ($attachment) => $attachment->where('file_name', 'ilike', "%{$search}%"));
            }))
            ->with(['conversation.participants:id,name,role', 'sender:id,name,role', 'attachments'])
            ->latest()
            ->paginate($perPage);
    }
}
