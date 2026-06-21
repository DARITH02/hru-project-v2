# ATS Real-Time Chat Module

This module provides the backend for a Messenger-style chat experience without adding visible UI screens yet.

## Dependencies

Install when network access is available:

```bash
composer require laravel/reverb
npm install react react-dom @tanstack/react-query laravel-echo pusher-js lucide-react emoji-picker-react
```

Set `.env` values:

```dotenv
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=ats-chat
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Run:

```bash
php artisan migrate
php artisan storage:link
php artisan reverb:start
```

## API

All routes require `auth:sanctum` and `role:teacher,admin,super_admin`.

- `GET /api/chat/users?q=` search allowed chat users.
- `GET /api/chat/conversations?q=` list recent conversations.
- `POST /api/chat/conversations` with `participant_ids`.
- `GET /api/chat/conversations/{conversation}/messages?per_page=30` infinite-scroll pages.
- `POST /api/chat/conversations/{conversation}/messages` multipart send with `message`, `reply_to_message_id`, and `attachments[]`.
- `PATCH /api/chat/messages/{message}` edit own message within 15 minutes.
- `DELETE /api/chat/messages/{message}` with `mode=me|everyone`.
- `POST /api/chat/messages/{message}/reactions` with one of `👍 ❤️ 😂 😮 😢 🔥`.
- `POST /api/chat/conversations/{conversation}/delivered` and `/read` with `message_ids[]`.
- `POST /api/chat/conversations/{conversation}/typing` with `typing`.
- `POST /api/chat/presence` with `online`.
- `GET /api/chat/notifications` and `POST /api/chat/notifications/read`.

## Broadcast Channels

- `private-chat.conversation.{id}` receives `message.sent`, `message.updated`, `message.deleted`, `message.reaction.updated`, `message.receipt.updated`, and `user.typing`.
- `private-chat.user.{id}` receives notification broadcasts for the authenticated user.
- `presence-chat.presence` receives `user.presence.changed` and supports Echo `join('chat.presence')`.

## Frontend Integration Notes

Use Laravel Echo with Reverb/Pusher protocol, React Query for API caching, and optimistic UI states:

- Create a temporary client message with `status: sending`.
- Replace it with API response and show `sent`.
- Call `/delivered` after the message enters the viewport or is received by Echo.
- Call `/read` when the conversation is active and visible.
- Subscribe to conversation channels after opening a chat.
- Debounce typing events to 300-500ms and send `typing=false` after idle.
- Use the browser Notification API only after explicit permission from the user.

Security controls included here:

- Students are blocked by route middleware.
- Conversation private channels verify membership.
- Uploads are limited to JPG, PNG, WEBP, PDF, DOCX, XLSX, ZIP, and TXT, max 20 MB each.
- Sender-only edit/delete-for-everyone policies.
- Edit window limited to 15 minutes with edit history retained.
