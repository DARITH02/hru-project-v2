<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    /**
     * Display a listing of the bots.
     */
    public function index()
    {
        $bots = TelegramBot::latest()->get();
        return view('admin.telegram_bots', compact('bots'));
    }

    /**
     * Store a newly created bot in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'bot_token' => 'required|string|unique:telegram_bots,bot_token',
        ]);

        $botToken = $request->bot_token;
        $chatId = null;

        try {
            // Try to fetch chat_id from getUpdates
            $response = Http::get("https://api.telegram.org/bot{$botToken}/getUpdates");
            
            if ($response->successful()) {
                $result = $response->json();
                if (!empty($result['result'])) {
                    // Get the chat_id from the latest update
                    $latestUpdate = end($result['result']);
                    if (isset($latestUpdate['message']['chat']['id'])) {
                        $chatId = $latestUpdate['message']['chat']['id'];
                    } elseif (isset($latestUpdate['my_chat_member']['chat']['id'])) {
                        $chatId = $latestUpdate['my_chat_member']['chat']['id'];
                    } elseif (isset($latestUpdate['channel_post']['chat']['id'])) {
                        $chatId = $latestUpdate['channel_post']['chat']['id'];
                    }
                }
            } else {
                return redirect()->back()->with('error', 'Invalid Bot Token. Could not connect to Telegram.');
            }
        } catch (\Exception $e) {
            Log::error('Telegram API Error: ' . $e->getMessage());
        }

        $bot = TelegramBot::create([
            'name' => $request->name,
            'bot_token' => $botToken,
            'chat_id' => $chatId,
            'is_active' => TelegramBot::count() === 0 // Make first bot active by default
        ]);

        if ($chatId) {
            return redirect()->back()->with('success', "Bot added successfully! Found Chat ID: {$chatId}");
        }

        return redirect()->back()->with('warning', 'Bot added, but no Chat ID found. Please send a message to the bot first and try to add it again or edit it.');
    }

    /**
     * Set the selected bot as active.
     */
    public function setActive($id)
    {
        // Set all bots to inactive
        TelegramBot::query()->update(['is_active' => false]);

        // Set selected bot to active
        $bot = TelegramBot::findOrFail($id);
        $bot->update(['is_active' => true]);

        return redirect()->back()->with('success', "Bot '{$bot->name}' is now active.");
    }

    /**
     * Remove the specified bot from storage.
     */
    public function destroy($id)
    {
        $bot = TelegramBot::findOrFail($id);
        $name = $bot->name;
        $wasActive = $bot->is_active;
        $bot->delete();

        // If active bot was deleted, set another one as active
        if ($wasActive) {
            $newActive = TelegramBot::first();
            if ($newActive) {
                $newActive->update(['is_active' => true]);
            }
        }

        return redirect()->back()->with('success', "Bot '{$name}' deleted successfully.");
    }

    /**
     * Send a test message.
     */
    public function sendTest($id)
    {
        $bot = TelegramBot::findOrFail($id);

        if (!$bot->chat_id) {
            return redirect()->back()->with('error', "Cannot send test. Chat ID is missing for this bot.");
        }

        try {
            $message = "🔔 *Test Message*\n\nThis is a test notification from your Obsidian Academic Attendance System.\n\nBot: {$bot->name}\nStatus: Online ✅";
            
            $response = Http::post("https://api.telegram.org/bot{$bot->bot_token}/sendMessage", [
                'chat_id' => $bot->chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            if ($response->successful()) {
                return redirect()->back()->with('success', 'Test message sent successfully!');
            }

            return redirect()->back()->with('error', 'Failed to send test message: ' . ($response->json()['description'] ?? 'Unknown error'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    /**
     * Re-sync Chat ID for established bot.
     */
    public function sync($id)
    {
        $bot = TelegramBot::findOrFail($id);
        $chatId = null;

        try {
            $response = Http::get("https://api.telegram.org/bot{$bot->bot_token}/getUpdates");
            if ($response->successful()) {
                $result = $response->json();
                if (!empty($result['result'])) {
                    $latestUpdate = end($result['result']);
                    $chatId = $latestUpdate['message']['chat']['id'] 
                        ?? $latestUpdate['my_chat_member']['chat']['id'] 
                        ?? $latestUpdate['channel_post']['chat']['id'] 
                        ?? null;
                }
            }
        } catch (\Exception $e) { }

        if ($chatId) {
            $bot->update(['chat_id' => $chatId]);
            return redirect()->back()->with('success', "Bot '{$bot->name}' synced! Chat ID: {$chatId}");
        }

        return redirect()->back()->with('error', "Could not find any updates for '{$bot->name}'. Please send a message to the bot first.");
    }
}
