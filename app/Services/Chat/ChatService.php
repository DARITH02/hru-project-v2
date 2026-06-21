<?php

namespace App\Services\Chat;

use App\Events\Chat\MessageDeleted;
use App\Events\Chat\MessageReactionUpdated;
use App\Events\Chat\MessageReceiptUpdated;
use App\Events\Chat\MessageSent;
use App\Events\Chat\MessageUpdated;
use App\Events\Chat\UserPresenceChanged;
use App\Events\Chat\UserTyping;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Models\Chat\MessageDeletion;
use App\Models\Chat\MessageEditHistory;
use App\Models\Chat\MessageReaction;
use App\Models\Chat\MessageReceipt;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ChatService
{
    public function startConversation(User $actor, array $participantIds, string $type = 'direct'): Conversation
    {
        $participants = User::query()
            ->whereIn('id', array_values(array_unique([...$participantIds, $actor->id])))
            ->get();

        if ($participants->count() < 2) {
            throw ValidationException::withMessages(['participant_ids' => 'A conversation requires at least two participants.']);
        }

        foreach ($participants as $participant) {
            if ($participant->id !== $actor->id && !$actor->canChatWith($participant)) {
                throw ValidationException::withMessages(['participant_ids' => "You cannot chat with {$participant->name}."]);
            }
        }

        if ($type === 'direct' && $participants->count() === 2) {
            $existing = $this->findDirectConversation($participants->pluck('id')->all());
            if ($existing) {
                return $existing->load(['participants:id,name,email,role']);
            }
        }

        return DB::transaction(function () use ($actor, $participants, $type) {
            $conversation = Conversation::create([
                'type' => $participants->count() > 2 ? 'group' : $type,
                'created_by' => $actor->id,
            ]);

            $conversation->participants()->attach($participants->pluck('id')->all());

            return $conversation->load(['participants:id,name,email,role']);
        });
    }

    public function sendMessage(User $sender, Conversation $conversation, array $payload): Message
    {
        return DB::transaction(function () use ($sender, $conversation, $payload) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'reply_to_message_id' => $payload['reply_to_message_id'] ?? null,
                'message' => $payload['message'] ?? null,
                'type' => $this->resolveMessageType($payload),
            ]);

            foreach (($payload['attachments'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $message->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $file->store('chat/'.$conversation->id, 'public'),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    ]);
                }
            }

            $conversation->touch();

            $conversation->participants()
                ->whereKeyNot($sender->id)
                ->get()
                ->each(function (User $participant) use ($message) {
                    MessageReceipt::firstOrCreate([
                        'message_id' => $message->id,
                        'user_id' => $participant->id,
                    ]);
                });

            MessageReceipt::firstOrCreate(
                ['message_id' => $message->id, 'user_id' => $sender->id],
                ['delivered_at' => now(), 'read_at' => now()]
            );

            $message->load(['sender:id,name,role', 'attachments', 'reactions.user:id,name', 'replyTo.sender:id,name,role', 'receipts']);

            MessageSent::dispatch($message);

            return $message;
        });
    }

    public function editMessage(User $editor, Message $message, string $text): Message
    {
        return DB::transaction(function () use ($editor, $message, $text) {
            MessageEditHistory::create([
                'message_id' => $message->id,
                'edited_by' => $editor->id,
                'old_message' => $message->message,
                'new_message' => $text,
            ]);

            $message->forceFill([
                'message' => $text,
                'is_edited' => true,
                'edited_at' => now(),
            ])->save();

            $message->load(['sender:id,name,role', 'attachments', 'reactions.user:id,name', 'replyTo.sender:id,name,role', 'receipts']);

            broadcast(new MessageUpdated($message))->toOthers();

            return $message;
        });
    }

    public function deleteMessage(User $user, Message $message, string $mode): void
    {
        if ($mode === 'everyone') {
            $message->delete();
            broadcast(new MessageDeleted($message, $mode, $user->id))->toOthers();
        } else {
            MessageDeletion::updateOrCreate(
                ['message_id' => $message->id, 'user_id' => $user->id],
                ['deleted_at' => now()]
            );
        }
    }

    public function toggleReaction(User $user, Message $message, string $reaction): Message
    {
        $existing = MessageReaction::query()
            ->where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('reaction', $reaction)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            MessageReaction::updateOrCreate(
                ['message_id' => $message->id, 'user_id' => $user->id],
                ['reaction' => $reaction]
            );
        }

        $message->load('reactions.user:id,name');
        broadcast(new MessageReactionUpdated($message))->toOthers();

        return $message;
    }

    public function markDelivered(User $user, Conversation $conversation, array $messageIds): void
    {
        $ids = $this->participantMessageIds($conversation, $user, $messageIds);

        MessageReceipt::query()
            ->whereIn('message_id', $ids)
            ->where('user_id', $user->id)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now(), 'updated_at' => now()]);

        broadcast(new MessageReceiptUpdated($conversation->id, $user->id, $ids, 'delivered'))->toOthers();
    }

    public function markRead(User $user, Conversation $conversation, array $messageIds): void
    {
        $ids = $this->participantMessageIds($conversation, $user, $messageIds);

        MessageReceipt::query()
            ->whereIn('message_id', $ids)
            ->where('user_id', $user->id)
            ->update(['delivered_at' => now(), 'read_at' => now(), 'updated_at' => now()]);

        Message::query()
            ->whereIn('id', $ids)
            ->where('sender_id', '!=', $user->id)
            ->update(['is_read' => true]);

        $conversation->participantRows()
            ->where('user_id', $user->id)
            ->update([
                'last_read_message_id' => max($ids) ?: null,
                'last_read_at' => now(),
                'updated_at' => now(),
            ]);

        broadcast(new MessageReceiptUpdated($conversation->id, $user->id, $ids, 'read'))->toOthers();
    }

    public function typing(User $user, Conversation $conversation, bool $typing): void
    {
        broadcast(new UserTyping($conversation->id, $user, $typing))->toOthers();
    }

    public function setPresence(User $user, bool $online): array
    {
        $lastSeen = now()->toISOString();

        if ($online) {
            Cache::put($this->presenceKey($user->id), $lastSeen, now()->addMinutes(3));
        } else {
            Cache::put($this->lastSeenKey($user->id), $lastSeen, now()->addDays(30));
            Cache::forget($this->presenceKey($user->id));
        }

        broadcast(new UserPresenceChanged($user, $online, $lastSeen))->toOthers();

        return ['online' => $online, 'last_seen_at' => $lastSeen];
    }

    public function deleteUserChatHistory(User $user): int
    {
        $conversationIds = $user->chatConversations()->pluck('conversations.id');

        if ($conversationIds->isEmpty()) {
            return 0;
        }

        Message::withTrashed()
            ->whereIn('conversation_id', $conversationIds)
            ->with('attachments')
            ->orderBy('id')
            ->chunkById(100, function ($messages) {
                foreach ($messages as $message) {
                    foreach ($message->attachments as $attachment) {
                        if ($attachment->file_path) {
                            Storage::disk('public')->delete($attachment->file_path);
                        }
                    }
                }
            });

        Conversation::query()
            ->whereIn('id', $conversationIds)
            ->delete();

        return $conversationIds->count();
    }

    private function findDirectConversation(array $participantIds): ?Conversation
    {
        sort($participantIds);

        return Conversation::query()
            ->where('type', 'direct')
            ->whereHas('participants', fn ($query) => $query->whereIn('users.id', $participantIds), '=', count($participantIds))
            ->withCount('participants')
            ->get()
            ->first(fn (Conversation $conversation) => $conversation->participants_count === count($participantIds));
    }

    private function resolveMessageType(array $payload): string
    {
        $files = collect($payload['attachments'] ?? []);

        if ($files->isEmpty()) {
            return 'text';
        }

        $imageCount = $files->filter(fn (UploadedFile $file) => str_starts_with((string) $file->getMimeType(), 'image/'))->count();

        if ($imageCount === $files->count() && empty($payload['message'])) {
            return 'image';
        }

        return empty($payload['message']) ? 'file' : 'mixed';
    }

    private function participantMessageIds(Conversation $conversation, User $user, array $messageIds): array
    {
        return Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereIn('id', $messageIds)
            ->pluck('id')
            ->all();
    }

    private function presenceKey(int $userId): string
    {
        return "chat:presence:{$userId}";
    }

    private function lastSeenKey(int $userId): string
    {
        return "chat:last_seen:{$userId}";
    }
}
