<?php

namespace App\Console\Commands;

use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldChatHistory extends Command
{
    protected $signature = 'chat:cleanup-old-history
        {--days=7 : Delete chat messages older than this many days}
        {--dry-run : Show what would be deleted without deleting data}';

    protected $description = 'Permanently delete old chat messages and uploaded chat files.';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);
        $dryRun = (bool) $this->option('dry-run');

        $messageQuery = Message::withTrashed()
            ->where('created_at', '<', $cutoff);

        $messageCount = (clone $messageQuery)->count();
        $attachmentCount = (clone $messageQuery)->withCount('attachments')->get()->sum('attachments_count');
        $emptyConversationCount = Conversation::query()
            ->where('updated_at', '<', $cutoff)
            ->whereDoesntHave('messages', fn ($query) => $query->withTrashed())
            ->count();

        if ($dryRun) {
            $this->info("Would permanently delete {$messageCount} chat message(s) older than {$days} day(s).");
            $this->info("Would delete {$attachmentCount} uploaded chat attachment file(s).");
            $this->info("Would delete {$emptyConversationCount} empty old conversation(s).");

            return self::SUCCESS;
        }

        $deletedMessages = 0;
        $deletedFiles = 0;

        $messageQuery
            ->with('attachments')
            ->orderBy('id')
            ->chunkById(100, function ($messages) use (&$deletedMessages, &$deletedFiles) {
                foreach ($messages as $message) {
                    foreach ($message->attachments as $attachment) {
                        if ($attachment->file_path && Storage::disk('public')->delete($attachment->file_path)) {
                            $deletedFiles++;
                        }
                    }

                    $message->forceDelete();
                    $deletedMessages++;
                }
            });

        $deletedConversations = Conversation::query()
            ->where('updated_at', '<', $cutoff)
            ->whereDoesntHave('messages', fn ($query) => $query->withTrashed())
            ->delete();

        $this->info("Permanently deleted {$deletedMessages} old chat message(s).");
        $this->info("Deleted {$deletedFiles} uploaded chat attachment file(s).");
        $this->info("Deleted {$deletedConversations} empty old conversation(s).");

        return self::SUCCESS;
    }
}
