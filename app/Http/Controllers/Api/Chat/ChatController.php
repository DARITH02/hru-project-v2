<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\DeleteMessageRequest;
use App\Http\Requests\Chat\ReactMessageRequest;
use App\Http\Requests\Chat\SearchMessagesRequest;
use App\Http\Requests\Chat\StoreConversationRequest;
use App\Http\Requests\Chat\StoreMessageRequest;
use App\Http\Requests\Chat\TypingRequest;
use App\Http\Requests\Chat\UpdateMessageRequest;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Repositories\Chat\ChatRepository;
use App\Services\Chat\ChatService;
use App\Support\Http\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatRepository $repository,
        private readonly ChatService $chat,
    ) {
    }

    public function conversations(SearchMessagesRequest $request)
    {
        $conversations = $this->repository->conversationsFor(
            $request->user(),
            $request->string('q')->toString() ?: null
        );

        $conversations->getCollection()->transform(function (Conversation $conversation) {
            $conversation->participants->transform(fn ($participant) => $this->withPresence($participant));

            return $conversation;
        });

        return ApiResponse::success(['conversations' => $conversations]);
    }

    public function storeConversation(StoreConversationRequest $request)
    {
        Gate::authorize('create', Conversation::class);

        $conversation = $this->chat->startConversation(
            $request->user(),
            $request->array('participant_ids'),
            $request->string('type', 'direct')->toString()
        );

        return ApiResponse::success(['conversation' => $conversation], 201);
    }

    public function messages(Request $request, Conversation $conversation)
    {
        Gate::authorize('view', $conversation);

        $messages = $this->repository->messagesFor(
            $conversation,
            $request->user(),
            min((int) $request->query('per_page', 30), 50)
        );

        return ApiResponse::success(['messages' => $messages]);
    }

    public function send(StoreMessageRequest $request, Conversation $conversation)
    {
        Gate::authorize('send', $conversation);

        $message = $this->chat->sendMessage($request->user(), $conversation, $request->validated());

        return ApiResponse::success(['message' => $message], 201);
    }

    public function update(UpdateMessageRequest $request, Message $message)
    {
        Gate::authorize('update', $message);

        $message = $this->chat->editMessage($request->user(), $message, $request->string('message')->toString());

        return ApiResponse::success(['message' => $message]);
    }

    public function destroy(DeleteMessageRequest $request, Message $message)
    {
        if ($request->string('mode')->toString() === 'everyone') {
            Gate::authorize('deleteForEveryone', $message);
        } else {
            Gate::authorize('view', $message);
        }

        $this->chat->deleteMessage($request->user(), $message, $request->string('mode')->toString());

        return ApiResponse::success(['message' => 'Message deleted.']);
    }

    public function react(ReactMessageRequest $request, Message $message)
    {
        Gate::authorize('react', $message);

        $message = $this->chat->toggleReaction($request->user(), $message, $request->string('reaction')->toString());

        return ApiResponse::success(['message' => $message]);
    }

    public function delivered(Request $request, Conversation $conversation)
    {
        Gate::authorize('view', $conversation);

        $data = $request->validate([
            'message_ids' => ['required', 'array', 'min:1'],
            'message_ids.*' => ['integer', 'exists:messages,id'],
        ]);

        $this->chat->markDelivered($request->user(), $conversation, $data['message_ids']);

        return ApiResponse::success(['message' => 'Messages marked delivered.']);
    }

    public function read(Request $request, Conversation $conversation)
    {
        Gate::authorize('view', $conversation);

        $data = $request->validate([
            'message_ids' => ['required', 'array', 'min:1'],
            'message_ids.*' => ['integer', 'exists:messages,id'],
        ]);

        $this->chat->markRead($request->user(), $conversation, $data['message_ids']);

        return ApiResponse::success(['message' => 'Messages marked read.']);
    }

    public function typing(TypingRequest $request, Conversation $conversation)
    {
        Gate::authorize('view', $conversation);

        $this->chat->typing($request->user(), $conversation, $request->boolean('typing'));

        return ApiResponse::success();
    }

    public function users(SearchMessagesRequest $request)
    {
        $users = $this->repository
            ->searchableUsers($request->user(), $request->string('q')->toString() ?: null)
            ->map(fn ($user) => $this->withPresence($user));

        return ApiResponse::success([
            'users' => $users,
        ]);
    }

    public function search(SearchMessagesRequest $request)
    {
        return ApiResponse::success([
            'messages' => $this->repository->searchMessages(
                $request->user(),
                $request->string('q')->toString() ?: null,
                (int) $request->query('per_page', 20)
            ),
        ]);
    }

    public function presence(Request $request)
    {
        $data = $request->validate(['online' => ['required', 'boolean']]);

        return ApiResponse::success([
            'presence' => $this->chat->setPresence($request->user(), (bool) $data['online']),
        ]);
    }

    public function notifications(Request $request)
    {
        return ApiResponse::success([
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function readNotifications(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return ApiResponse::success(['message' => 'Notifications marked read.']);
    }

    private function withPresence($user)
    {
        $user->setAttribute('online', Cache::has("chat:presence:{$user->id}"));
        $user->setAttribute('last_seen_at', Cache::get("chat:last_seen:{$user->id}"));

        return $user;
    }
}
